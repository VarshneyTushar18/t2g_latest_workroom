#!/usr/bin/env python3
"""Build local secrets inventory Excel from repo files (never commit output)."""
from __future__ import annotations

import json
import re
from datetime import datetime, timezone
from pathlib import Path

from openpyxl import Workbook
from openpyxl.styles import Font, PatternFill
from openpyxl.utils import get_column_letter

ROOT = Path(__file__).resolve().parents[1]
OUT_DOWNLOADS = Path(r"C:\Users\Morning\Downloads") / "t2gworkroom-secrets-inventory.xlsx"
OUT_DESKTOP = Path(r"C:\Users\Morning\Desktop") / "t2gworkroom-secrets-inventory.xlsx"
OUT_DEPLOY = Path(__file__).resolve().parent / "t2gworkroom-secrets-inventory.xlsx"

HEADER_FILL = PatternFill("solid", fgColor="1F4E79")
HEADER_FONT = Font(color="FFFFFF", bold=True)
WARN_FILL = PatternFill("solid", fgColor="FFF2CC")


def add_sheet(wb: Workbook, title: str, rows: list[list], widths: dict[int, int] | None = None):
    ws = wb.create_sheet(title)
    for r_idx, row in enumerate(rows, start=1):
        for c_idx, val in enumerate(row, start=1):
            cell = ws.cell(row=r_idx, column=c_idx, value=val)
            if r_idx == 1:
                cell.fill = HEADER_FILL
                cell.font = HEADER_FONT
    for col, w in (widths or {}).items():
        ws.column_dimensions[get_column_letter(col)].width = w
    ws.freeze_panes = "A2"
    return ws


def parse_php_defines(path: Path) -> dict[str, str]:
    if not path.is_file():
        return {}
    text = path.read_text(encoding="utf-8", errors="replace")
    out: dict[str, str] = {}
    for m in re.finditer(
        r"define\s*\(\s*['\"]([A-Z0-9_]+)['\"]\s*,\s*(.+?)\s*\)\s*;",
        text,
        re.DOTALL,
    ):
        name, raw = m.group(1), m.group(2).strip()
        if raw.startswith(("'", '"')):
            q = raw[0]
            end = raw.rfind(q)
            out[name] = raw[1:end] if end > 0 else raw
        elif raw.startswith("$"):
            out[name] = f"(expression: {raw[:80]}...)" if len(raw) > 80 else f"(expression: {raw})"
        else:
            out[name] = raw
    return out


def parse_dotenv(path: Path) -> list[tuple[str, str, str]]:
    rows: list[tuple[str, str, str]] = []
    if not path.is_file():
        return rows
    for line in path.read_text(encoding="utf-8", errors="replace").splitlines():
        s = line.strip()
        if not s or s.startswith("#"):
            continue
        if "=" not in s:
            continue
        k, _, v = s.partition("=")
        v = v.strip().strip('"').strip("'")
        rows.append((k.strip(), v, str(path.relative_to(ROOT))))
    return rows


