# Deploy another server to match production (t2gworkroom.com)

After `git pull`, complete these steps so behavior matches live.

## Requirements
- **PHP 8.1+** (live uses **Apache mod_php 8.3**). PHP 7.4 will break Google Snapshot libraries.
- MySQL/MariaDB, Apache with `mod_rewrite`, Composer (optional, for `google-client` repairs)

## 1. Code
```bash
git checkout phase-a-leave   # or main after merge
git pull origin phase-a-leave
```

## 2. Configuration
- Copy `application/config/app-config.php` from live (DB credentials, `APP_BASE_URL`, encryption key).
- Do **not** commit secrets to GitHub.

## 3. Database
- Import production dump or run migrations:
```bash
php index.php cron upgrade_db
```
- Target version: **311** (`application/config/migration.php`).

## 4. Google Snapshot (Drive)
```bash
cd google-client && composer install --no-dev
```
If `GuzzleHttp\Psr7\Request` missing on PHP 8.3, update auth/psr7 per live (Oct 2026).

## 5. Carbon (PHP 8.3 admin dashboard)
If `/admin` throws Carbon `setLastErrors` errors, patch:
`application/vendor/nesbot/carbon/src/Carbon/Traits/Creator.php` line ~98:
```php
static::setLastErrors(parent::getLastErrors() ?: []);
```

## 6. Writable dirs
`application/logs`, `uploads`, `temp`, `application/cache` — www-data writable.

## 7. Apache
- Enable **php8.3** (or matching version).
- Document root = project root; `AllowOverride All` for `.htaccess`.
- `ErrorDocument 500/503` → `/maintenance.html` (included in repo).

## 8. Post-deploy checks
- Login: `/admin/authentication`
- Dashboard: `/admin`
- WFH check-in (staff on Manage WFH for today)
- Snapshot: `/admin/Emt/getEmployeeId`
- `php _deploy/scan_malware_targeted.php` → CLEAN

## Not in Git (copy manually from live if needed)
- `google-client/t2g-monitoring-*.json` (service account)
- Full `uploads/` media
- `Snapshot.zip` (large; optional in repo)
