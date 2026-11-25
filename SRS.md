# SRS — وثيقة المتطلبات التقنية والتنفيذ
## مشروع «السعر مش ع الخاص»

> **لمين الوثيقة دي:** المبرمج اللي هيشتغل على المشروع.
> **الإصدار:** 1.0 — 2026-11-25
> **بتكمّل:** [`PROJECT_SPEC.md`](PROJECT_SPEC.md) (الـ **ماذا** — المنتج والنطاق) — ودي بتجاوب على الـ **كيف** (المعمارية والتنفيذ والوقت).
> **قواعد العمل اليومية:** [`CLAUDE.md`](CLAUDE.md)

```
PROJECT_SPEC.md   →  إيه اللي هنعمله ولمين وليه          (المرجع النهائي عند أي خلاف)
SRS.md            →  هنعمله إزاي بالظبط، وهياخد قد إيه   (الملف ده)
CLAUDE.md         →  قواعد وخطوط حمراء وأوامر البيئة
```

---

## فهرس

| # | القسم |
|---|---|
| 1 | [الملخص التقني في صفحة](#1-الملخص-التقني-في-صفحة) |
| 2 | [البيئة والتشغيل](#2-البيئة-والتشغيل) |
| 3 | [المعمارية العامة](#3-المعمارية-العامة) |
| 4 | [نموذج البيانات](#4-نموذج-البيانات) |
| 5 | [خريطة المسارات وترتيبها الحرج](#5-خريطة-المسارات-وترتيبها-الحرج) |
| 6 | [الوحدة 1 — الحسابات والأونبوردنج](#6-الوحدة-1--الحسابات-والأونبوردنج) |
| 7 | [الوحدة 2 — المنتجات والأسعار](#7-الوحدة-2--المنتجات-والأسعار) |
| 8 | [الوحدة 3 — الروابط وأكواد QR ⭐](#8-الوحدة-3--الروابط-وأكواد-qr-) |
| 9 | [الوحدة 4 — الصفحات العامة ⭐](#9-الوحدة-4--الصفحات-العامة-) |
| 10 | [الوحدة 5 — استوديو الطباعة](#10-الوحدة-5--استوديو-الطباعة) |
| 11 | [الوحدة 6 — الموقع التسويقي](#11-الوحدة-6--الموقع-التسويقي) |
| 12 | [الوحدة 7 — الاشتراكات والدفع](#12-الوحدة-7--الاشتراكات-والدفع) |
| 13 | [الوحدة 8 — الإعلانات ولوحة الإدارة](#13-الوحدة-8--الإعلانات-ولوحة-الإدارة) |
| 14 | [الكاش وإبطاله](#14-الكاش-وإبطاله) |
| 15 | [الأمان](#15-الأمان) |
| 16 | [استراتيجية الاختبار](#16-استراتيجية-الاختبار) |
| 17 | [WBS — التقسيم والتقديرات الزمنية](#17-wbs--التقسيم-والتقديرات-الزمنية) |
| 18 | [المسار الحرج والتوازي](#18-المسار-الحرج-والتوازي) |
| 19 | [المخاطر التقنية](#19-المخاطر-التقنية) |
| 20 | [أعراف الكود و Git](#20-أعراف-الكود-و-git) |

---

## 1. الملخص التقني في صفحة

### المنتج في جملة
تاجر بيعمل قائمة أسعار، بيطبع كود QR ويحطه في محله، والعميل بيمسح الكود **فيلاقي السعر قدامه فورًا** — من غير تسجيل ولا بحث ولا تطبيق.

### المبدأ اللي بيحكم كل قرار تقني
> **«الكود بيوصّل لمكان واحد محدد. اللي التاجر حطّه في الكود هو اللي العميل بيشوفه — فورًا.»**

عمليًا ده بيترجم لـ 4 قيود تقنية صارمة:

| القيد | التنفيذ |
|---|---|
| الكود المطبوع **ما يبطّلش يشتغل أبدًا** | `public_code` immutable + soft deletes + resolver بـ fallback لكل حالة |
| صفحة السعر **أسرع حاجة في النظام** | SSR + صفر JS ضروري + response cache + FCP < 1.2s على 3G |
| **صفر احتكاك** قبل السعر | مفيش auth، مفيش redirect وسيط، مفيش modal، مفيش إعلان فوق السعر |
| التعديل **يظهر فورًا** | إبطال كاش بالـ version bump عند أي تغيير سعر |

### الستاك
```
PHP 8.4.20  ·  Laravel 12.73  ·  MySQL 9.7 (utf8mb4)
Livewire 4.4  ·  Alpine  ·  Tailwind 4 (CSS-first @theme)  ·  Vite 7
Filament 5.12  ·  Socialite 5.33
endroid/qr-code 6.1  ·  mPDF 8.3  ·  intervention/image 4.1
spatie/laravel-activitylog 5.1  ·  spatie/laravel-backup 10.5
```

### المخرجات الأربعة
```
1. الموقع التسويقي     /  /pricing  /how-it-works  /examples  /faq ...
2. الصفحات العامة      /{slug}  /{slug}/{category}  /p/{code}   ← قلب المنتج
3. لوحة التاجر         /dashboard/*
4. لوحة الإدارة        /admin/*   (Filament)
```

### الحالة دلوقتي
**P0 خلص.** الموجود فعليًا في الريبو:

```
app/Models/            14 موديل كاملة بالعلاقات والـ casts
app/Services/          CodeGenerator.php فقط
app/Console/Commands/  SpikeQrPdfCommand.php (تجربة إثبات، مش كود منتج)
database/migrations/   18 مايجريشن (23 جدول على MySQL)
database/factories/    8 factories
database/seeders/      5 seeders (تاجر تجريبي بـ 28 منتج)
resources/css/         tokens.css (نظام الألوان كامل) + app.css
public/fonts/tajawal/  6 ملفات woff2
routes/web.php         فاضي — فيه الـ welcome بس
tests/                 19 اختبار ناجح
```

**مفيش ولا صفحة واحدة من صفحات المنتج اتعملت لسه.** كل اللي تحت ده شغل جديد.

---

## 2. البيئة والتشغيل

### 🚨 أول حاجة تعرفها

الـ `php` اللي في الـ PATH هو **XAMPP 8.2.12** — **متستخدموش**. الحزم مش بتتثبت عليه أصلاً (`pint` عايز 8.3، `endroid/qr-code` عايز 8.5، spatie عايزين 8.4).

```bash
PHP       /c/laraenv/php/current/php.exe      # 8.4.20
COMPOSER  /c/laraenv/bin/composer.phar
MySQL     /c/laraenv/mysql                     # port 3306
الموقع    http://priceprivate.test             # nginx من laraenv
```

**لازم تشغّل laraenv (nginx + mysql) قبل أي حاجة.**

### اعمل alias يريّحك
```bash
# في ~/.bashrc
alias p='/c/laraenv/php/current/php.exe'
alias pa='/c/laraenv/php/current/php.exe artisan'
alias pc='/c/laraenv/php/current/php.exe /c/laraenv/bin/composer.phar'
```

### التشغيل من الصفر
```bash
pc install
npm install
pa migrate:fresh --seed
npm run dev
```

### أوامر يومية
```bash
pa test                      # 19 اختبار، SQLite in-memory، ثانيتين
p vendor/bin/pint            # تنسيق — إجباري قبل كل commit
pa spike:qr-pdf              # إثبات QR + PDF عربي (مرجع للتنفيذ)
pa migrate:fresh --seed      # تصفير الداتا
```

### حسابات تجريبية
| الدور | الإيميل | الرابط |
|---|---|---|
| أدمن | `admin@priceprivate.test` | `/admin` |
| تاجر | `demo@priceprivate.test` | `/el-amana` |

---

## 3. المعمارية العامة

### الطبقات
```
┌──────────────────────────────────────────────────────────────┐
│  العرض                                                        │
│  Blade views  ·  Livewire components  ·  Filament resources  │
│  مسؤوليتها: العرض والتفاعل فقط. صفر منطق أعمال. صفر استعلام.  │
└───────────────────────────┬──────────────────────────────────┘
                            │
┌───────────────────────────▼──────────────────────────────────┐
│  HTTP                                                         │
│  Controllers (نحيفة)  ·  Form Requests  ·  Middleware         │
│  مسؤوليتها: تحقق من المدخل، نادي السيرفس، رجّع response.       │
└───────────────────────────┬──────────────────────────────────┘
                            │
┌───────────────────────────▼──────────────────────────────────┐
│  الأعمال  ← كل المنطق هنا                                     │
│  Services  ·  Actions  ·  DTOs  ·  Enums  ·  Policies         │
└───────────────────────────┬──────────────────────────────────┘
                            │
┌───────────────────────────▼──────────────────────────────────┐
│  البيانات                                                     │
│  Eloquent Models  ·  Query scopes  ·  Events                  │
└──────────────────────────────────────────────────────────────┘

جانبيًا:  Events → Listeners → (إبطال كاش، إيميلات)
          Jobs (Queue) لأي عملية > ثانيتين
```

### قواعد معمارية غير قابلة للتفاوض
1. **منطق الأعمال في Service.** ممنوع في Controller أو Livewire component أو Blade.
2. **Policy لكل صلاحية.** ممنوع `if ($user->id === $x->user_id)` في أي view أو controller.
3. **Form Request** لكل مدخل. التحقق على السيرفر مش على الواجهة.
4. **Queue** لأي عملية > ثانيتين (PDF، إيميل، ضغط صور).
5. **`preventLazyLoading` مفعّل** في غير الإنتاج → أي N+1 بيرمي استثناء وقت التطوير. (متعمول في `AppServiceProvider` خلاص.)
6. **`preventSilentlyDiscardingAttributes` مفعّل** → mass assignment لحقل محمي بيرمي استثناء بدل ما يتجاهل بصمت.

### دورة حياة أهم request في النظام: `GET /p/{code}`

```
الموبايل بيمسح الكود
   │
   ▼
nginx ──► public/index.php ──► Laravel
   │
   ├─ Route: GET /p/{code}  →  ProductController@show
   │
   ├─ ProductResolver::resolve($code)              ← كل منطق الـ fallback هنا
   │     │
   │     ├─ Product::withTrashed()->with('merchant')->where('public_code',$code)->first()
   │     │
   │     └─ بيرجع ProductResolution (enum) + DTO:
   │          Ok                 → اعرض الصفحة
   │          Hidden             → اعرض الصفحة + شارة «مش متاح دلوقتي» + السعر مكتوم
   │          ProductDeleted     → 302 لقائمة المحل + رسالة
   │          MerchantSuspended  → 403 + صفحة «القائمة موقوفة مؤقتًا»
   │          MerchantDeleted    → 410 Gone
   │          NotFound           → 404 مخصصة
   │
   ├─ ResponseCache (TTL ساعة، مفتاح فيه cache_version بتاع التاجر)
   │
   ├─ view('public.product')   ← SSR، صفر JS ضروري لعرض السعر
   │
   └─ terminate(): TrackScan middleware ──► ScanCounter (upsert مجمّع يومي)
         ← بعد ما الـ response اتبعت، عشان ما يبطّأش الصفحة
```

**لاحظ:** `TrackScan` بيشتغل في `terminate()` مش في `handle()` — العميل بيشوف السعر الأول، العدّ بيحصل بعدين.

### هيكل المجلدات المستهدف

```
app/
├── Actions/                    عمليات ذرّية بخطوة واحدة
│   ├── Products/
│   │   ├── CreateProduct.php
│   │   ├── UpdateProductPrice.php
│   │   └── ReorderProducts.php
│   └── Merchants/
│       ├── CompleteOnboarding.php
│       └── ChangeSlug.php
│
├── Console/Commands/
│   ├── RollSubscriptions.php        يومي — دورة حياة الاشتراك
│   ├── NotifyExpiringSubscriptions.php
│   └── SpikeQrPdfCommand.php        (موجود — مرجع)
│
├── DTOs/
│   ├── ProductResolution.php
│   ├── QrOptions.php
│   ├── CheckoutSession.php
│   └── WebhookPayload.php
│
├── Enums/
│   ├── ShareTargetType.php          store | category | product
│   ├── ProductResolutionStatus.php
│   ├── PrintTemplate.php
│   ├── SubscriptionStatus.php
│   └── AdPlacement.php
│
├── Events/
│   ├── PriceUpdated.php
│   ├── ProductChanged.php
│   ├── MerchantUpdated.php
│   └── SubscriptionChanged.php
│
├── Http/
│   ├── Controllers/
│   │   ├── Public/
│   │   │   ├── ProductController.php      /p/{code}
│   │   │   ├── MenuController.php         /{slug}  /{slug}/{category}
│   │   │   ├── QrImageController.php      /qr/{code}.png|svg
│   │   │   └── ReportController.php
│   │   ├── Marketing/PageController.php
│   │   ├── Auth/SocialiteController.php
│   │   └── Billing/{CheckoutController, WebhookController}.php
│   ├── Middleware/
│   │   ├── EnsureMerchant.php
│   │   ├── EnsureOnboarded.php
│   │   ├── CheckProductLimit.php
│   │   └── TrackScan.php
│   └── Requests/
│
├── Jobs/
│   ├── GeneratePrintSheet.php
│   ├── OptimizeUploadedImage.php
│   └── GenerateInvoicePdf.php
│
├── Listeners/
│   └── InvalidateMerchantCache.php
│
├── Livewire/
│   ├── Merchant/
│   │   ├── Dashboard.php
│   │   ├── ProductManager.php
│   │   ├── QuickPriceEditor.php      ⚡ أهم مكوّن
│   │   ├── QrStudio.php
│   │   ├── PrintStudio.php
│   │   ├── StoreSettings.php
│   │   └── Billing.php
│   └── Public/
│       ├── MenuSearch.php
│       └── ReportButton.php
│
├── Models/                     ✅ موجودة كلها
├── Policies/
├── Rules/
│   └── AvailableSlug.php
│
├── Services/
│   ├── CodeGenerator.php            ✅ موجود
│   ├── ProductResolver.php          ⭐ قلب نظام الـ QR
│   ├── QrService.php
│   ├── PrintSheetService.php
│   ├── ImageProcessor.php
│   ├── ScanCounter.php
│   ├── MerchantCache.php
│   ├── SubscriptionService.php
│   ├── ProductLimitChecker.php
│   ├── InvoiceService.php
│   └── PaymentGateway/
│       ├── PaymentGateway.php       (interface)
│       ├── MockDriver.php
│       ├── PaymobDriver.php
│       └── FawryDriver.php
│
└── Support/
    └── PriceFormatter.php

resources/
├── css/tokens.css              ✅ موجود — المصدر الوحيد للألوان
└── views/
    ├── components/             مكتبة المكونات (P1)
    ├── layouts/                marketing | merchant | public | print
    ├── marketing/
    ├── public/                 menu · product · category · suspended · gone
    ├── merchant/
    └── print/                  قوالب PDF
```

---

## 4. نموذج البيانات

### العلاقات

```
users ──1:1──► merchants ──1:n──► categories ──1:n──► products
                   │                                      │
                   ├──1:n──► subscriptions ──n:1──► plans │
                   │              │                        │
                   ├──1:n──► payments ──1:1──► invoices    │
                   │                                        │
                   ├──1:n──► scan_stats                     │
                   ├──1:n──► share_targets                  │
                   └──morph─► reports ◄──morph──────────────┘
                                                             │
                                          products ──1:n──► price_history

مستقلة:  plans  ·  ad_slots  ·  settings  ·  activity_log
```

### الجداول الحرجة وما لا يجوز كسره

#### `products` — الجدول الأهم
```sql
id                 bigint unsigned   PK
merchant_id        bigint unsigned   FK → merchants   NOT NULL
category_id        bigint unsigned   FK → categories  NULL (nullOnDelete)
name               varchar(255)      NOT NULL
slug               varchar(255)      NOT NULL
public_code        char(6)           UNIQUE  NOT NULL   🔒 immutable للأبد
image_path         varchar(255)      NULL
short_description  varchar(140)      NULL
price              decimal(12,2)     NULL      ← NULL = «السعر غير متاح حاليًا»
currency           varchar(3)        'EGP'
unit               varchar(20)       NULL
is_visible         tinyint(1)        NOT NULL
sort_order         smallint unsigned NOT NULL
price_updated_at   timestamp         NULL      ← يغذّي شارة التحديث
created_at / updated_at / deleted_at           ← soft delete إجباري

INDEX products_listing_index  (merchant_id, is_visible, sort_order)
INDEX products_category_index (category_id, is_visible, sort_order)
```

**لماذا soft delete إجباري:** لو حذفنا المنتج فعليًا، الكود المطبوع في المحل هيرجع 404. الـ resolver محتاج يلاقي الصف عشان يعرف لأي محل يحوّل العميل.

**لماذا `public_code` محمي من اتجاهين:**
```php
// 1) محمي من mass assignment
protected $guarded = ['public_code'];

// 2) محمي من أي تعديل بعد الإنشاء — في Product::booted()
static::updating(function (Product $product): void {
    if ($product->isDirty('public_code')) {
        throw new RuntimeException('public_code is immutable: printed QR labels would stop working.');
    }
});
```
مغطّى بـ 7 اختبارات في `tests/Feature/ProductCodeTest.php`.

#### `merchants` — الحقول اللي ليها سلوك خاص
| الحقل | السلوك |
|---|---|
| `slug` | يتغيّر **مرة واحدة** خلال أول 7 أيام (`Merchant::canChangeSlug()`) |
| `slug_changed_at` | بيقفل إمكانية التغيير للأبد لما يتملا |
| `default_qr_target_type` + `default_qr_target_id` | «اللينك اللي التاجر حاططه في الكود» — §6.3 |
| `prices_updated_at` | آخر تعديل لأي سعر — بيغذّي تنبيه «أسعارك مش متحدّثة» |
| `cache_version` | عدّاد إبطال الكاش — `bumpCacheVersion()` |
| `products_count` | عدّاد مخزّن لفرض حد الخطة بدون `COUNT(*)` |

#### `scan_stats` — مجمّع يومي
```sql
UNIQUE (merchant_id, target_type, target_id, date, source)
```
صف واحد لكل (هدف × يوم × مصدر). **مفيش سجل لكل زيارة** — خصوصية + تكلفة تخزين. الكتابة بـ upsert ذرّي.

#### `payments` — الـ idempotency
```sql
gateway_transaction_id  varchar  UNIQUE  NULL
```
ده اللي بيمنع إن webhook مكرر يفعّل اشتراكين. القيد على مستوى الداتابيز مش على مستوى الكود.

### ثوابت ملزمة
- كل المبالغ `DECIMAL(12,2)` — **ممنوع float** نهائيًا.
- كل الجداول `utf8mb4_unicode_ci` (متحقق: 23/23).
- حذف قسم → منتجاته تروح `category_id = NULL` («غير مصنّف») — **ما تتحذفش**.

---

## 5. خريطة المسارات وترتيبها الحرج

### ⚠️ الفخ رقم 1 في المشروع

`/{merchant:slug}` هو **catch-all** — لو اتسجّل قبل أي راوت تاني هيبلعه. لازم يكون **آخر راوت مسجّل في الملف**.

```php
// routes/web.php — الترتيب ده إلزامي

// 1) الموقع التسويقي (مسارات ثابتة)
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/how-it-works', ...);
Route::get('/pricing', ...)->name('pricing');
Route::get('/examples', ...);
Route::get('/faq', ...);
Route::get('/contact', ...);   Route::post('/contact', ...)->middleware('throttle:3,60');
Route::view('/terms', ...);    Route::view('/privacy', ...);

// 2) المصادقة
Route::get('/login', ...)->name('login');
Route::get('/auth/{provider}/redirect', ...)->whereIn('provider', ['google','facebook']);
Route::get('/auth/{provider}/callback', ...);
Route::post('/logout', ...);

// 3) لوحة التاجر
Route::middleware(['auth','merchant','onboarded'])->prefix('dashboard')->group(...);

// 4) الدفع
Route::get('/payment/callback', ...);                      // عرض فقط
Route::post('/payment/webhook/{gateway}', ...)             // التفعيل الحقيقي
     ->withoutMiddleware([VerifyCsrfToken::class]);

// 5) أصول عامة
Route::get('/qr/{code}.{ext}', [QrImageController::class,'show'])->whereIn('ext',['png','svg']);
Route::post('/report/{type}/{id}', ...)->middleware('throttle:3,60');

// 6) ⭐ صفحة المنتج المباشرة — قبل الـ catch-all
Route::get('/p/{code}', [ProductController::class,'show'])
     ->where('code', '[A-HJ-NP-Z2-9]{6}')
     ->name('product.show');

// 7) 🚨 الـ catch-all — آخر حاجة خالص
Route::get('/{merchant:slug}', [MenuController::class,'show'])
     ->where('merchant', '[a-z0-9][a-z0-9-]{1,38}[a-z0-9]')
     ->name('menu.show');

Route::get('/{merchant:slug}/p/{code}', [ProductController::class,'show']);   // رابط أطول للمشاركة
Route::get('/{merchant:slug}/{category:slug}', [MenuController::class,'category'])
     ->name('menu.category');
```

### الحماية التانية: الكلمات المحجوزة
موجودة خلاص في [`config/egypt.php`](config/egypt.php) تحت `reserved_slugs` (33 كلمة). تتفرض وقت اختيار الـ slug عن طريق `Rules\AvailableSlug`:

```php
final class AvailableSlug implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (in_array($value, config('egypt.reserved_slugs'), true)) {
            $fail('الرابط ده محجوز، اختار واحد تاني.');
        }
    }
}
```

**اختبار إلزامي:** راوت لكل كلمة محجوزة بيتأكد إنها مابتروحش للـ `MenuController`.

### ملخص المسارات
| الطريقة | المسار | الوجهة | Auth |
|---|---|---|---|
| GET | `/p/{code}` | ⭐ صفحة سعر المنتج | ❌ |
| GET | `/{slug}` | قائمة المحل | ❌ |
| GET | `/{slug}/{category}` | القائمة على قسم | ❌ |
| GET | `/{slug}/p/{code}` | نفس صفحة المنتج | ❌ |
| GET | `/qr/{code}.png\|svg` | صورة الكود (كاش دائم) | ❌ |
| POST | `/report/{type}/{id}` | بلاغ (3/ساعة) | ❌ |
| GET | `/dashboard*` | لوحة التاجر | ✅ merchant |
| POST | `/payment/webhook/{gw}` | 🔒 تفعيل الاشتراك | توقيع HMAC |
| * | `/admin*` | Filament | ✅ admin/moderator |

---

## 6. الوحدة 1 — الحسابات والأونبوردنج

**المرحلة:** P2 · **المرجع:** `PROJECT_SPEC.md` §5.1، §23

### الهدف المقاس
**من التسجيل لأول منتج في أقل من 3 دقايق.**

### الأدوار
```php
// App\Models\User — موجودة خلاص
ROLE_MERCHANT  ROLE_MODERATOR  ROLE_ADMIN
isMerchant()  isModerator()  isAdmin()
// ملاحظة: isModerator() بترجع true للأدمن كمان — الأدمن بيعمل كل اللي المشرف بيعمله
```

### تدفق الدخول
```
/login
  └─► زرين: جوجل · فيسبوك
        │
        ▼
  /auth/{provider}/redirect  ──► Socialite ──► مزوّد الخدمة
        │
        ▼
  /auth/{provider}/callback
        │
        ├─ findOrCreateUser(provider, providerId, email, name, avatar)
        │     بيدوّر بـ (provider, provider_id) الأول  [UNIQUE موجود]
        │     لو ملقاش، بيدوّر بالإيميل ← يربط المزوّد بالحساب القديم
        │     لو ملقاش، ينشئ user جديد role=merchant
        │
        ├─ تحديث last_login_at
        │
        └─ redirect:
              مفيش merchant؟  → /onboarding
              فيه؟            → /dashboard
```

**ملاحظة أمنية:** الربط بالإيميل مقبول هنا لأن جوجل وفيسبوك الاتنين بيأكدوا الإيميل. لو مزوّد رجّع `email = null` → اعمل حساب جديد ولا تربط.

### الأونبوردنج
```php
// App\Actions\Merchants\CompleteOnboarding
public function handle(User $user, OnboardingData $data): Merchant
```
الحقول: اسم المحل*، اللوجو، المحافظة* (27 في `config/egypt.php`)، النشاط* (16)، التليفون*، الواتساب، العنوان، ساعات العمل.

التحقق:
- التليفون المصري: `/^01[0125][0-9]{8}$/`
- الـ slug: `AvailableSlug` + `unique:merchants,slug` + `[a-z0-9-]{3,40}`
- اللوجو: يعدّي على `ImageProcessor` (قسم 7)

### Middleware
```php
EnsureMerchant   // عنده merchant؟ لأ → /onboarding
EnsureOnboarded  // الـ merchant كامل البيانات؟
```

### Policies
```php
MerchantPolicy::update(User, Merchant)   → $user->id === $merchant->user_id
CategoryPolicy::*(User, Category)        → $category->merchant->user_id === $user->id
ProductPolicy::*(User, Product)          → $product->merchant->user_id === $user->id
```
**اختبار إلزامي:** تاجر A يحاول يعدّل منتج تاجر B → **403**.

### الالتفاف على حجب فيسبوك
مراجعة Meta بتاخد أسابيع. الحل: فلاج في `settings`:
```php
Setting::get('facebook_login_enabled', false)
```
الزر بيظهر لما الفلاج يبقى `true` بس. جوجل شغال من اليوم الأول. **مفيش تاسك محجوب.**

---

## 7. الوحدة 2 — المنتجات والأسعار

**المرحلة:** P3 · **المرجع:** `PROJECT_SPEC.md` §8.2

### 7.1 معالجة الصور

```php
final class ImageProcessor
{
    /** @return string المسار النسبي بعد التخزين */
    public function store(UploadedFile $file, string $directory): string;

    /** يولّد 64 / 400 / 800 للـ srcset */
    public function generateVariants(string $path): array;
}
```

الخطوات بالترتيب:
1. تحقق من الامتداد **والـ MIME الحقيقي** (`finfo`) — مش الـ MIME اللي المتصفح باعته.
2. حد 5MB.
3. **إعادة ترميز** الصورة بالكامل (بيقتل أي payload مدسوس).
4. تصغير لأقصى 1200px على الضلع الأكبر.
5. تحويل WebP جودة 82 → الهدف < 200KB.
6. توليد مقاسات 64/400/800.
7. لو الملف > 1MB → الخطوات 4–6 في `OptimizeUploadedImage` job.

### 7.2 ⚡ تعديل الأسعار السريع — أهم شاشة في المنتج

**الهدف المقاس: 20 سعر في أقل من دقيقة على موبايل حقيقي.**

```php
// App\Livewire\Merchant\QuickPriceEditor
class QuickPriceEditor extends Component
{
    public array $prices = [];      // [productId => '95.50']
    public array $saved  = [];      // [productId => true]  لعرض «اتحفظ ✓»
    public string $search = '';
    public ?int $categoryFilter = null;
    public bool $staleOnly = false;

    public function updatedPrices(mixed $value, string $key): void
    {
        $this->validateOnly("prices.$key", [
            "prices.*" => ['nullable','numeric','min:0','max:9999999999','decimal:0,2'],
        ]);

        app(UpdateProductPrice::class)->handle(
            $this->productFor((int) $key),
            $value === '' ? null : (float) $value,
        );

        $this->saved[$key] = true;
    }
}
```

**قرارات واجهة مهمة:**
- `inputmode="decimal"` → كيبورد أرقام على الموبايل (بدونه الموبايل بيفتح كيبورد كامل والتاجر بيتعب).
- `wire:model.blur` مش `.live` → حفظ عند الخروج من الخانة، مش مع كل حرف.
- `wire:key` على كل صف عشان Livewire ما يلخبطش الصفوف عند الفلترة.
- الصفوف مجمّعة بالقسم مع عنوان لاصق (sticky).
- زر «حفظ الكل» ثابت تحت كشبكة أمان.

### 7.3 الـ Action اللي بيحدّث السعر

```php
final class UpdateProductPrice
{
    public function handle(Product $product, ?float $newPrice): void
    {
        $old = $product->price;

        if ((float) $old === (float) $newPrice) {
            return;   // مفيش تغيير فعلي — بلاش كتابة ولا إبطال كاش
        }

        DB::transaction(function () use ($product, $old, $newPrice) {
            $product->forceFill([
                'price'            => $newPrice,
                'price_updated_at' => now(),
            ])->save();

            PriceHistory::create([
                'product_id' => $product->id,
                'old_price'  => $old,
                'new_price'  => $newPrice,
                'changed_at' => now(),
            ]);

            $product->merchant->forceFill(['prices_updated_at' => now()])->save();
        });

        PriceUpdated::dispatch($product);   // → InvalidateMerchantCache
    }
}
```

**تحسين:** عند حفظ جماعي، اعمل `bumpCacheVersion()` مرة واحدة في آخر الدفعة مش مع كل سطر — استخدم flag على الـ event أو اعمل الإبطال في `dehydrate()` بتاع المكوّن.

### 7.4 الترتيب بالسحب
مكتبة `SortableJS` (مجانية، 12KB) عبر Alpine. **لازم تشتغل باللمس على الموبايل** مش الماوس بس — جرّبها على جهاز حقيقي. الحفظ بـ `ReorderProducts` action يعمل `CASE WHEN` واحد بدل n استعلام.

### 7.5 فرض حد المنتجات
```php
final class ProductLimitChecker
{
    public function limitFor(Merchant $m): ?int;    // null = غير محدود
    public function canAdd(Merchant $m): bool;
    public function remaining(Merchant $m): ?int;
}
```
بيقرأ الحد من `plans.product_limit` بتاع الاشتراك الحالي، وبيرجع لـ `Setting::get('free_product_limit')` كافتراضي.
بيتفرض في `CheckProductLimit` middleware + تحقق في الـ action (طبقتين).

⚠️ `FREE_LIMIT` لسه مش متحدد (§31 Q2 — المقترح 25). **مابيحجبش الشغل** — القيمة من `settings` وبتتغيّر من لوحة الإدارة.

---

## 8. الوحدة 3 — الروابط وأكواد QR ⭐

**المرحلة:** P4 · **المرجع:** `PROJECT_SPEC.md` §6 · **ده قلب المنتج.**

### 8.1 المستويات الثلاثة
```php
enum ShareTargetType: string
{
    case Store    = 'store';      // /{slug}
    case Category = 'category';   // /{slug}/{category}
    case Product  = 'product';    // /p/{code}   ⭐
}
```

### 8.2 مولّد الأكواد — ✅ متعمول خلاص
```php
App\Services\CodeGenerator
  const ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';   // 31 حرف، من غير 0 O 1 I L
  const LENGTH = 6;                                      // 31^6 ≈ 887 مليون
  uniqueCode(): string        // بيتأكد من الـ DB شاملًا المحذوف soft-deleted
  randomCode(): string        // random_int — آمن تشفيريًا، غير متسلسل
  exists(string): bool
  static isValid(string): bool
```
مغطّى بـ 5 اختبارات وحدة + 7 اختبارات feature.

### 8.3 ⭐ ProductResolver — أهم سيرفس في المشروع

```php
enum ProductResolutionStatus
{
    case Ok;
    case Hidden;
    case ProductDeleted;
    case MerchantSuspended;
    case MerchantDeleted;
    case NotFound;
}

final readonly class ProductResolution
{
    public function __construct(
        public ProductResolutionStatus $status,
        public ?Product  $product  = null,
        public ?Merchant $merchant = null,
    ) {}

    public function httpStatus(): int;        // 200 | 302 | 403 | 404 | 410
    public function shouldShowPrice(): bool;  // Ok فقط
}

final class ProductResolver
{
    public function resolve(string $code): ProductResolution
    {
        if (! CodeGenerator::isValid($code)) {
            return new ProductResolution(NotFound);
        }

        $product = Product::withTrashed()
            ->with('merchant')              // withTrashed على العلاقة كمان
            ->where('public_code', $code)
            ->first();

        if (! $product)                          return new ProductResolution(NotFound);

        $merchant = $product->merchant;

        if ($merchant->trashed())                return new ProductResolution(MerchantDeleted, merchant: $merchant);
        if ($merchant->status === 'suspended')   return new ProductResolution(MerchantSuspended, merchant: $merchant);
        if ($product->trashed())                 return new ProductResolution(ProductDeleted, merchant: $merchant);
        if (! $product->is_visible)              return new ProductResolution(Hidden, $product, $merchant);

        return new ProductResolution(Ok, $product, $merchant);
    }
}
```

**لاحظ:** حالة انتهاء الاشتراك **مش** في القائمة دي بالمرة. الاشتراك المنتهي **لا يؤثر على صفحة العميل** إطلاقًا (القرار D8) — بيأثر على الإعلانات واللون والشارة بس.

### جدول السلوك — كل سطر فيه لازم يبقى له اختبار

| الحالة | HTTP | اللي العميل بيشوفه |
|---|---|---|
| `Ok` | 200 | صفحة المنتج كاملة |
| `Hidden` | 200 | الصفحة + شارة «المنتج ده مش متاح دلوقتي» + **السعر مكتوم** + زر القائمة |
| `ProductDeleted` | 302 | تحويل لـ `/{slug}` + «المنتج ده اتشال من القائمة. دي باقي أسعار {المحل}» |
| `MerchantSuspended` | 403 | «القائمة دي موقوفة مؤقتًا» — **بدون أي بيانات تواصل** |
| `MerchantDeleted` | 410 | صفحة مهذبة |
| `NotFound` | 404 | «الكود ده مش موجود» + زر الرئيسية + «اعمل قائمتك» |

🚨 **ممنوع نهائيًا** رجوع 404 فاضية أو صفحة لارافيل الافتراضية لأي كود. كود مطبوع بيرجّع 404 = فقدان ثقة كامل.

### 8.4 الهدف الافتراضي للكود الرئيسي

ده الترجمة الحرفية لطلب العميل: «اللينك اللي أنا حاططه في الـ QR Code».

```php
// Merchant: default_qr_target_type (enum) + default_qr_target_id (nullable)

final class MerchantCache { /* ... */ }

// في QrStudio:
public function defaultTargetUrl(Merchant $m): string
{
    return match ($m->default_qr_target_type) {
        'product'  => Product::find($m->default_qr_target_id)?->publicUrl()  ?? $m->publicUrl(),
        'category' => route('menu.category', [$m, $category])                ?? $m->publicUrl(),
        default    => $m->publicUrl(),
    };
}
```

**قاعدة حاسمة:** تغيير الهدف **ما يغيّرش صورة الكود ولا الرابط المطبوع** — بيغيّر الوجهة بس. ولو الهدف اتحذف → بيرجع تلقائيًا لقائمة المحل (لاحظ الـ `?? $m->publicUrl()`).

### 8.5 خدمة الـ QR

```php
final readonly class QrOptions
{
    public function __construct(
        public string $format = 'png',    // png | svg | pdf
        public int    $size = 1024,
        public bool   $withLogo = false,
        public string $foreground = '#000000',
        public string $background = '#FFFFFF',
    ) {}

    public function cacheKey(): string;   // hash للخيارات → اسم الملف
}

final class QrService
{
    public function generate(string $url, QrOptions $o): string;   // بيرجع مسار الملف
    public function forProduct(Product $p, QrOptions $o): string;
    public function forMerchant(Merchant $m, QrOptions $o): string;
    public function forCategory(Category $c, QrOptions $o): string;
}
```

التنفيذ — نسخه من `SpikeQrPdfCommand` اللي شغال ومتأكد منه:
```php
$qr = new QrCode(
    data: $url,
    encoding: new Encoding('UTF-8'),
    errorCorrectionLevel: $o->withLogo
        ? ErrorCorrectionLevel::High      // إلزامي مع اللوجو
        : ErrorCorrectionLevel::Medium,
    size: $o->size,
    margin: (int) round($o->size * 0.04),
);

$result = $o->withLogo
    ? (new PngWriter)->write($qr, new Logo(path: $logoPath, resizeToWidth: (int)($o->size * 0.21), punchoutBackground: true))
    : (new PngWriter)->write($qr);
```

**الكاش:** الملف بيتكتب على disk `public` في `qr/{code}-{optionsHash}.{ext}` ويتسرف بـ:
```
Cache-Control: public, max-age=31536000, immutable
```
الكود ثابت للأبد → الصورة ثابتة للأبد. ما يتولّدش تاني أبدًا.

**الحد الأدنى للطباعة: 2.5 سم.** حذّر في الواجهة لو المستخدم اختار أصغر.

### 8.6 عدّ المسح

```php
final class ScanCounter
{
    public function record(Merchant $m, ShareTargetType $type, ?int $targetId, string $source): void
    {
        DB::table('scan_stats')->upsert(
            [[
                'merchant_id' => $m->id,
                'target_type' => $type->value,
                'target_id'   => $targetId,
                'date'        => today(),
                'source'      => $source,
                'count'       => 1,
            ]],
            ['merchant_id','target_type','target_id','date','source'],   // المفتاح الفريد
            ['count' => DB::raw('count + 1')],
        );
    }
}
```

المصدر من الـ query string: `?s=qr` في أكواد QR، `?s=l` في روابط المشاركة، وإلا `direct`.
بيتنادى من `TrackScan` middleware في `terminate()` — **بعد** ما الـ response اتبعت.
⛔ **صفر بيانات شخصية**: لا IP ولا كوكيز تتبّع.

### 8.7 المشاركة
`navigator.share` على الموبايل (Alpine، 10 أسطر). Fallback على الديسكتوب: نسخ + روابط واتساب/فيسبوك/تليجرام. **مفيش أي تكامل خارجي مدفوع.**

---

## 9. الوحدة 4 — الصفحات العامة ⭐

**المرحلة:** P5 · **المرجع:** `PROJECT_SPEC.md` §6.4، §9

### 9.1 صفحة المنتج `/p/{code}` — الشاشة الأهم

ترتيب العناصر ملزم:

```
┌─────────────────────────────────┐
│ [لوجو 32px] اسم المحل  [موثّق]  │  ← ضغطة = القائمة الكاملة
├─────────────────────────────────┤
│                                 │
│      صورة المنتج 4:3            │  lazy + أبعاد محددة (CLS = 0)
│                                 │
├─────────────────────────────────┤
│ زيت عافية 1 لتر                 │  22px / 700 / --ink
│                                 │
│   95.50  جنيه  / لتر            │  ⭐ 40px موبايل · 48px ديسكتوب
│                                 │     وزن 700 · لون --brand
│ 🟢 اتحدّث النهاردة               │  chip
│ آخر تحديث: الأحد 22 نوفمبر، 3:40م│  12px / --muted
├─────────────────────────────────┤
│ [ واتساب ]                      │  عرض كامل · --whatsapp
│ [ اتصال  ]                      │  عرض كامل · --brand
├─────────────────────────────────┤
│ [مكان إعلان]                    │  مجاني فقط · min-height محجوز
├─────────────────────────────────┤
│ [ شوف باقي قائمة الأسعار ]      │  outline
├─────────────────────────────────┤
│ منتجات من نفس القسم →→→         │  تمرير أفقي · 6 منتجات · lazy
├─────────────────────────────────┤
│ مدعوم بـ «السعر مش ع الخاص»     │
│ [بلاغ]      [اعمل قائمتك مجانًا] │
└─────────────────────────────────┘
```

**ميزانية الأداء — غير قابلة للتفاوض:**

| المقياس | الحد |
|---|---|
| FCP على 3G بطيء | **< 1.2s** |
| LCP | < 1.8s |
| حجم الصفحة (بدون صورة المنتج) | **< 120KB** |
| CLS | **0** |
| JS ضروري لعرض السعر | **صفر** |
| Lighthouse Performance | ≥ 90 |
| Lighthouse Accessibility | ≥ 95 |

**إزاي نوصلها:**
- Blade عادي، **مش** Livewire. Livewire بيضيف 30KB+ وما فيش داعي له هنا.
- CSS حرج inline في `<head>`، الباقي مؤجل.
- خط Tajawal: preload للـ subset العربي 400 و 700 بس (9KB لكل واحد).
- الصورة: `<img width height loading="lazy" srcset>`.
- Response cache ساعة + إبطال بالـ version.
- أماكن الإعلانات بـ `min-height` محجوز.

**شارة التحديث:**
```php
// App\Support\PriceFormatter
public static function freshness(?Carbon $at): array
{
    if (! $at)                       return ['neutral', 'مفيش تاريخ تحديث'];
    if ($at->isToday())              return ['ok',      'اتحدّث النهاردة'];
    if ($at->diffInDays() <= 7)      return ['neutral', "اتحدّث من {$at->diffInDays()} يوم"];
    if ($at->diffInDays() <= 30)     return ['neutral', "آخر تحديث من {$at->diffInDays()} يوم"];
    return ['warn', "آخر تحديث من {$at->diffInDays()} يوم"];
}
```

**رسالة الواتساب الجاهزة:**
```
https://wa.me/{رقم بالصيغة الدولية}?text={urlencode}
«السلام عليكم، حابب أسأل عن: {اسم المنتج} — {رابط المنتج}»
```

### 9.2 صفحة القائمة `/{slug}`
- بحث **client-side** فوري (القائمة محمّلة أصلاً) — صفر request. يظهر لو > 10 منتجات بس.
- شرائط أقسام أفقية.
- ضغطة على المنتج = **صفحة المنتج المباشرة**.
- أزرار اتصال sticky تحت.
- أقصى **إعلانين**.
- لو > 100 منتج → تحميل تدريجي بالقسم.
- LCP < 2.0s.

### 9.3 البلاغات
```php
'reporter_ip_hash' => hash('sha256', $request->ip() . config('app.key'))
```
IP **مُجزّأ** مش مخزّن صريح. Rate limit 3/ساعة/IP.

---

## 10. الوحدة 5 — استوديو الطباعة

**المرحلة:** P6 · **المرجع:** `PROJECT_SPEC.md` §6.7

### القرار متحسم: mPDF
جُرّب فعليًا في P0 واتأكد بصريًا: حروف عربية موصولة، RTL سليم، تشكيل (`مُحدَّث`، `لؤلؤة`)، أرقام لاتينية، جداول RTL. `dompdf` بيكسر التشكيل، و`spatie/laravel-pdf` محتاج Node + Chrome على السيرفر.

**`SpikeQrPdfCommand` فيه إعداد mPDF الشغّال — انسخه حرفيًا:**
```php
$fontDirs = (new ConfigVariables)->getDefaults()['fontDir'];
$fontData = (new FontVariables)->getDefaults()['fontdata'];

new Mpdf([
    'mode'        => 'utf-8',
    'format'      => 'A4',
    'tempDir'     => storage_path('app/mpdf'),
    'fontDir'     => array_merge($fontDirs, [storage_path('fonts')]),
    'fontdata'    => $fontData + ['tajawal' => [
        'R' => 'Tajawal-Regular.ttf',
        'M' => 'Tajawal-Medium.ttf',
        'B' => 'Tajawal-Bold.ttf',
        'useOTL'     => 0xFF,    // ← بدون دي الحروف بتتفصل
        'useKashida' => 75,
    ]],
    'default_font'   => 'tajawal',
    'directionality' => 'rtl',
    'autoScriptToLang' => true,
    'autoLangToFont'   => false,
]);
```
خطوط Tajawal TTF موجودة خلاص في `storage/fonts/`.

### القوالب الخمسة
```php
enum PrintTemplate: string
{
    case LabelSheet  = 'label_sheet';    // A4 — 3×8 = 24 ملصق   ✅ مُجرَّب
    case ShelfTalker = 'shelf_talker';   // 10×3 سم
    case TableStand  = 'table_stand';    // A5 مطوي
    case Poster      = 'poster';         // A4 / A3
    case StoreCard   = 'store_card';     // 9×5 سم
}
```

```php
final class PrintSheetService
{
    public function generate(Merchant $m, PrintTemplate $t, Collection $products): string;
}
```

### قيود إلزامية
- كل قالب فيه **لوجو المحل** + نص ثابت: **«امسح الكود تشوف السعر الحالي»**.
- خطوط قص `dashed 0.25pt` بلون `--border-strong`.
- الكود ≥ **2.5 سم**.
- **> 50 منتج → Job في الخلفية** + إشعار بالتحميل.
- **حد أقصى 500 منتج** في الشيت الواحد.
- Rate limit **10/ساعة** لتوليد PDF.
- الملف المولّد بينمسح بعد 24 ساعة.

### ⚠️ حدود mPDF اللي هتقابلك
مفيش flexbox ولا grid. استخدم `<table>` بـ `width` ثابت لكل خلية — زي ما الـ spike عمل بالظبط.

### الاختبار الحقيقي
```
اطبع الشيت على ورق → امسح 5 أكواد من مسافة 30 سم بـ 3 موبايلات
```
ده **مش اختبار آلي**. لازم يتعمل بإيدك.

---

## 11. الوحدة 6 — الموقع التسويقي

**المرحلة:** P7 · **المرجع:** `PROJECT_SPEC.md` §7، §13.6

### الصفحات
`/` · `/how-it-works` · `/pricing` · `/examples` · `/faq` · `/contact` · `/terms` · `/privacy` · `/report`

### أقسام الصفحة الرئيسية (11 قسم بالترتيب)
Hero → المشكلة (فقاعات شات) → الحل في 3 خطوات → 6 ميزات → قبل/بعد → نماذج → الخطط → آراء → FAQ → CTA → Footer

### صفحة الخطط
الأسعار وحد المنتجات **بيتقروا من جدول `plans`** — ممنوع تكتبهم في الـ Blade. لما الأدمن يغيّر السعر، الصفحة تتغيّر لوحدها.

على الموبايل: كرتين فوق بعض، **المدفوعة الأولى ومعلّمة «الأكثر طلبًا»**.

### 🚫 قواعد «التصميم مش شكله AI» — مرفوضة في الـ review

| ممنوع | المطلوب بدله |
|---|---|
| تدرّج بنفسجي/أزرق كحلي | أبيض + شرائط `--surface-3` |
| Glassmorphism / blur | حدود 1px + ظل `--sh-1` |
| 3 كروت متطابقة في النص | تنوّع تخطيط: صورة-يمين → شريط عرض كامل → شبكة |
| إيموجي في العناوين 🚀✨ | أيقونات Lucide stroke 1.5 |
| توسيط كل حاجة | محاذاة يمين (RTL) |
| «حلول مبتكرة» «نقلة نوعية» | «خلّي سعرك معلن» «عدّل من موبايلك» |
| ظل ملوّن بلون العلامة | `--sh-1/2/3` الرمادية |
| أرقام مدوّرة في الأمثلة (200) | أرقام واقعية (185، 42.50) |
| Parallax وأنيميشن ثقيل | fade-in 200ms + hover رفع 2px |
| شهادات أو أرقام وهمية | القسم يختفي لو مفيش محتوى حقيقي |

**عناوين بلونين** + **عنوان شبح باهت** خلف كل قسم (`@utility watermark` موجود في `tokens.css`) — دول التوقيع البصري من xtremedsa.

### الأداء
الرئيسية < 300KB، LCP < 2.0s. كل CTA بيروح `/register`.

---

## 12. الوحدة 7 — الاشتراكات والدفع

**المرحلة:** P8 · **المرجع:** `PROJECT_SPEC.md` §10

### آلة حالات الاشتراك
```
                  دفع ناجح
   free ─────────────────────────► active (365 يوم)
                                      │
                       تنبيهات 14/7/1 │
                                      ▼
                                  انتهى التاريخ
                                      │
                                      ▼
                     grace (7 أيام) — المميزات شغالة + بانر تحذير
                                      │
                                      ▼
                                    free
```

```php
final class SubscriptionService
{
    public function activate(Payment $payment): Subscription;
    public function roll(): int;                    // يومي: active→grace→free
    public function currentPlan(Merchant $m): Plan;
    public function hasPaidFeatures(Merchant $m): bool;
}
```

### 🚨 القاعدة اللي ما تتكسرش (D8)
**انتهاء الاشتراك لا يوقف القائمة العامة أبدًا.** العميل النهائي مش طرف في المعاملة — لازم يفضل يشوف الأسعار.

اللي بيحصل بس:
- الإعلانات ترجع تظهر
- اللون المخصص يرجع الافتراضي
- شارة «موثّق» تختفي
- المنتجات الزيادة عن الحد **تتخفي (`is_visible = false`) ولا تتحذف أبدًا** — آخر المضاف أولاً. عند التجديد ترجع تظهر بنفس الترتيب.

### تدفق الدفع
```
/dashboard/subscription → [ترقية]
   │
   ├─ Payment::create(status: pending, gateway_order_id: uuid)
   │
   ├─ $gateway->createCheckout($payment) → CheckoutSession{url, orderId}
   │
   ├─ redirect لبوابة الدفع
   │
   ├─ العميل بيدفع
   │
   ├─ /payment/callback ────────► ⚠️ عرض فقط. ما يفعّلش حاجة.
   │
   └─ POST /payment/webhook/{gw} ──► 🔒 التفعيل الحقيقي
          ├─ $gateway->verifyWebhook()   HMAC — توقيع غلط = 403 + log
          ├─ DB::transaction + lockForUpdate على الـ payment
          ├─ gateway_transaction_id UNIQUE ← الـ idempotency
          ├─ SubscriptionService::activate()
          ├─ InvoiceService::generate() → PDF عربي
          └─ إيميل تأكيد
```

### العقد — يخلّيك تبني كل حاجة قبل ما العميل يقرر البوابة
```php
interface PaymentGateway
{
    public function createCheckout(Payment $payment): CheckoutSession;
    public function verifyWebhook(Request $request): bool;
    public function parseWebhook(Request $request): WebhookPayload;
}
```
`MockDriver` بيحاكي نجاح/فشل/تكرار. `PAYMENT_GATEWAY=mock` في `.env` خلاص.
⚠️ §31 Q3 (Paymob ولا Fawry) **محجوب على العميل** — بس **P8.1، P8.2، P8.4–P8.8 كلهم شغالين** على الـ Mock. المحجوب هو P8.3 بس.

### اختبارات إلزامية
```
توقيع صحيح  → يفعّل
توقيع خاطئ  → يرفض + يسجّل
نفس الـ webhook مرتين → اشتراك واحد بس
انتهاء → grace → free
تجديد قبل الانتهاء → التمديد من ends_at مش من اليوم
انتهاء الاشتراك → القائمة العامة لسه 200
```

---

## 13. الوحدة 8 — الإعلانات ولوحة الإدارة

**المرحلة:** P9 · **المرجع:** `PROJECT_SPEC.md` §11، §12

### الإعلانات
```php
enum AdPlacement: string
{
    case ListAfterFirstCategory = 'list_after_first_category';
    case ListFooter             = 'list_footer';
    case ProductBelowContact    = 'product_below_contact';
}
```
الصفوف الثلاثة موجودة في الداتابيز خلاص (معطّلة).

| القاعدة | التفاصيل |
|---|---|
| الظهور | الخطة المجانية فقط |
| الحد | إعلانان في القائمة، **واحد** في صفحة المنتج |
| الموضع | 🚨 **ممنوع فوق السعر أو يغطيه** |
| CLS | `min-height` محجوز من `ad_slots.min_height` |
| الأولوية | الإعلان المباشر (المباع) قبل AdSense |
| ممنوع | interstitial · pop-up · أي حاجة بتزحزح المحتوى |

### لوحة الإدارة (Filament 5)
```bash
pa filament:install --panels     # بينشئ AdminPanelProvider
```
RTL: `->direction('rtl')` في الـ panel provider.

Resources: Merchant · Product · Subscription · Payment · Report · AdSlot · Plan · Setting · ActivityLog

**مؤشرات لوحة البداية:** تجار جدد/يوم · النشطين · المشتركين · الإيراد · المسح اليومي · قمع الإكمال (سجّل → أول منتج → أول كود → مشاركة) · **نسبة التجار اللي حدّثوا سعر خلال آخر 7 أيام** ← ده مؤشر صحة المنتج الحقيقي.

**Impersonate:** للأدمن فقط، بيتسجّل في activity log، وشريط واضح أثناء التقمّص.

كل إجراء إداري يتسجّل في `activity_log` (الجدول موجود).

---

## 14. الكاش وإبطاله

### الاستراتيجية: version bump (مش مسح مفاتيح)

```php
// المفتاح
"page:merchant:{$merchantId}:v{$cacheVersion}:product:{$code}"
"page:merchant:{$merchantId}:v{$cacheVersion}:menu"
"page:merchant:{$merchantId}:v{$cacheVersion}:menu:{$categorySlug}"
```

الإبطال = زيادة `cache_version` للتاجر. المفاتيح القديمة بتبقى غير قابلة للوصول وبتنتهي لوحدها بالـ TTL. **مفيش scan ولا حذف** — عملية `UPDATE` واحدة.

```php
// App\Models\Merchant — متعمولة خلاص
public function bumpCacheVersion(): void
{
    $this->newQuery()->whereKey($this->getKey())->increment('cache_version');
}
```

### متى يتم الإبطال
```php
PriceUpdated · ProductChanged · MerchantUpdated · SubscriptionChanged
            ↓
   InvalidateMerchantCache listener → $merchant->bumpCacheVersion()
```
يشمل: تعديل سعر · إضافة/حذف/إخفاء منتج · تعديل بيانات المحل · تغيير الخطة · إيقاف الحساب · ترتيب.

### TTL
| المحتوى | المدة |
|---|---|
| صفحات القوائم والمنتجات | ساعة |
| صور QR | **دائم** (`immutable`) — الكود ثابت فالصورة ثابتة |
| الموقع التسويقي | ساعة |
| `Setting::get()` | `rememberForever` + `forget` عند التعديل |

### اختبار إلزامي
```
عدّل سعر → اطلب الصفحة العامة → لازم تعرض السعر الجديد فورًا
```

---

## 15. الأمان

| المجال | الإجراء |
|---|---|
| النقل | HTTPS إجباري + HSTS (`URL::forceScheme` متعمول) |
| CSRF | على كل النماذج. الـ webhook مستثنى (بيتحقق بـ HMAC) |
| XSS | escaping افتراضي. أي `{!! !!}` لازم يتبرر في الـ review |
| الصلاحيات | Policy لكل مورد. **اختبار صريح**: تاجر ما يوصلش لبيانات تاجر تاني |
| رفع الملفات | MIME حقيقي + إعادة ترميز + حد 5MB + امتدادات محددة |
| Webhook | HMAC + idempotency + تسجيل كامل للـ raw |
| كلمات المرور | **مفيش** — OAuth فقط في v1 |
| بيانات العميل النهائي | صفر تتبّع. IP البلاغ مُجزّأ. مفيش كوكيز غير اللي AdSense بيحتاجه |
| الأسرار | في `.env` خارج git. `APP_DEBUG=false` في الإنتاج |

### Rate limits
```php
'throttle:5,1'    // تسجيل الدخول  — 5/دقيقة
'throttle:3,60'   // البلاغات      — 3/ساعة
'throttle:3,60'   // نموذج التواصل — 3/ساعة
'throttle:10,60'  // توليد PDF     — 10/ساعة
```

---

## 16. استراتيجية الاختبار

### الوضع الحالي
19 اختبار ناجح (SQLite in-memory، ثانيتين):
- `tests/Unit/CodeGeneratorTest.php` — 5
- `tests/Feature/ProductCodeTest.php` — 12

### المطلوب إضافته لكل مرحلة

| المرحلة | ملف الاختبار | ما يغطيه |
|---|---|---|
| P2 | `AuthFlowTest` | جوجل · ربط الإيميل · التحويل للأونبوردنج |
| P2 | `MerchantIsolationTest` | 🔒 تاجر A ما يوصلش لبيانات B (403) |
| P2 | `SlugTest` | الكلمات المحجوزة · التغيير مرة واحدة · 7 أيام |
| P3 | `QuickPriceEditorTest` | الحفظ · price_history · prices_updated_at |
| P3 | `ProductLimitTest` | فرض الحد · الرسالة · القراءة من settings |
| P3 | `CategoryDeletionTest` | المنتجات تروح «غير مصنّف» مش تتحذف |
| **P4** | **`ProductResolverTest`** | **الست حالات كلها** — الأهم في المشروع |
| P4 | `ReservedSlugRoutingTest` | 33 كلمة محجوزة ما توصلش للـ MenuController |
| P4 | `DefaultQrTargetTest` | الهدف · التغيير · الـ fallback لما يتحذف |
| P5 | `ProductPageTest` | السعر الصحيح · السعر الفاضي · المنتج المخفي |
| P5 | `PublicPagePerformanceTest` | عدد الاستعلامات ≤ 5 · مفيش N+1 |
| P8 | `WebhookTest` | توقيع صح/غلط · تكرار · التفعيل |
| P8 | `SubscriptionLifecycleTest` | active→grace→free · التجديد |
| P8 | **`ExpiredSubscriptionTest`** | 🚨 القائمة العامة لسه 200 |
| P10 | `CacheInvalidationTest` | تعديل سعر → الصفحة تتحدّث فورًا |

### اختبارات يدوية — مفيش بديل ليها
```
□ مسح كود من ورق مطبوع — 3 موبايلات (أندرويد · آيفون · واتساب) من 30 سم
□ مسح بعد تغيير اسم المنتج وسعره وقسمه → لسه شغال
□ تعديل 20 سعر في أقل من دقيقة على موبايل حقيقي (بالساعة)
□ Chrome throttling: Slow 3G على صفحة المنتج
□ عرض 320px — صفر تمرير أفقي
□ دورة دفع كاملة على sandbox البوابة
□ Lighthouse: Performance ≥ 90 · Accessibility ≥ 95
```

---

## 17. WBS — التقسيم والتقديرات الزمنية

### الافتراضات
- **مبرمج واحد full-stack**، 6 ساعات إنتاجية في اليوم.
- التقديرات **بتشمل** الاختبارات الآلية والـ code review الذاتي ومطابقة الـ DoD.
- **مابتشملش**: وقت انتظار موافقات العميل، ولا جولات الملاحظات (محسوبة في الجدول الزمني بالأسابيع في §28 من الـ spec).

| المرحلة | الحالة | ساعات | أيام |
|---|---|---|---|
| P0 — التهيئة | ✅ خلصت | 18 | 3 |
| P1 — نظام التصميم | ⬜ | 30 | 5 |
| P2 — الحسابات | ⬜ | 30 | 5 |
| P3 — المنتجات والأسعار | ⬜ | 42 | 7 |
| P4 — الروابط و QR ⭐ | ⬜ | 42 | 7 |
| P5 — الصفحات العامة ⭐ | ⬜ | 36 | 6 |
| P6 — استوديو الطباعة | ⬜ | 24 | 4 |
| P7 — الموقع التسويقي | ⬜ | 36 | 6 |
| P8 — الاشتراكات والدفع | ⬜ | 36 | 6 |
| P9 — الإعلانات والإدارة | ⬜ | 30 | 5 |
| P10 — SEO والأداء والأمان | ⬜ | 18 | 3 |
| P11 — الاختبار والإطلاق | ⬜ | 30 | 5 |
| **المتبقي** | | **354** | **59** |
| **الإجمالي** | | **372** | **62 يوم ≈ 9 أسابيع** |

> + أسبوعان دعم بعد الإطلاق (P12).

---

### P1 — نظام التصميم · 30 ساعة

| # | المهمة | س | يعتمد على |
|---|---|---|---|
| 1.1 | ✅ `tokens.css` — متعمول في P0 | 0 | — |
| 1.2 | إعداد Tailwind 4 + RTL + preload الخطوط | 3 | — |
| 1.3 | مكتبة المكونات: زر · كرت · حقل · chip · modal · toast · accordion · جدول · حالة فاضية · skeleton | 12 | 1.2 |
| 1.4 | 4 تخطيطات: marketing · merchant · public · print | 6 | 1.3 |
| 1.5 | صفحة `/styleguide` | 4 | 1.3 |
| 1.6 | صفحات الأخطاء 404/403/419/500/410 | 3 | 1.4 |
| 1.7 | `lang/ar/` — رسائل التحقق والنظام | 2 | — |

**القبول:** كل مكوّن شغال على 320px · RTL سليم · صفر لون خارج التوكنز.

---

### P2 — الحسابات والأونبوردنج · 30 ساعة

| # | المهمة | س | يعتمد على |
|---|---|---|---|
| 2.1 | Socialite + جوجل + `findOrCreateUser` | 6 | P1 |
| 2.2 | فيسبوك + فلاج التفعيل | 3 | 2.1 |
| 2.3 | الأدوار + Policies + Middleware | 5 | 2.1 |
| 2.4 | شاشة الأونبوردنج + `CompleteOnboarding` | 7 | 2.1، P1 |
| 2.5 | `AvailableSlug` + قاعدة الـ 7 أيام + التحذير | 4 | 2.4 |
| 2.6 | معالج أول قسم ومنتج + معاينة | 5 | 2.4 |

**القبول:** تاجر جديد يوصل للوحة في < 3 دقايق · اختبار عزل التجار بيمر.
**🔴 محجوب جزئيًا:** 2.2 محتاج حساب Meta — لكن الفلاج بيخلي الشغل يكمل.

---

### P3 — المنتجات والأسعار · 42 ساعة

| # | المهمة | س | يعتمد على |
|---|---|---|---|
| 3.1 | CRUD الأقسام + «غير مصنّف» عند الحذف | 5 | P2 |
| 3.2 | CRUD المنتجات | 7 | 3.1 |
| 3.3 | `ImageProcessor` + WebP + variants + Job | 7 | — |
| 3.4 | الترتيب بالسحب (SortableJS، يشتغل باللمس) | 5 | 3.2 |
| 3.5 | إخفاء/إظهار + أثره على الكود | 2 | 3.2 |
| 3.6 | ⚡ **`QuickPriceEditor`** + `UpdateProductPrice` + price_history | 10 | 3.2 |
| 3.7 | `ProductLimitChecker` + middleware + الرسالة | 3 | 3.2 |
| 3.8 | لوحة التاجر الرئيسية + تنبيه عدم التحديث | 3 | 3.6 |

**القبول:** 20 سعر في < دقيقة على موبايل حقيقي.

---

### P4 — الروابط وأكواد QR ⭐ · 42 ساعة

| # | المهمة | س | يعتمد على |
|---|---|---|---|
| 4.1 | ✅ `CodeGenerator` — متعمول في P0 | 0 | — |
| 4.2 | ✅ منع تغيير الكود — متعمول في P0 | 0 | — |
| 4.3 | المسارات الثلاثة + ترتيبها + الكلمات المحجوزة | 6 | P3 |
| 4.4 | **الهدف الافتراضي للكود** + معاينة + fallback | 6 | 4.3 |
| 4.5 | `QrService` + PNG/SVG/PDF + لوجو + كاش الملفات | 9 | — |
| 4.6 | واجهة `QrStudio` + معاينة حية + تحميل | 7 | 4.5، P1 |
| 4.7 | 🚨 **`ProductResolver`** + الست حالات + اختباراتها | 10 | 4.3 |
| 4.8 | المشاركة (Web Share API + fallback) | 2 | 4.3 |
| 4.9 | `ScanCounter` + `TrackScan` في terminate | 2 | 4.3 |

**القبول:** كل حالات §6.5 بتمر · مسح من ورق مطبوع شغال.
**ده أخطر جزء في المشروع — لا تستعجله.**

---

### P5 — الصفحات العامة ⭐ · 36 ساعة

| # | المهمة | س | يعتمد على |
|---|---|---|---|
| 5.1 | ⭐ **صفحة المنتج** — كل العناصر + ميزانية الأداء | 12 | P4، P1 |
| 5.2 | شارة آخر تحديث (`PriceFormatter`) | 3 | 5.1 |
| 5.3 | أزرار الاتصال + رسالة واتساب جاهزة | 3 | 5.1 |
| 5.4 | منتجات من نفس القسم (بدون N+1) | 3 | 5.1 |
| 5.5 | صفحة القائمة العامة | 8 | P4، P1 |
| 5.6 | البحث الفوري + شرائط الأقسام | 3 | 5.5 |
| 5.7 | صفحة القسم | 2 | 5.5 |
| 5.8 | البلاغات (واجهة + IP مُجزّأ + rate limit) | 2 | 5.5 |

**القبول:** FCP < 1.2s على 3G · Lighthouse ≥ 90 · CLS = 0.

---

### P6 — استوديو الطباعة · 24 ساعة

| # | المهمة | س | يعتمد على |
|---|---|---|---|
| 6.1 | شيت 24 ملصق A4 (الأهم) | 7 | P4 |
| 6.2 | تاج رف + كارت محل | 4 | 6.1 |
| 6.3 | ستاند طاولة + بوستر | 4 | 6.1 |
| 6.4 | التوليد بالجملة في Queue + الحدود | 5 | 6.1 |
| 6.5 | واجهة الاستوديو + معاينة | 4 | 6.1–6.3 |

**القبول:** طباعة فعلية + مسح 5 أكواد من الورق.
**ميزة بتفرق في السوق — متقللش من قيمتها.**

---

### P7 — الموقع التسويقي · 36 ساعة

| # | المهمة | س | يعتمد على |
|---|---|---|---|
| 7.1 | هيدر + فوتر + قائمة موبايل | 4 | P1 |
| 7.2 | Hero + موك موبايل حقيقي | 7 | 7.1 |
| 7.3 | باقي أقسام الرئيسية (9 أقسام) | 10 | 7.2 |
| 7.4 | `/how-it-works` | 4 | 7.1 |
| 7.5 | `/pricing` (يقرأ من `plans`) | 5 | 7.1 |
| 7.6 | `/examples` (قوائم حقيقية) | 2 | P5 |
| 7.7 | `/faq` + `/contact` | 2 | 7.1 |
| 7.8 | الصفحات القانونية + بانر الكوكيز | 2 | 7.1 |

**القبول:** LCP < 2.0s · موافقة العميل على التصميم.
**🟡 7.6 محتاج 3 تجار حقيقيين · 7.8 محتاج نصوص قانونية من العميل.**

---

### P8 — الاشتراكات والدفع · 36 ساعة

| # | المهمة | س | يعتمد على |
|---|---|---|---|
| 8.1 | `SubscriptionService` + الخطط + المميزات | 6 | P3 |
| 8.2 | `PaymentGateway` interface + `MockDriver` | 5 | 8.1 |
| 8.3 | 🔴 الدرايفر الحقيقي (Paymob/Fawry) | 6 | **قرار العميل** |
| 8.4 | Webhook + HMAC + idempotency | 6 | 8.2 |
| 8.5 | دورة الحياة + grace + أمر يومي | 5 | 8.1 |
| 8.6 | تجاوز الحد → إخفاء مش حذف | 2 | 8.5 |
| 8.7 | التنبيهات 14/7/1 | 2 | 8.5 |
| 8.8 | الفواتير PDF + السجل | 4 | 8.4 |

**8.1، 8.2، 8.4–8.8 (30 ساعة من أصل 36) كلهم شغالين على الـ Mock من غير ما تستنى العميل.**

---

### P9 — الإعلانات ولوحة الإدارة · 30 ساعة

| # | المهمة | س | يعتمد على |
|---|---|---|---|
| 9.1 | Filament + RTL + المؤشرات | 7 | P8 |
| 9.2 | إدارة التجار + impersonate | 5 | 9.1 |
| 9.3 | الاشتراكات والمدفوعات + التفعيل اليدوي | 4 | 9.1 |
| 9.4 | البلاغات والإجراءات | 4 | 9.1 |
| 9.5 | أماكن الإعلانات + AdSense + CLS=0 | 6 | P5 |
| 9.6 | الخطط والإعدادات والمحتوى | 2 | 9.1 |
| 9.7 | Activity log | 2 | 9.2 |

---

### P10 — SEO والأداء والأمان · 18 ساعة

| # | المهمة | س |
|---|---|---|
| 10.1 | الكاش + الإبطال الفوري + اختباره | 5 |
| 10.2 | الصور والخطوط والأصول | 3 |
| 10.3 | Meta + OG + JSON-LD + sitemap + robots | 4 |
| 10.4 | التقوية الأمنية + rate limits + مراجعة | 3 |
| 10.5 | GA4 + مؤشرات الإدارة | 1 |
| 10.6 | Lighthouse + 3G + تقرير مكتوب | 2 |

---

### P11 — الاختبار والإطلاق · 30 ساعة

| # | المهمة | س |
|---|---|---|
| 11.1 | استكمال الاختبارات الآلية للمسارات الحرجة | 8 |
| 11.2 | 📄 مسح من ورق مطبوع — 3 موبايلات | 3 |
| 11.3 | دورة الدفع الكاملة على sandbox | 3 |
| 11.4 | RTL + الاستجابة + 320px + تجربة التاجر | 5 |
| 11.5 | النشر على الإنتاج | 5 |
| 11.6 | النسخ الاحتياطي + **اختبار استرجاع فعلي** | 3 |
| 11.7 | التسليم + دليل التاجر | 3 |

---

## 18. المسار الحرج والتوازي

### السلسلة اللي بتحدد الوقت
```
P1 ──► P2 ──► P3 ──► P4 ──► P5 ──► P10 ──► P11
30     30     42     42     36     18      30     = 228 ساعة ≈ 38 يوم
```

**ممنوع تبدأ P5 قبل ما P4.7 (`ProductResolver`) يخلص ويعدّي اختباراته.** صفحة المنتج كلها مبنية على نتيجته.

### شغل ممكن يتوازى (لو فيه مبرمج تاني أو وقت فاضي)

| المهمة | متى تقدر تبدأها | ليه مستقلة |
|---|---|---|
| **P7 الموقع التسويقي** (36س) | بعد P1 | مالوش علاقة بالداتا — صفحات ثابتة |
| **P8.2 + P8.4** (11س) | بعد P3 | شغالة على الـ Mock |
| **P6 الطباعة** (24س) | بعد P4.5 | محتاجة `QrService` بس، مش الصفحات |
| **P9.1 Filament** (7س) | بعد P3 | مستقلة عن الواجهة العامة |
| **P1.7 ملفات اللغة** (2س) | من أول يوم | — |

**لو اتعملوا بالتوازي:** المدة ممكن تنزل لـ **7–7.5 أسبوع** بدل 9.

### حاجات ابدأها **اليوم** مهما كان
```
🔴 حساب مطوّر Meta         — المراجعة بتاخد أسابيع، لو استنيت هتتعطل في P2
🔴 اختيار بوابة الدفع       — بتحجب P8.3 (8 ساعات)
🟡 الدومين والاستضافة       — بتحجب P11.5 كله
🟡 حد المنتجات المجاني      — بتحجب التحقق النهائي في P3.7 و P7.5
🟡 3–5 تجار حقيقيين         — بتحجب P7.6 والتسليم
```

### الترتيب الموصى به
```
أسبوع 1     P1 نظام التصميم
أسبوع 2     P2 الحسابات              [ابدأ إجراءات Meta بالتوازي]
أسبوع 3-4   P3 المنتجات والأسعار
أسبوع 4-5   P4 الروابط و QR  ⭐       ← أخطر مرحلة، خد وقتك
أسبوع 6     P5 الصفحات العامة ⭐
أسبوع 7     P6 الطباعة + P7 الموقع التسويقي
أسبوع 8     P8 الدفع + P9 الإدارة
أسبوع 9     P10 الأداء + P11 الإطلاق
```

---

## 19. المخاطر التقنية

| الخطر | الاحتمال | الأثر | التخفيف |
|---|---|---|---|
| **كود مطبوع يبطّل يشتغل** | منخفض | 🔴 كارثي | immutable + soft delete + resolver + اختبار الست حالات. **اتعمل في P0.** |
| صفحة المنتج تتقل | متوسط | 🔴 عالي | ميزانية أداء مكتوبة · Blade مش Livewire · قياس في P5 مش في P11 |
| الـ catch-all يبلع راوت | **عالي** | 🟠 متوسط | 33 كلمة محجوزة + regex + اختبار لكل كلمة |
| مراجعة Meta بتتأخر | **عالي** | 🟠 متوسط | فلاج تفعيل + جوجل شغال · ابدأ الإجراءات أسبوع 1 |
| تأخير قرار بوابة الدفع | متوسط | 🟠 متوسط | Interface + Mock → 28 من 36 ساعة شغالة |
| العربي في PDF | ~~عالي~~ | ~~عالي~~ | ✅ **اتحل في P0** — mPDF مُثبت بصريًا |
| N+1 في الصفحات العامة | متوسط | 🟡 منخفض | `preventLazyLoading` مفعّل → استثناء وقت التطوير |
| ضغط صور بطيء | منخفض | 🟡 منخفض | Queue + حدود |
| زيارات مفاجئة لقائمة منتشرة | منخفض | 🟠 متوسط | response cache + VPS قابل للترقية |

---

## 20. أعراف الكود و Git

### تعريف «تم» — بند ببند
```
□ الوظيفة شغالة زي الوثيقة بالظبط
□ شغالة على 360px · 768px · 1280px
□ RTL سليم 100%
□ صفر لون أو مسافة خارج التوكنز
□ النصوص بالمصري ومن §24
□ حالات: تحميل · فاضي · خطأ · نجاح — كلها متعمولة
□ Validation على السيرفر
□ صلاحيات بـ Policy
□ مفيش N+1
□ اختبار آلي للمسارات الحرجة
□ مفيش dd() ولا console.log ولا TODO بدون تذكرة
□ pint عدّى
□ ClickUp متحدّث
```

### الأعراف
- **PSR-12** + Pint قبل كل commit.
- Models مفرد `Product` · جداول جمع `products` · Livewire `QuickPriceEditor`.
- `declare(strict_types=1)` في كل ملف جديد.
- السيرفسات `final` ما لم يكن فيه سبب للوراثة.
- DTOs `final readonly`.
- تعليقات الكود بالإنجليزي، ولكن **كل سيرفس له docblock فيه رقم القسم في `PROJECT_SPEC.md`**.
- الترجمات في `lang/ar/` — ممنوع نص عربي في Blade خارج الصفحات التسويقية الثابتة.

### Git
```
main       ← الإنتاج فقط
develop    ← التكامل
p4-qr-system, p5-public-pages, ...   ← فرع لكل مرحلة
```
```
feat: fix: refactor: test: docs: style:
```
PR لكل مرحلة → merge في `develop` → `main` عند الإطلاق.

### قبل ما تسأل
1. دوّر في [`PROJECT_SPEC.md`](PROJECT_SPEC.md) — 31 قسم، غالبًا الإجابة هناك.
2. شوف [سجل القرارات §30](PROJECT_SPEC.md#30-سجل-القرارات-decision-log) — 15 قرار متسجّل بأسبابه.
3. شوف [الأسئلة المفتوحة §31](PROJECT_SPEC.md#31-أسئلة-مفتوحة) — ممكن يكون سؤالك مسجّل ومستني العميل.
4. لسه مش لاقي؟ **اكتبه في §31 واسأل. ما تخمّنش.**

---

> ### «الكود بيوصّل لمكان واحد محدد. اللي التاجر حطّه في الكود هو اللي العميل بيشوفه — فورًا.»
> أي قرار تصميمي أو تقني بيزوّد خطوة بين **المسح** و**السعر** = قرار غلط.
