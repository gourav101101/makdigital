# MAK Digital Arena

Complete local Laravel 13 website with an original animated design, interactive Three.js globe, responsive public pages and authenticated content administration.

## Open locally

Run `php artisan serve` from `D:\makdigital`, then visit http://127.0.0.1:8000. Admin: http://127.0.0.1:8000/admin/login.

Local admin credentials are saved privately in `storage/app/private/admin-access.json`. Do not publish that file. Create another administrator interactively with `php artisan admin:create`; production has no default account or password.

## Features

- Homepage, projects, services, insights, about, careers, contact and legal pages.
- Desktop interactive 3D; mobile opt-in 3D; static fallback, reduced-motion support, keyboard navigation.
- Admin editing, draft/published states, validated WebP uploads and contact settings.
- Saved enquiries, duplicate protection, rate limiting, status/notes and optional queued notifications.
- Server-rendered metadata, sitemap, social preview and mapped legacy redirects.

## Fresh installation

Requires PHP 8.3+ with SQLite and GD, Composer and Node/npm.

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item database/database.sqlite -ItemType File
php artisan migrate --seed
npm.cmd ci
npm.cmd run build
php artisan admin:create
php artisan serve
```

The seed preserves existing edited entries. SQLite is configured locally; MySQL can be configured through the usual Laravel database environment variables.

## Saved source website

Archive: `storage/app/legacy/mdarena/`. Read `content.json`, `assets.json` and `report.json` for saved text, media and unavailable resources. Refresh with `php scripts/archive-legacy.php`, then `php artisan legacy:import`. Importing preserves source evidence; it does not automatically publish unreviewed material. The archive and SQLite database are ignored by Git; back them up separately, along with uploaded images.

## Verification

```powershell
php artisan test --compact
npm.cmd run build
node scripts/review-homepage.mjs
```

The browser script uses installed Windows Chrome and the running local server. Screenshots, accessibility checks and Lighthouse output are saved under `storage/app/review/`. Browser admin checks require the private local credentials file.

## Production setup

Point the web server document root at `public/`, enable HTTPS, set `APP_ENV=production`, `APP_DEBUG=false`, the public `APP_URL`, secure session cookies and production database credentials. Keep the application key private; ensure storage/bootstrap cache are writable. Run migrations, build assets and cache configuration/routes/views during deployment. The local environment deliberately blocks search indexing; production robots and metadata permit indexing.

Enquiries already persist without mail. To enable email, configure a real mail transport, `ENQUIRY_NOTIFICATIONS=true`, `ENQUIRY_NOTIFICATION_RECIPIENT` and a supervised `php artisan queue:work --tries=3` worker. Log mail is labelled logged, never delivered. Failed notification attempts are visible and retryable in admin. Real delivery has not been tested without SMTP credentials.

Review the imported legal wording, contact details, client permissions and business claims before public launch. Complete real Safari/iOS/Android checks and production-host performance measurement. Deployment itself is not performed.

See [website plan](docs/website-plan.md), [project structure](docs/project-structure.md) and [content review](docs/content-review.md). The existing `D:\Xampp\htdocs\indorelitfest` was a structural reference and was not modified.
