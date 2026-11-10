# Sync `t2g_latest_workroom` to FTP

## Prerequisites
- FileZilla site **tech2globe** (or set environment variables below)
- Know the **remote folder** on the server (e.g. `/public_html`, `/t2gworkroom`)

## Option A — FileZilla site (recommended on this PC)
Site **tech2globe** is read from `%APPDATA%\FileZilla\sitemanager.xml`.

Preview:
```powershell
cd c:\xampp\htdocs\myproject\t2g_latest_workroom
.\_deploy\sync_ftp.ps1 -RemotePath /YOUR/REMOTE/PATH -WhatIf
```

Upload:
```powershell
.\_deploy\sync_ftp.ps1 -RemotePath /YOUR/REMOTE/PATH
```

## Option B — Environment variables (CI / other machines)
```powershell
$env:T2G_FTP_HOST = 'your.host'
$env:T2G_FTP_USER = 'username'
$env:T2G_FTP_PASS = 'password'
$env:T2G_FTP_PORT = '21'   # optional
.\_deploy\sync_ftp.ps1 -RemotePath /remote/path
```

## Excluded from upload
- `.git`, `node_modules`, `temp`, `application/cache`, `application/logs`
- `*.log`, `*.tmp`, `*.bak`, `*.sql`

## After upload
- Keep `application/config/app-config.php` on the server aligned with production DB/URL
- Run `php _deploy/scan_malware_targeted.php` on the server if available
- Do **not** commit FTP passwords to Git

## Full tree in FileZilla
For a one-off full mirror you can also drag `t2g_latest_workroom` in FileZilla (same site **tech2globe**) into the correct remote directory.