def main() -> None:
    wb = Workbook()
    wb.remove(wb.active)

    generated = datetime.now(timezone.utc).strftime("%Y-%m-%d %H:%M UTC")
    readme = [
        ["Item", "Detail"],
        ["Generated", generated],
        ["Source", "Local repo + files on this PC only"],
        [
            "Not included (fetch manually)",
            "Live AWS app-config extras (APP_CRON_KEY, BIOMETRIC_*), tbloptions SMTP password (encrypted), staging server if different",
        ],
        [
            "Security",
            "Do not commit this xlsx or upload to cloud. Rotate anything ever pasted in chat.",
        ],
        ["Live SSH", "3.221.241.235 timed out at build time — re-run script when VPN/IP allowed"],
        ["Staging", "103.174.102.70 — merge staging app-config rows manually if needed"],
    ]
    add_sheet(wb, "README", readme, {1: 28, 2: 90})

    # App config
    app_cfg = ROOT / "application" / "config" / "app-config.php"
    defines = parse_php_defines(app_cfg)
    app_rows = [["Constant", "Value", "Environment", "Source file"]]
    for k in sorted(defines):
        env = "local+prod URL" if k == "APP_BASE_URL" else ("local empty pwd" if "PASSWORD" in k else "repo copy")
        app_rows.append([k, defines[k], env, str(app_cfg.relative_to(ROOT))])
    add_sheet(wb, "app-config.php", app_rows, {1: 28, 2: 55, 3: 18, 4: 40})

    # .env
    env_rows = [["Key", "Value", "Source file"]]
    for k, v, src in parse_dotenv(ROOT / ".env"):
        env_rows.append([k, v, src])
    add_sheet(wb, "dotenv", env_rows, {1: 22, 2: 55, 3: 30})

    # Biometric bridge
    bio_path = ROOT / "biometric-bridge" / "config.json"
    bio_rows = [["Key", "Value", "Source file"]]
    if bio_path.is_file():
        bio = json.loads(bio_path.read_text(encoding="utf-8"))
        for k in sorted(bio):
            bio_rows.append([k, bio[k], str(bio_path.relative_to(ROOT))])
    add_sheet(wb, "biometric-bridge", bio_rows, {1: 28, 2: 70, 3: 35})

    # Google service accounts
    g_rows = [["File", "Field", "Value"]]
    g_dir = ROOT / "google-client"
    for fp in sorted(g_dir.glob("*.json")):
        if fp.name == "composer.json":
            continue
        data = json.loads(fp.read_text(encoding="utf-8"))
        for field in (
            "type",
            "project_id",
            "private_key_id",
            "client_email",
            "client_id",
            "private_key",
        ):
            if field in data:
                g_rows.append([fp.name, field, data[field]])
    add_sheet(wb, "google-service-accounts", g_rows, {1: 42, 2: 18, 3: 100})

    # Server access (no PEM contents)
    access = [
        ["Role", "Host", "User", "Auth", "App path", "Notes"],
        [
            "Live AWS",
            "3.221.241.235 / t2gworkroom.com",
            "ubuntu",
            "PEM: C:\\Users\\Morning\\Downloads\\Workroom_T2g_2026.pem",
            "/var/www/html/t2gworkroom",
            "Security group must allow your IP",
        ],
        [
            "Staging MilesWeb",
            "103.174.102.70",
            "root",
            "Password (CyberPanel — your records)",
            "/var/www/workroom",
            "APP_BASE_URL http://103.174.102.70/",
        ],
        [
            "Biometric device API",
            "127.0.0.1:82 (office PC)",
            "—",
            "biometric_api_key in bridge config",
            "GetDeviceLogs",
            "Runs on LAN with bridge service",
        ],
    ]
    add_sheet(wb, "server-access", access, {1: 16, 2: 32, 3: 10, 4: 45, 5: 28, 6: 40})

    # Mail / SES (from .env + where live password lives)
    mail = [
        ["Setting", "Value in repo", "Live source"],
        ["SMTP host", "email-smtp.us-east-1.amazonaws.com", "tbloptions smtp_host + Setup → Email"],
        ["SMTP user (IAM)", "AKIAXYKJTZ5SEI3NFYSN", "tbloptions smtp_username"],
        ["SMTP password", "(empty in .env)", "tbloptions smtp_password (encrypted in DB)"],
        ["From", "noreply@t2gworkroom.com", "tbloptions smtp_email / company name"],
        ["Policy file", "application/config/app_email_policy.php", "Blocks non-production hosts"],
    ]
    ws_mail = add_sheet(wb, "email-SES", mail, {1: 18, 2: 45, 3: 45})
    for r in range(2, 2 + len(mail) - 1):
        if mail[r - 1][1] == "(empty in .env)":
            ws_mail.cell(row=r, column=2).fill = WARN_FILL

    # Missing on live — user fills
    missing = [
        ["Name", "Where to get it", "Value (fill in)"],
        ["APP_CRON_KEY", "Live: grep APP_CRON_KEY application/config/app-config.php", ""],
        [
            "BIOMETRIC_SYNC_TOKEN",
            "Live app-config.php; must match biometric-bridge workroom_token",
            defines.get("BIOMETRIC_SYNC_TOKEN")
            or (
                json.loads(bio_path.read_text(encoding="utf-8")).get("workroom_token", "")
                if bio_path.is_file()
                else ""
            ),
        ],
        ["BIOMETRIC_SYNC_ALLOWED_IPS", "Live app-config.php if set", ""],
        ["smtp_password (plain)", "AWS SES console → SMTP password; or decrypt from tbloptions on live", ""],
        ["Live APP_DB_PASSWORD", "Live app-config.php (may differ from local Testpassword@123)", ""],
        ["Staging DB password", "CyberPanel / MySQL user for production_crm on 103.174.102.70", ""],
    ]
    add_sheet(wb, "fill-from-live", missing, {1: 28, 2: 55, 3: 40})

    saved: list[Path] = []
    for out in (OUT_DEPLOY, OUT_DESKTOP, OUT_DOWNLOADS):
        out.parent.mkdir(parents=True, exist_ok=True)
        wb.save(out)
        saved.append(out)
    for p in saved:
        print(p)


if __name__ == "__main__":
    main()
