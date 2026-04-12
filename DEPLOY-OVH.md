# نشر المشروع على OVH — Symfony

نفس المبادئ العامة في **`DEPLOY.md`** (PHP 8.2+، MySQL، جذر الموقع = **`public/`**، ملف **`.env.prod.local`** على السيرفر فقط). اختلافات OVH تظهر في **أسماء القوائم** في المدير (غالباً بالفرنسية في `ovh.com` الفرنسي).

---

## تنفيذ سريع على OVH (مشروعك + sd-rennes.fr أو نطاقك)

1. **الدخول للوحة:** [Espace Client OVH](https://www.ovh.com/manager/) → **Web Cloud** → **Hébergements** (الاستضافة التي تريدها).
2. **النطاق:** في **Noms de domaine** تأكد أن النطاق يشير إلى **نفس** مساحة الاستضافة (سجلات DNS عند OVH أو عند المسجّل نحو IP الاستضافة).
3. **قاعدة بيانات:** **Hébergements** → استضافتك → **Bases de données** → أنشئ **MySQL**. احفظ: المستخدم، كلمة المرور، **اسم المضيف (nom d’hôte / host)** — غالباً **ليس** `127.0.0.1` عند الاتصال من تطبيق على الاستضافة؛ انسخ القيمة كما في اللوحة — واسم القاعدة والمنفذ إن وُجد.
4. **PHP:** من إعدادات الاستضافة، اختر **PHP 8.2** أو أحدث (**Version du PHP** / **moteur PHP**).
5. **رفع الملفات:** **FTP** أو **SFTP** (بيانات الاتصال في المدير أو بريد التفعيل). ارفع المشروع كاملاً؛ المهم أن **جذر الموقع** يكون مجلد **`public`** (انظر القسم التالي).
6. **البيئة على السيرفر:** أنشئ **`.env.prod.local`** (من `.env.prod.local.dist`) وضع:
   - `APP_SECRET` عشوائي طويل  
   - `DATABASE_URL="mysql://USER:PASS@HOST:PORT/DBNAME?serverVersion=10.11.2-MariaDB&charset=utf8mb4"` (القيم من الخطوة 3)  
   - `DEFAULT_URI=https://sd-rennes.fr` (أو نطاقك، بـ `https`، **بدون** `/` في النهاية)
7. **أوامر (SSH)** إن فعّلت SSH على الاستضافة، من **جذر المشروع** (ليس من `public/`):
   ```bash
   composer install --no-dev --optimize-autoloader
   php bin/console doctrine:schema:update --force
   php bin/console cache:clear --env=prod --no-debug
   php bin/console assets:install public --env=prod
   ```
8. **SSL:** في المدير، فعّل شهادة مجانية (**Let’s Encrypt**) للنطاق المرتبط بالاستضافة.
9. **اختبار:** افتح الموقع ثم `/news` و`/admin`.

> **بدون SSH على الاستضافة:** نفّذ `composer install --no-dev --optimize-autoloader` على جهازك ثم ارفع مجلد **`vendor/`** كاملاً مع باقي المشروع.

---

## جذر الموقع = مجلد `public` (مهم لـ Symfony)

يجب أن يصل الزائر إلى **`public/index.php`**، وليس إلى جذر المشروع.

- إن سمحت OVH بتعيين **répertoire racine** / **dossier racine** لموقع متعدد (**multisite**)، اجعله يشير إلى **`…/nom-du-projet/public`**.
- إن كان الجذر الافتراضي مجلداً مثل **`www`**، ارفع المشروع بحيث يكون **`public`** هو المجلد الذي يُعرَض كموقع، أو عدّل ربط النطاق حسب [توثيق OVH للاستضافة](https://docs.ovh.com/fr/hosting/).

تأكد أن **`public/.htaccess`** موجود (Apache + `mod_rewrite`).

---

## نطاق إضافي على نفس العقد (فكرة)

إذا كان النطاق **إضافياً** على نفس الحساب، ربطه يتم من **Noms de domaine** و**Hébergements** بحيث يشير إلى **نفس مساحة الويب** التي فيها مشروعك، مع **جذر** = **`public`**. انتظر بضع دقائق بعد تغيير DNS. إن ظهر **NXDOMAIN** فالنطاق لم يُحلّ بعد أو غير موصول بالاستضافة الصحيحة.

---

## خطأ المتصفح: «ERR_SSL_PROTOCOL_ERROR» (HTTPS)

المتصفح يطلب **HTTPS** لكن الخادم لا يقدّم **TLS** بشكل صحيح (غالباً شهادة غير مفعّلة لهذا النطاق).

1. جرّب **`http://`** (بدون **s**). إن عمل الموقع، المشكلة **في SSL** وليست في كود Symfony.
2. في مدير OVH: **SSL** / الشهادات — فعّل **Let’s Encrypt** للنطاق المرتبط بالاستضافة (و**www** إن استخدمته).
3. بعد التفعيل انتظر قليلاً ثم أعد المحاولة. عند نجاح HTTPS، ضع **`DEFAULT_URI=https://…`** في `.env.prod.local`.

> لا يُصلَح هذا الخطأ من داخل كود المشروع؛ الإصلاح من **لوحة OVH** وDNS/النطاق.

---

## 2) VPS أو Public Cloud

- ثبّت **Debian/Ubuntu**، ثم **Nginx** أو **Apache**، **PHP-FPM 8.2+**، **MariaDB/MySQL**.
- **`root` / DocumentRoot** = **`/chemin/vers/sudanese-association/public`**.
- **Certbot** (Let’s Encrypt) للحصول على HTTPS.
- باقي الخطوات كما في **`DEPLOY.md`**.

---

## 3) أخطاء شائعة

| المشكلة | ماذا تفعل |
|--------|-----------|
| **500** | جذر الموقع ليس `public/`، أو `DATABASE_URL` خاطئ (خاصة **HOST**)، أو `var/` غير قابل للكتابة — راجع `var/log/prod.log` إن أمكن. |
| **NXDOMAIN** | DNS النطاق لا يشير بعد لخادم OVH. |
| **فشل الاتصال بقاعدة البيانات** | استخدم **nom d’hôte** المعروض في لوحة قواعد البيانات، وليس افتراضياً `127.0.0.1` إذا كان الخادم يفرض اسماً آخر. |

---

## 4) تحقق بعد الرفع

- [ ] الصفحة الرئيسية تفتح بـ **HTTPS**
- [ ] `/news` و`/contact` تعملان
- [ ] `/sitemap.xml` و`/robots.txt` (إن كانت مفعّلة في المشروع)
- [ ] `/admin` يعمل بحساب مسؤول فقط

---

## 5) روابط

- [توثيق استضافة الويب OVH](https://docs.ovh.com/fr/hosting/)
- [ضبط PHP على الاستضافة](https://docs.ovh.com/fr/hosting/configurer-php-web/)
- الدليل العام: **`DEPLOY.md`**
