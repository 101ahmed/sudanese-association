# نشر المشروع على الإنترنت — Sudanese Association (Symfony)

للنشر على **IONOS** (Web Hosting أو VPS)، راجع **`DEPLOY-IONOS.md`**.  
للنشر على **OVH**، راجع **`DEPLOY-OVH.md`**.

## المتطلبات على الخادم

- **PHP 8.2+** مع الامتدادات: `ctype`, `iconv`, `pdo_mysql` (أو `pdo_pgsql` حسب قاعدة البيانات)
- **Composer** (على الخادم أو محلياً ثم رفع `vendor`)
- **خادم ويب**: Apache مع `mod_rewrite` أو Nginx مع تمرير الطلبات إلى `public/index.php`
- **MySQL / MariaDB** (أو قاعدة أخرى متوافقة مع Doctrine)

## شراء اسم النطاق وربطه بالموقع

لا يمكن تنفيذ الشراء من داخل المشروع؛ الخطوات العملية:

1. **شراء النطاق** من أي مسجّل (Namecheap، Cloudflare، مزوّد محلي، أو مع باقة الاستضافة).
2. **في لوحة DNS** عند المسجّل (أو عند الاستضافة إذا استخدمت نطاقهم):
   - سجّل **`A`** يُشير اسم النطاق (مثل `@` أو `example.com`) إلى **عنوان IP** الخادم الذي يستضيف المشروع.
   - اختياري: سجّل **`CNAME`** لـ `www` يشير إلى نطاقك أو إلى نفس الاستضافة (حسب ما يوصي المزوّد).
3. **فعّل HTTPS** (غالباً Let’s Encrypt من مزوّد الاستضافة أو Cloudflare).
4. على الخادم، انسخ القالب وعدّل العنوان العام:
   ```bash
   cp .env.prod.local.dist .env.prod.local
   ```
   ثم غيّر **`DEFAULT_URI`** إلى `https://اسم-نطاقك` (بدون `/` في النهاية).

بعدها أكمل الخطوات التالية (أسرار، قاعدة بيانات، كاش).

## 1) إعداد الملفات البيئية

على الخادم، أنشئ ملف **`.env.prod.local`** انطلاقاً من **`.env.prod.local.dist`** (`cp .env.prod.local.dist .env.prod.local`) وضع فيه على الأقل:

| المتغير | الوصف |
|--------|--------|
| `APP_SECRET` | مفتاح عشوائي: `php -r "echo bin2hex(random_bytes(16)), PHP_EOL;"` |
| `DATABASE_URL` | اتصال قاعدة البيانات الإنتاجية |
| `DEFAULT_URI` | عنوان الموقع العام، مثل `https://sd-rennes.fr` (بدون `/` في النهاية) |
| `MAILER_DSN` | إن وُجدت رسائل بريد حقيقية |

يمكنك تعديل القيم الافتراضية في `.env.prod` (المُلتزَم في Git) للإعدادات غير السرية فقط.

## 2) تثبيت الاعتمادات (بدون أدوات التطوير)

```bash
composer install --no-dev --optimize-autoloader
```

## 3) قاعدة البيانات

بعد ضبط `DATABASE_URL`:

```bash
php bin/console doctrine:schema:update --force
```

أو استخدم migrations إذا أضفتها لاحقاً: `php bin/console doctrine:migrations:migrate --no-interaction`

## 4) التخزين المؤقت والأصول

```bash
php bin/console cache:clear --env=prod --no-debug
php bin/console assets:install public --env=prod
```

## 5) صلاحيات المجلدات

يجب أن يكون خادم الويب قادراً على الكتابة تحت `var/` (الكاش، الجلسات، السجلات):

```bash
# Linux مثال
chmod -R ug+rwX var/
```

إذا رفعتم ملفات (صور أخبار، إلخ) إلى `public/uploads/`، أنشئوا المجلد واضبطوا الصلاحيات المناسبة.

## 6) إعداد Apache

- **DocumentRoot** يجب أن يشير إلى مجلد **`public/`** وليس جذر المشروع.
- ملف **`public/.htaccess`** مضمّن لإعادة التوجيه إلى `index.php`.

## 7) إعداد Nginx (مختصر)

مرّروا كل الطلبات غير الملفات الثابتة إلى `public/index.php` (راجع [توثيق Symfony — web server](https://symfony.com/doc/current/setup/web_server_configuration.html)).

## 8) HTTPS ووكيل عكسي

إذا كان الموقع خلف Cloudflare أو Nginx كوكيل، اضبطوا `TRUSTED_PROXIES` / `SYMFONY_TRUSTED_PROXIES` حسب بيئتكم حتى تُحسب عناوين IP والمخططات (`https`) بشكل صحيح.

## 9) بعد الرفع

- افتحوا الموقع وتأكدوا من الصفحة الرئيسية و`/news` و`/sitemap.xml` و`/robots.txt`.
- جرّبوا تسجيل الدخول للوحة الإدارة على `/admin` بحساب مسؤول فقط.

---

## ملخص سريع (مراجعة قبل الإطلاق)

1. PHP 8.2+، Composer، قاعدة بيانات، خادم ويب بجذر **`public/`**.  
2. إنشاء **`.env.prod.local`** يحتوي **`APP_SECRET`**, **`DATABASE_URL`**, **`DEFAULT_URI`**.  
3. `composer install --no-dev --optimize-autoloader`  
4. `php bin/console doctrine:schema:update --force` (أو migrations إن استخدمتها)  
5. `php bin/console cache:clear --env=prod --no-debug` و`assets:install`  
6. **`var/`** قابل للكتابة؛ **`public/uploads/`** إن رفعتم ملفات من الموقع.

**English (short):** Same steps — docroot `public/`, `.env.prod.local`, composer prod, schema/cache, writable `var/`.
