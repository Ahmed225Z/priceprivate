# instinct-work: status of the build against SRS.md

Built on this branch only. 71 automated tests pass (SQLite in memory): `php artisan test`.

## Done
- P1 (partial): Arabic RTL error pages, lang/ar messages.
- P2: Google login, Facebook behind the `facebook_login_enabled` setting, onboarding, middleware, policies.
- P3: products, categories, quick bulk price editor, soft delete, image upload (WebP, variants), product limit.
- P4: ProductResolver (all six cases), QrService (PNG/SVG, cached), QR image route, scan counting.
- P5: public product page, store and category pages.
- P6: print studio (A4 24-label sheet, store card) with mPDF.
- P7 (basic): home, how-it-works, pricing (from plans table), faq, terms/privacy/contact placeholders.
- P8: subscriptions and payments on a Mock gateway (HMAC webhook, idempotent, grace, auto-hide/restore).
- P9 (partial): ad placement rules, reports.

## Not done
- Filament admin panel (needs PHP ext-intl; not written because it could not be run or tested).
- Real gateway (Paymob/Fawry), invoice PDF, confirmation email.
- Drag-sort, Livewire QuickPriceEditor, QR studio page with live preview, other 3 print templates, background job for big sheets.
- Full design system / Tailwind components, real marketing copy and design, SEO/sitemap, Lighthouse tuning.
- Real-device checks: scanning printed codes, 3G performance, Google login against real credentials.

## To run
```
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan test
```
Set GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET for login, PAYMENT_MOCK_SECRET for the mock gateway.
