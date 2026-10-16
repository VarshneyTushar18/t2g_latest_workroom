# Restore `new_workroom` on local MySQL 8 (port 3300)

Your current `new_workroom` catalog is broken (*doesn't exist in engine*). Re-import from:

`D:\karan\production_crm_maria.sql`

## 1. phpMyAdmin

- URL: http://localhost/phpmyadmin/
- Server port is set to **3300** in `C:\xampp\phpMyAdmin\config.inc.php`
- Login: user **root**, password **empty** (MySQL 8 on this PC)

If you still see "Access denied", confirm **MySQL80** service is running (not XAMPP MySQL on 3307).

## 2. Replace `new_workroom` (recommended)

**Option A — Command line (fastest for large dump)**

```powershell
# Stop MySQL80 service (Services.msc or):
Stop-Service MySQL80

# Remove broken data folder (run PowerShell as Administrator):
Remove-Item -Recurse -Force "C:\ProgramData\MySQL\MySQL Server 8.0\Data\new_workroom"

Start-Service MySQL80

$mysql = "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe"
& $mysql --host=127.0.0.1 --port=3300 -uroot -e "CREATE DATABASE new_workroom CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
cmd /c "`"$mysql`" --host=127.0.0.1 --port=3300 -uroot new_workroom < `"C:\Users\Admin\Desktop\29_sept_workroom_db.sql`""
& $mysql --host=127.0.0.1 --port=3300 -uroot -e "SELECT COUNT(*) FROM new_workroom.tbloptions;"
```

**Option B — phpMyAdmin**

1. Open http://localhost/phpmyadmin/
2. Select `new_workroom` → **Operations** → **Drop the database** (if drop fails, use Option A).
3. **New** → database name `new_workroom`, collation `utf8mb4_unicode_ci`.
4. **Import** → choose `D:\karan\production_crm_maria.sql` (may take 15–30 minutes).

## 3. Workroom app

`application/config/app-config.php` already uses **127.0.0.1:3300**, database **new_workroom**, root / empty password.

Restart Apache in XAMPP, then open http://localhost/myproject/
