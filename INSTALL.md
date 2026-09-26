# Tech2Globe Workroom — Local Installation Guide

This project is a customized **Perfex CRM / CodeIgniter** app (HRMS + Timesheets + Biometric).  
Follow these steps to run it on your Windows PC (XAMPP / Laragon / PHP built-in server).

---

## 1. Requirements

| Software | Version | Notes |
|----------|---------|--------|
| **PHP** | 7.4+ (8.1 / 8.2 recommended) | Must match or be close to live |
| **MySQL / MariaDB** | 5.7+ / 10.3+ | Database name usually `production_crm` |
| **Composer** | Latest | Only if you need to refresh PHP packages |
| **Git** | Latest | To clone the repo |

### Required PHP extensions

Enable these in `php.ini` (XAMPP: `php/php.ini`):

```text
mysqli
pdo_mysql
mbstring
curl
gd
zip
openssl
fileinfo
intl
```

Check:

```bash
php -v
php -m
```

---

## 2. Get the code

```bash
git clone https://github.com/VarshneyTushar18/t2g_latest_workroom.git
cd t2g_latest_workroom
```

Or use your existing local folder:

```text
d:\important files\workroom downloaded\t2gworkroom_backup_...\t2gworkroom
```

---

## 3. Database setup (required)

Workroom does **not** ship a full SQL dump in Git. You need a DB dump from live / backup.

### 3.1 Create database

In phpMyAdmin or MySQL CLI:

```sql
CREATE DATABASE production_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3.2 Import dump

```bash
# Example (adjust path/password)
mysql -u root -p production_crm < path\to\production_crm_backup.sql
```

Or use **phpMyAdmin → Import**.

### 3.3 Confirm tables exist

You should see tables like:

- `tblstaff`
- `tblstaff_info`
- `tbltimesheets_requisition_leave`
- `tbloptions`
- `tblsessions`

---

## 4. Configure the app

Perfex reads **`application/config/app-config.php`** (not Laravel `.env`).

### Option A — edit existing file

Open:

```text
application/config/app-config.php
```

Set at least:

```php
define('APP_BASE_URL', 'http://127.0.0.1:8000/');   // trailing slash required

define('APP_DB_HOSTNAME', 'localhost');
define('APP_DB_USERNAME', 'root');
define('APP_DB_PASSWORD', '');                      // your MySQL password
define('APP_DB_NAME', 'production_crm');

