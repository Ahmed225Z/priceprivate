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

## Round 8
Added: invoice PDF (after paid webhook) + subscription page, product drag-sort (SortableJS from CDN, JS not run in a browser here), QR studio (preview, default target), print templates shelf_talker/table_stand/poster, robots.txt, sitemap.xml, noindex on hidden/state pages, security headers. 76 tests pass.
Still not done: Filament admin (needs PHP intl), real Paymob/Fawry, confirmation email, phone QR scan, real Google login, MySQL, 3G test.

## MySQL run
Tests also run against MariaDB 10.11.9 (userspace, local port 3307): migrate:fresh --seed OK, 76/76 pass on both SQLite and MariaDB. Found and fixed one MySQL-only bug (products_count decrement below 0 on unsigned column). To repeat: set DB_CONNECTION=mysql DB_HOST DB_PORT DB_DATABASE DB_USERNAME env vars and run php artisan test. Not real MySQL 8, MariaDB is a close stand-in.

## Round 9: Filament admin (/admin)
Resources: Merchants (suspend/reactivate, edit, impersonate), Reports (reviewed/dismissed/hide content, moderators allowed), Payments (manual activation), Subscriptions, AdSlots, Plans, Settings, ActivityLog. Dashboard widgets: new merchants today, active, paying, revenue, scans today, % who updated a price in 7 days, completion funnel.
Sign-in is the normal /login flow (EnsureStaff middleware gates /admin); no separate Filament login. Panel RTL comes from APP_LOCALE=ar (Filament 5.12 has no ->direction()).
Impersonation: admin only, logged with the real admin as causer, red bar in the public layout with a leave button.
Deploy: run `php artisan filament:assets` (published assets are not committed). Needs ext-intl.
Tested on MariaDB with an intl PHP build: 81 pass. On a PHP without ext-intl the 4 admin-panel tests fail (Filament needs intl), the other 77 pass.
Known gaps: funnel step 3 ("first code") counts share_targets, which the app does not write yet. Impersonate start/leave are POST-only (CSRF, auth, admin only, logged).
