# MilesWeb nginx restore (103.174.102.70)

## What broke

Workroom staging was patched into **`/etc/nginx/nginx.conf`** as **`listen 80 default_server`** with **`root /var/www/workroom`**.  
Any hostname pointing at this IP **without its own `conf.d` vhost** (e.g. **services4amazon.com**) got Workroom login.

Your **old backup** is correct:

- `include /etc/nginx/conf.d/*.conf;` — per-site configs
- **`default_server`** → **`root /usr/share/nginx/html`** (harmless placeholder)
- **No Workroom** in `nginx.conf`

## Fix (on server as root)

### 1) Restore main config default block

Either restore from backup file, or edit `/etc/nginx/nginx.conf` so the only `default_server` block matches your backup ( **`server_name _;`**, **`root /usr/share/nginx/html;`** ) — **not** `/var/www/workroom`.

Quick check:

```bash
grep -n 'default_server\|/var/www/workroom' /etc/nginx/nginx.conf
```

There should be **no** `/var/www/workroom` in `nginx.conf`.

### 2) Install Workroom staging as its own vhost (not default)

```bash
cp /path/to/workroom-staging.conf /etc/nginx/conf.d/workroom-staging.conf
```

### 3) Restore services4amazon vhost

Find docroot first:

```bash
ls -la /home/ | grep -i amazon
grep -ri services4amazon /etc/nginx/conf.d /usr/local/lsws/conf/vhosts 2>/dev/null
```

Then:

```bash
cp /path/to/services4amazon.com.conf /etc/nginx/conf.d/services4amazon.com.conf
# Edit root= line if docroot is not /home/services4amazon.com/public_html
```

### 4) Test and reload

```bash
nginx -t && systemctl reload nginx
curl -sI -H 'Host: services4amazon.com' http://127.0.0.1/ | head -5
curl -sI -H 'Host: 103.174.102.70' http://127.0.0.1/admin/ | head -5
```

Expected:

- **services4amazon.com** → your site (not `Location: .../admin/authentication`)
- **103.174.102.70** → Workroom staging still OK

### 5) SSL (if needed)

If HTTPS broke, re-issue or restore cert:

```bash
certbot certificates | grep -i amazon
certbot --nginx -d services4amazon.com -d www.services4amazon.com
```
