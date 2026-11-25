# السعر مش ع الخاص

منصة SaaS مصرية بتدي كل تاجر **قائمة أسعار أونلاين** بيحدّثها من موبايله في ثواني،
وبيشاركها **برابط ثابت وكود QR ثابت** — والعميل بيمسح الكود فيلاقي **السعر قدامه فورًا**.

| الوثيقة | المحتوى |
|---|---|
| 📘 [PROJECT_SPEC.md](PROJECT_SPEC.md) | **إيه** اللي هنعمله — النطاق والمنتج. المرجع النهائي عند أي خلاف. |
| 🛠️ [SRS.md](SRS.md) | **إزاي** هنعمله وهياخد **قد إيه** — المعمارية، التنفيذ، تقسيم المهام بالساعات. |
| 🤖 [CLAUDE.md](CLAUDE.md) | القواعد والخطوط الحمراء وأوامر البيئة. |

**مبرمج جديد على المشروع؟** ابدأ بـ [SRS.md](SRS.md) — القسم 1 و 2 هيوصّلوك للصورة كاملة في 10 دقايق.

---

## 🚨 البيئة — مهم جدًا

الـ `php` اللي في الـ PATH هو **XAMPP 8.2.12** وهو **غلط** للمشروع ده (الحزم مش بتتثبت عليه).
استخدم PHP بتاع laraenv دايمًا:

| | المسار |
|---|---|
| PHP 8.4.20 | `C:\laraenv\php\current\php.exe` |
| Composer | `C:\laraenv\bin\composer.phar` |
| MySQL | `C:\laraenv\mysql` — port 3306 |
| الموقع | http://priceprivate.test |

```bash
# artisan
/c/laraenv/php/current/php.exe artisan <command>

# composer
/c/laraenv/php/current/php.exe /c/laraenv/bin/composer.phar <command>
```

**لازم laraenv يكون شغال** (nginx + mysql) قبل أي migration أو تشغيل للموقع.

---

## التشغيل من الصفر

```bash
/c/laraenv/php/current/php.exe /c/laraenv/bin/composer.phar install
npm install
/c/laraenv/php/current/php.exe artisan migrate --seed
npm run dev
```

بعدها افتح http://priceprivate.test

**حسابات تجريبية (بيئة local بس):**

| الدور | الإيميل |
|---|---|
| أدمن | `admin@priceprivate.test` |
| تاجر | `demo@priceprivate.test` — قائمته على `/el-amana` |

---

## أوامر مفيدة

```bash
/c/laraenv/php/current/php.exe artisan test          # الاختبارات (sqlite in-memory)
/c/laraenv/php/current/php.exe artisan spike:qr-pdf  # إثبات عمل QR و PDF عربي
/c/laraenv/php/current/php.exe vendor/bin/pint       # تنسيق الكود قبل كل commit
npm run build                                        # بناء الواجهات
```

---

## الهوية البصرية

مستخرجة من [xtremedsa.com](https://xtremedsa.com) — **ملزمة حرفيًا** (PROJECT_SPEC.md §13).

| | |
|---|---|
| تركواز (الأساسي) | `#029A8E` |
| كحلي (العناوين) | `#243858` |
| أصفر (الشارات فقط) | `#FFD54F` |
| الخط | **Tajawal** — أوزان 400 / 500 / 700 |
| الاتجاه | **RTL · موبايل أولًا** |

كل التوكنز في [`resources/css/tokens.css`](resources/css/tokens.css).
**ممنوع أي لون خارج الملف ده.**

---

## المبدأ الحاكم

> ### «الكود بيوصّل لمكان واحد محدد. اللي التاجر حطّه في الكود هو اللي العميل بيشوفه — فورًا.»

أي قرار بيزوّد خطوة بين **مسح الكود** و**ظهور السعر** = قرار مرفوض.

---

## الهيكل

| المسار | الوصف |
|---|---|
| `/` | الموقع التسويقي + صفحة الخطط |
| `/{slug}` | قائمة أسعار المحل |
| `/{slug}/{category}` | قائمة مفتوحة على قسم |
| **`/p/{code}`** | ⭐ صفحة سعر المنتج المباشرة — وجهة كود QR |
| `/dashboard` | لوحة التاجر |
| `/admin` | لوحة الإدارة (Filament) |

---

© Xtreme Digital
