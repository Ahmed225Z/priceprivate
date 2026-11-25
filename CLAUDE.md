# CLAUDE.md — قواعد العمل على مشروع «السعر مش ع الخاص»

## اقرأ أولًا
**[PROJECT_SPEC.md](PROJECT_SPEC.md)** هو المرجع الوحيد للمشروع. اقرأه بالكامل قبل أي تاسك.
أي تعارض بين الكود والوثيقة → **الوثيقة هي الصح**. لو الكود أصح، عدّل الوثيقة الأول ثم الكود.

**[SRS.md](SRS.md)** — الوثيقة التقنية: المعمارية، توقيعات السيرفسات، تدفقات التنفيذ، وتقسيم المهام بالساعات. اقرأها قبل ما تبدأ أي مرحلة جديدة.

```
PROJECT_SPEC.md  →  إيه اللي هنعمله (المرجع النهائي عند أي خلاف)
SRS.md           →  هنعمله إزاي، وهياخد قد إيه
CLAUDE.md        →  القواعد والخطوط الحمراء وأوامر البيئة (الملف ده)
```

**جذر المشروع:** `C:\laraenv\www\priceprivate`
**التاسكات:** ClickUp — فولدر «السعر مش ع الخاص» (12 لست P0→P11، 85 تاسك)
https://app.clickup.com/9012450566/v/f/1500460000003615

## المبدأ الحاكم
> **«الكود بيوصّل لمكان واحد محدد. اللي التاجر حطّه في الكود هو اللي العميل بيشوفه — فورًا.»**

أي قرار بيزوّد خطوة بين **مسح الكود** و**ظهور السعر** = قرار مرفوض.

## خطوات أي تاسك
1. حدّد المرحلة (P0–P12) والقسم الحاكم في `PROJECT_SPEC.md`.
2. نفّذ **نطاق التاسك فقط** — ممنوع إضافة ميزات مش مكتوبة في الوثيقة.
3. طابق [تعريف «تم» (القسم 29)](PROJECT_SPEC.md#29-تعريف-تم-dod-وقواعد-الكود) بند ببند.
4. حدّث حالة التاسك في ClickUp.
5. أي قرار تقني جديد → سطر في [سجل القرارات (القسم 30)](PROJECT_SPEC.md#30-سجل-القرارات-decision-log).
6. أي غموض → [أسئلة مفتوحة (القسم 31)](PROJECT_SPEC.md#31-أسئلة-مفتوحة) + اسأل. **ما تخمّنش.**

## خطوط حمراء
- ❌ أي لون خارج `resources/css/tokens.css` (القسم 13).
- ❌ تغيير `public_code` بعد إنشائه.
- ❌ إرجاع 404 فارغة لأي كود QR — لازم fallback ذكي (القسم 6.5).
- ❌ إعلان فوق السعر أو يغطيه.
- ❌ إيقاف القائمة العامة بسبب انتهاء الاشتراك.
- ❌ تسجيل دخول للعميل النهائي.
- ❌ منطق أعمال في Blade أو Controller (Service Layer إجباري).
- ❌ نص عربي مكتوب داخل Blade خارج الصفحات التسويقية الثابتة (استخدم `lang/ar/`).
- ❌ `float` للمبالغ — `DECIMAL(12,2)` دائمًا.
- ❌ تصميم «شكله AI» — راجع القسم 13.6.

## الستاك
Laravel 12.73 · **PHP 8.4.20** · MySQL 9.7 · **Livewire 4.4** · Alpine · **Tailwind 4 (CSS-first `@theme`)** · **Filament 5.12** · Socialite 5.33 · endroid/qr-code 6.1 · **mPDF 8.3**

> ⚠️ Tailwind 4 مفيهاش `tailwind.config.js` — التوكنز كلها في `resources/css/tokens.css` داخل `@theme`.

## 🚨 البيئة — اقرأ ده قبل أي أمر
الـ `php` اللي في الـ PATH هو **XAMPP 8.2.12** وهو **غلط** للمشروع ده — الحزم مش بتتثبت عليه.
استخدم دايمًا PHP بتاع laraenv:

```
PHP      C:\laraenv\php\current\php.exe        (8.4.20)
COMPOSER C:\laraenv\bin\composer.phar
MySQL    C:\laraenv\mysql   (port 3306، لازم laraenv يكون شغال)
الموقع   http://priceprivate.test  (nginx من laraenv)
```

أي أمر artisan أو composer:
```bash
/c/laraenv/php/current/php.exe artisan <command>
/c/laraenv/php/current/php.exe /c/laraenv/bin/composer.phar <command>
```

## الهوية (من xtremedsa.com)
تركواز `#029A8E` · كحلي `#243858` · أصفر `#FFD54F` · خط `Tajawal` · **RTL · موبايل أولًا**

## الأوامر
```bash
php artisan test            # الاختبارات
./vendor/bin/pint           # تنسيق الكود (قبل كل commit)
npm run dev / npm run build # الواجهات
```

## Commits
`feat:` `fix:` `refactor:` `test:` `docs:` `style:` — بالإنجليزي، فرع لكل مرحلة (`p4-qr-system`).
