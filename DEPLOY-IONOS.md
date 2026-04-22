# نشر المشروع على IONOS — Symfony

> **تنبيه:** [صفحة حالة IONOS](https://www.ionos-status.fr/) للمراقبة فقط وليست لوحة رفع. الرفع والإعداد يتم من **IONOS Control Panel** (مثل `my.ionos.fr` / `my.ionos.com`) ومن SFTP/SSH حسب باقتك.

---

## تنفيذ سريع على IONOS (مشروعك + sd-rennes.fr)

1. **الدخول للوحة:** [IONOS](https://www.ionos.fr/) → تسجيل الدخول → **Hosting** / **Hébergement** (حسب واجهتك).
2. **النطاق:** تأكد أن **sd-rennes.fr** مربوط بهذه الاستضافة (أو أن سجلات DNS عند IONOS تشير لخادم الاستضافة).
3. **قاعدة بيانات:** أنشئ **MySQL** من القسم المعني، واحفظ: اسم القاعدة، المستخدم، كلمة المرور، **hostname** (مثل `db5001234567.hosting-data.io` — انسخه من اللوحة).
4. **PHP:** فعّل **PHP 8.2+** لموقعك إن وُجد خيار «إصدار PHP».
5. **رفع الملفات:** **SFTP** أو **مدير ملفات** — ارفع مجلد المشروع كاملاً؛ المهم أن **جذر الموقع (Document Root)** يكون مجلد **`public`** داخل المشروع (غالباً من إعدادات الموقع / النطاق في IONOS).
6. **البيئة على السيرفر:** أنشئ ملف **`.env.prod.local`** (من `.env.prod.local.dist` عبر SSH أو لصق المحتوى يدوياً) وضع:
   - `APP_SECRET` عشوائي طويل  
   - `DATABASE_URL="mysql://USER:PASS@HOST:3306/DBNAME?serverVersion=10.11.2-MariaDB&charset=utf8mb4"` (استبدل القيم من خطوة 3)  
   - `DEFAULT_URI=https://sd-rennes.fr`
7. **أوامر (SSH إن وُجد):** من جذر المشروع:
   ```bash
   composer install --no-dev --optimize-autoloader
   composer run prod:post-install
   ```
8. **SSL:** فعّل **Let’s Encrypt** للنطاق من لوحة IONOS إن كان متاحاً.
9. **اختبار:** افتح `https://sd-rennes.fr` ثم `/news` و`/admin`.

> بدون SSH: نفّذ `composer install --no-dev` على جهازك ثم ارفع **`vendor/`** كاملاً مع باقي المشروع.

### إن ظهر النطاق كـ «Domaine supplémentaire» — «Géré par IONOS»

هذا يعني أن **sd-rennes.fr** نطاق إضافي على عقدك، و**DNS يدار من IONOS** (لا تحتاج تعديلاً عند مسجّل خارجي لنفس النطاق).

1. في لوحة IONOS: **Domaines** / **Hébergement** — اختر **sd-rennes.fr**.
2. **ربط النطاق بالمساحة (hébergement web)** التي رفعت عليها المشروع: يجب أن يشير الموقع/النطاق إلى نفس المجلد الذي فيه **`public/index.php`** (أو أن **Document root** = `…/votre-projet/public`).
3. إن عرض IONOS **«répertoire cible»** أو **«dossier du site»**، اجعله يشير إلى مجلد **`public`** لمشروع Symfony وليس جذر المشروع.
4. فعّل **SSL** لـ **sd-rennes.fr** (و **www** إن استخدمته) من قسم الشهادات / HTTPS.
5. في **`.env.prod.local`** على السيرفر: `DEFAULT_URI=https://sd-rennes.fr` (أو `https://www.sd-rennes.fr` إن كان الموقع الرسمي مع `www` فقط).

بعد الربط، انتظر بضع دقائق ثم جرّب `https://sd-rennes.fr` — إن بقي **NXDOMAIN** فالنطاق لم يُسند بعد لمساحة أو DNS لم يُحدَّث.

### إن فتح الرابط `…/defaultsite` أو صفحة «Ce domaine est déjà enregistré»

هذا **ليس** موقع Symfony؛ إنها **صفحة IONOS الافتراضية** عندما يكون النطاق مسجَّلاً لدى IONOS لكن **غير موصول بمساحة ويب** تعرض مشروعك، أو **الجذر** ما زال يشير إلى الموقع الافتراضي وليس إلى مجلد **`public`**.

1. في لوحة IONOS: **Hébergement** → اختر **مساحة الاستضافة** التي رفعت عليها الملفات (وليس فقط قسم «Domaines» بدون استضافة).
2. اربط **sd-rennes.fr** بهذه المساحة، واضبط **répertoire cible / dossier du site / Document root** على **`…/votre-projet/public`** (حيث `index.php`).
3. تأكد أن لديك **Web Hosting** فعلياً (ليس تسجيل نطاق فقط بدون استضافة ويب).
4. بعد التصحيح، يجب أن يختفي المسار `/defaultsite` وأن تظهر **صفحة Symfony** على `https://sd-rennes.fr/` (ثم راجع `.env.prod.local` وقاعدة البيانات كما في الأعلى).

### خطأ Chrome: «ERR_SSL_PROTOCOL_ERROR» / «réponse incorrecte» (HTTPS)

المتصفح يحاول **HTTPS** لكن الخادم **لا يقدّم TLS بشكل صحيح** (غالباً **شهادة SSL غير مفعّلة** لهذا النطاق بعد، أو المنفذ 443 لا يخدم HTTPS).

1. **جرّب أولاً:** `http://sd-rennes.fr` (بدون **s**). إن فتح الموقع، المشكلة **فقط في SSL** وليست من Symfony.
2. في **IONOS:** **SSL / Certificat / HTTPS** أو **Sécurité** — فعّل شهادة **Let’s Encrypt** (أو الشهادة المجانية) **لـ sd-rennes.fr** وللنطاق المرتبط بنفس الموقع (و **www** إن استخدمته).
3. تأكد أن **النطاق الإضافي** مربوط **بنفس مساحة الاستضافة** التي تريد تفعيل SSL عليها (أحياناً تُفعَّل الشهادة للنطاق الأساسي فقط).
4. بعد التفعيل انتظر **بضع دقائق إلى ساعة** ثم أعد تحميل `https://sd-rennes.fr` (أو امسح الكاش / نافذة خاصة).
5. عندما يعمل HTTPS، اجعل **`DEFAULT_URI=https://sd-rennes.fr`** في `.env.prod.local`.

> هذا الخطأ **لا يُصلَح من كود المشروع**؛ يُصلَح من **لوحة IONOS** بتفعيل الشهادة وربطها بالنطاق.

### خطأ **403 Forbidden** (Apache / IONOS)

1. **جذر الموقع:** يجب أن يكون **Document root** = مجلد **`public`** (حيث `index.php` و`.htaccess`). إن كان الجذر = جذر المشروع بدون `public`، قد يظهر 403 أو سلوك غريب.
2. **ملف `public/.htaccess`:** يجب أن يُرفع مع المشروع؛ يحتوي الآن على **`Require all granted`** (Apache 2.4) لتفادي سياسة رفض موروثة من الخادم.
3. **الصلاحيات:** مجلدات المسار إلى المشروع غالباً **755**، الملفات **644**؛ **`var/`** و**`public/uploads/`** قابلة للكتابة من مستخدم الويب إن رفعت صوراً من الإدارة.
4. **403 على `/admin` فقط** بعد تسجيل الدخول بحساب **غير** مسؤول → طبيعي (صلاحيات Symfony). سجّل دخولاً بحساب له **`ROLE_ADMIN`**.
5. إن بقي 403 بعد ذلك: راجع سجلات Apache في لوحة IONOS أو **`var/log/prod.log`** إن وصل الطلب إلى PHP.

---

## أولاً: اختر نوع الاستضافة

| النوع | مناسب لـ Symfony | ملاحظة |
|--------|------------------|--------|
| **Web Hosting (Linux + PHP)** | نعم، إذا كان **PHP ≥ 8.2** ويمكن جعل الجذر = `public/` أو إعادة توجيه صحيحة | غالباً أقل تحكماً، لكن أسهل للبداية |
| **VPS / Cloud Server** | نعم — تحكم كامل | تثبت أنت Nginx/Apache وPHP وMySQL |

---

## المسار أ — Web Hosting (استضافة مشتركة)

### 1) في لوحة IONOS

1. تأكد أن الدومين مربوط بالاستضافة (DNS/الموقع الافتراضي).
2. فعّل **PHP 8.2 أو أحدث** من إعدادات PHP (إن وُجدت في لوحة التحكم).
3. أنشئ **قاعدة بيانات MySQL** واسم مستخدم وكلمة مرور، وسجّل **اسم الخادم** (host) والمنفذ إن طُلب.
4. فعّل **SSL** (Let’s Encrypt) للدومين إن كان متاحاً.

### 2) هيكل الملفات (مهم لـ Symfony)

يجب أن يصل الزائر إلى مجلد **`public/`** (حيث `index.php`).

- إذا سمحت IONOS بتعيين **Document Root** إلى مجلد فرعي، اجعله يشير إلى **`.../your-project/public`**.
- إذا كان الجذر ثابتاً على `htdocs` فقط، الأنظف: **رفع المشروع كاملاً** بحيث يكون المسار مثل:
  - `htdocs/your-project/public`  
  ثم استخدم **إعادة توجيه** أو **رابط رمزي (symlink)** إن كانت الخطة تدعم ذلك، أو اتبع توثيق IONOS لـ «مجلد فرعي كجذر الموقع».

> إن لم يكن تغيير الجذر ممكناً، راجع مساعدة IONOS لـ **Symfony** أو **إعادة كتابة URL** — الحل القياسي هو أن يكون **DocumentRoot = `public`**.

### 3) رفع الملفات

- **SFTP** (FileZilla، WinSCP): ارفع المشروع بعد استبعاد `var/cache` غير الضروري أو اتركه ويُعاد بناؤه على السيرفر.
- **مهم — مسار محلي ≠ مسار السيرفر:** في نافذة **الخادم (distant)** لا تكتب مسار Windows مثل `C:\Users\...`؛ المسافر يكون **Linux** فقط (مثل `/public/newsite`). افتح المشروع في **الجهاز المحلي** واسحب الملفات إلى المجلد الصحيح على السيرفر. وإلا يظهر خطأ `No such file` / `SSH_FX_NO_SUCH_FILE`.
- **لا ترفع** `.env.local` من جهازك إن كان فيه أسرار غير إنتاجية؛ أنشئ **`.env.prod.local`** على السيرفر فقط.

### 4) Composer

- إن وُجد **SSH** و**Composer** على الخادم:
  ```bash
  cd /path/to/project
  composer install --no-dev --optimize-autoloader
  ```
- إن لم يتوفر Composer على الاستضافة: نفّذ `composer install --no-dev --optimize-autoloader` **على جهازك** ثم ارفع مجلد **`vendor/`** كاملاً (أثقل لكن يعمل).

### 5) البيئة والأوامر

1. انسخ القالب وعدّله على السيرفر:
   ```bash
   cp .env.prod.local.dist .env.prod.local
   ```
2. عدّل **`APP_SECRET`**, **`DATABASE_URL`**, **`DEFAULT_URI=https://نطاقك`**.
3. إن أمكن عبر SSH:
   ```bash
   composer run prod:post-install
   ```
4. تأكد أن **`public/.htaccess`** موجود (Apache + `mod_rewrite`).

### 6) صلاحيات

- مجلد **`var/`** يجب أن يكون **قابلاً للكتابة** من مستخدم خادم الويب (غالباً `chmod` من SSH أو أداة الملفات إن سمحت).

---

## المسار ب — VPS / Cloud Server (سيرفر خاص)

مناسب إذا أردت **Nginx + PHP-FPM** وتحكماً كاملاً.

### 1) نظام تشغيل

- غالباً **Ubuntu LTS** أو Debian.

### 2) تثبيت الحزم (مثال مفاهيمي)

- **Nginx** أو **Apache**
- **PHP 8.2+** مع امتدادات: `php-fpm`, `php-mysql`, `php-xml`, `php-mbstring`, `php-curl`, `php-intl` (حسب احتياج المشروع)
- **MariaDB/MySQL**
- **Composer** (عالمياً على السيرفر)
- جدار ناري: **UFW** — اسمح بـ 22 (SSH)، 80، 443

### 3) قاعدة البيانات

```bash
# أنشئ مستخدماً وقاعدة بيانات، ثم ضع القيم في DATABASE_URL
```

### 4) نشر الكود

- **Git clone** على السيرفر (مستحسن)، أو SFTP.
- داخل مجلد المشروع:
  ```bash
  composer install --no-dev --optimize-autoloader
  cp .env.prod.local.dist .env.prod.local
  # عدّل .env.prod.local
  composer run prod:post-install
  ```

### 5) إعداد Nginx (فكرة)

- `root` يجب أن يشير إلى **`/path/to/sudanese-association/public`**.
- تمرير PHP إلى **PHP-FPM** (socket أو منفذ `php8.2-fpm`).
- إعادة كتابة جميع الطلبات إلى **`/index.php`** ما عدا الملفات الموجودة فعلياً تحت `public/` (نفس فكرة Symfony الرسمية).

راجع [توثيق Symfony — web server](https://symfony.com/doc/current/setup/web_server_configuration.html).

### 6) HTTPS

- **Let’s Encrypt** (Certbot) مع Nginx/Apache شائع على VPS.

### 7) الصلاحيات والتحديثات

- مالك الملفات: غالباً مستخدم نشر (مثل `deploy`) + `www-data` للقراءة/الكتابة على `var/`.
- حدّث النظام والحزم دورياً.

---

## الموقع لا يعمل — ماذا أفحص؟

1. **صفحة IONOS** («Ce domaine a été enregistré…») → المشكلة **ربط النطاق بالاستضافة** و**Document root = `…/public`** (انظر الأقسام أعلاه). ليست من Symfony.
2. **500 أو صفحة بيضاء** → افتح **`var/log/prod.log`** على السيرفر (آخر أسطر). غالباً: `DATABASE_URL` خاطئ، أو **`vendor/`** غير مرفوع، أو **`APP_SECRET`** فارغ في `.env.prod.local`.
3. **تأكد من الملفات:** يوجد **`public/index.php`**, **`public/.htaccess`**, مجلد **`vendor/`** (بعد `composer install --no-dev` محلياً ثم رفع، أو composer على SSH).
4. **صلاحيات:** **`var/`** قابل للكتابة من خادم الويب (انظر أعلى).
5. **PHP:** إصدار **8.2+** من لوحة IONOS لذلك الموقع.
6. بعد أي تعديل: `php bin/console cache:clear --env=prod --no-debug`
7. **HTTPS خلف وكيل:** الإعداد `trusted_proxies` مفعّل في **`config/packages/framework.yaml`** لبيئة `prod` حتى يُعرَف العنوان والمخطط بشكل صحيح؛ إن لزم تخصيص إضافي راجع `TRUSTED_PROXIES` في [توثيق Symfony](https://symfony.com/doc/current/deployment/proxies.html).

---

## تحقق بعد الرفع

- [ ] الصفحة الرئيسية تفتح بـ **HTTPS**
- [ ] `/news` و`/contact` تعمل
- [ ] `/sitemap.xml` و`/robots.txt`
- [ ] تسجيل الدخول `/admin` (حساب مسؤول فقط)

---

## روابط مفيدة

- حالة الخدمات (للتأكد من عطل عام فقط): [IONOS Status](https://www.ionos-status.fr/)
- الدليل العام للمشروع: **`DEPLOY.md`** — النشر على **OVH**: **`DEPLOY-OVH.md`**
