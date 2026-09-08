# Tech2Globe Workroom

Customized Perfex CRM / CodeIgniter HRMS used by Tech2Globe (leave, attendance, biometric, PEDMA, etc.).

## Local installation

Full step-by-step guide:

**[INSTALL.md](INSTALL.md)**

### Fast start

1. Clone this repo  
2. Import MySQL dump into database `production_crm`  
3. Edit `application/config/app-config.php` (`APP_BASE_URL`, DB credentials, `APP_ENC_KEY`)  
4. Run:

```bash
php -S 127.0.0.1:8000 router.php
```

5. Open http://127.0.0.1:8000/admin

## Important notes

- Config is in **`application/config/app-config.php`**, not Laravel-style `.env` (`.env.example` is optional notes only).
- Do **not** commit real secrets (`app-config.php` with live tokens, `biometric-bridge/config.json`).
- Biometric office bridge: see [`biometric-bridge/README.md`](biometric-bridge/README.md).

## Live

Production: https://t2gworkroom.com