define('APP_ENC_KEY', 'a1622d603fafb2edc3cd8c9f073fe277'); // keep same as live if restoring same DB
```

### Option B — create from sample

```bash
copy application\config\app-config-sample.php application\config\app-config.php
```

Then fill `APP_BASE_URL`, DB credentials, and `APP_ENC_KEY`.

> **Important:** If you import a live database, keep the same `APP_ENC_KEY` as live, or passwords/sessions may break.

### Optional `.env`

`.env.example` is for local notes / tooling only. The running PHP app uses `app-config.php`.

```bash
copy .env.example .env
```

---

## 5. Writable folders

Make sure these folders exist and are writable by PHP:

```text
uploads/
temp/
application/cache/
application/logs/
media/
modules/*/uploads/   (as needed)
```

Windows (PowerShell, from project root):

```powershell
New-Item -ItemType Directory -Force -Path uploads,temp,application\cache,application\logs,media | Out-Null
```

---

## 6. Composer (required on fresh clone)

`vendor/` folders are **not** committed to Git (see `.gitignore`).  
If you cloned from GitHub, you **must** install PHP packages with Composer.

### Install Composer (Windows)

1. Download: https://getcomposer.org/download/  
2. Or with PHP already installed:

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```

Confirm:

```bash
composer -V
```

### Install project dependencies

There are **two** Composer projects in this codebase:

#### A) Main Perfex packages (required)

```bash
cd application
composer install
```

This creates / refreshes `application/vendor/` (PHPMailer, Guzzle, Stripe, elFinder, TCPDF, etc.).  
CodeIgniter loads it via `$config['composer_autoload'] = true` → `application/vendor/autoload.php`.

#### B) Root PhpSpreadsheet (optional but recommended)

```bash
cd ..
composer install
```

This creates / refreshes root `vendor/` for `phpoffice/phpspreadsheet` (Excel import/export used by timesheets / biometric).

### Quick check

```bash
# From project root
dir application\vendor\autoload.php
dir vendor\autoload.php
```

Both files should exist.

### Notes

| Situation | What to do |
|-----------|------------|
| Fresh `git clone` | Run both `composer install` commands above |
| You already have a full backup folder with `vendor` | Skip Composer unless something is broken |
| Missing class / autoload errors | Re-run `composer install` in `application/` then root |
| Do **not** use `composer update` casually | Prefer `composer install` (uses lock files; safer) |

---

## 7. Start the local server

### Recommended — PHP built-in server + router

From the **project root** (folder that contains `index.php` and `router.php`):

```bash
php -S 127.0.0.1:8000 router.php
```

Open:

```text
http://127.0.0.1:8000/admin
```

`router.php` emulates Apache rewrite so CodeIgniter URLs work.

### Alternative — XAMPP / Laragon / Apache

1. Point the virtual host / document root to the project root (where `index.php` lives).
2. Ensure `mod_rewrite` is enabled (`.htaccess` is already present).
3. Set `APP_BASE_URL` to that vhost URL, e.g. `http://workroom.local/`.

---

## 8. First login

Use an existing admin from the imported DB (same as live), or reset password in DB if needed.

Typical admin URL:

```text
http://127.0.0.1:8000/admin
```

After login you should see the Workroom sidebar (HRMS, Timesheets, Biometric Attendance, etc.).

---

## 9. Local checklist after install

| Check | How |
|-------|-----|
| Login works | `/admin` |
| Leave balance opens | Staff / Timesheets leave pages |
| Biometric page | Admin → Biometric Attendance |
| DB connected | No “Unable to connect” / blank white error |

### Optional local features

- **Leave policy** needs `tblstaff_info.employment_category` (`fte` / `intern` / `wfh`). Migration file: `application/migrations/309_version_309.php`. If migrations do not auto-run, add the column manually:

```sql
ALTER TABLE tblstaff_info
  ADD COLUMN employment_category VARCHAR(20) NOT NULL DEFAULT 'fte' AFTER doj;
```

- **Biometric bridge** (office PC only): see `biometric-bridge/README.md`. Copy `config.example.json` → `config.json` and fill tokens. Do **not** commit real keys.

---

## 10. Common problems

### White page / 500

- Check `application/logs/` for the latest log file.
- Confirm PHP version ≥ 7.4 and extensions listed above.

### Database connection error

- Wrong password / DB name in `app-config.php`.
- MySQL service not running (start XAMPP MySQL).

### CSS / links broken / 404 on admin pages

- `APP_BASE_URL` must match the URL you open (including port and trailing slash).
- Use `php -S 127.0.0.1:8000 router.php` (not plain `php -S` without router).

### Session / login loops

- Sessions table missing → import full DB dump.
- Or temporarily use file sessions in `app-config.php`:

```php
define('SESS_DRIVER', 'files');
define('SESS_SAVE_PATH', NULL);
```

### CSRF / 419 Page Expired

- Clear browser cookies for `127.0.0.1`.
- Confirm `APP_BASE_URL` protocol/host matches the browser URL.

### “Filename too long” on Windows Git

- Enable long paths: `git config --system core.longpaths true`

---

## 11. Quick start (summary)

```bash
# 1) Clone
git clone https://github.com/VarshneyTushar18/t2g_latest_workroom.git
cd t2g_latest_workroom

# 2) Composer (required — vendor is not in Git)
cd application
composer install
cd ..
composer install

# 3) Import MySQL dump into database: production_crm

# 4) Edit application/config/app-config.php
#    APP_BASE_URL, APP_DB_*, APP_ENC_KEY

# 5) Start server
php -S 127.0.0.1:8000 router.php

# 6) Open
# http://127.0.0.1:8000/admin
```

---

## 12. Project map (useful paths)

| Path | Purpose |
|------|---------|
| `application/config/app-config.php` | Base URL + DB |
| `application/controllers/` | Core controllers |
| `modules/timesheets/` | Leave, attendance, shifts |
| `application/controllers/admin/Biometric.php` | Biometric Attendance UI |
| `biometric-bridge/` | Office PC Biometric → Workroom sync |
| `router.php` | Local PHP server rewrite helper |

---

## Need a DB dump?

Ask the team for the latest `production_crm` SQL backup (or export from live phpMyAdmin / `mysqldump`). Without the database, the PHP code alone cannot run Workroom.
