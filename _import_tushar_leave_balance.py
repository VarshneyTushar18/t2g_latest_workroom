#!/usr/bin/env python3
"""
Import annual leave balances from Tushar Leave Data Excel into tbltimesheets_day_off.
Overwrites total / remain / days_off for matched staff (year 2026, type_of_leave 8).

Usage:
  python3 _import_tushar_leave_balance.py /path/to/Tushar\ Leave\ Data_Sept\ 2026.xlsx [--dry-run]
"""
from __future__ import annotations

import re
import sys
from pathlib import Path

try:
    import openpyxl
except ImportError:
    print("openpyxl required: pip install openpyxl", file=sys.stderr)
    sys.exit(1)

try:
    import pymysql
except ImportError:
    pymysql = None


def load_db_config(app_root: Path) -> dict:
    cfg_file = app_root / "application" / "config" / "app-config.php"
    text = cfg_file.read_text(encoding="utf-8", errors="ignore")

    def grab(name: str) -> str:
        m = re.search(r"define\s*\(\s*['\"]" + re.escape(name) + r"['\"]\s*,\s*['\"]([^'\"]*)['\"]", text)
        if not m:
            raise RuntimeError(f"Missing {name} in app-config.php")
        return m.group(1)

    return {
        "host": grab("APP_DB_HOSTNAME"),
        "user": grab("APP_DB_USERNAME"),
        "password": grab("APP_DB_PASSWORD"),
        "database": grab("APP_DB_NAME"),
        "charset": "utf8mb4",
    }


def norm_emp(value) -> str:
    if value is None:
        return ""
    if isinstance(value, float) and value.is_integer():
        value = int(value)
    s = str(value).strip()
    if re.fullmatch(r"\d+\.0", s):
        s = s[:-2]
    return s


def to_float(value) -> float:
    if value is None or value == "":
        return 0.0
    return float(value)


def read_excel(path: Path) -> list[dict]:
    wb = openpyxl.load_workbook(path, data_only=True)
    ws = wb.active
    rows = []
    for r in range(2, ws.max_row + 1):
        emp = norm_emp(ws.cell(r, 1).value)
        if not emp:
            continue
        rows.append(
            {
                "emp_id": emp,
                "name": (ws.cell(r, 2).value or "").strip(),
                "carry_fwd": to_float(ws.cell(r, 5).value),
                "taken": to_float(ws.cell(r, 6).value),
                "earned": to_float(ws.cell(r, 7).value),
                "balance": to_float(ws.cell(r, 8).value),
            }
        )
    return rows


def build_staff_map(conn) -> dict[str, dict]:
    sql = """
        SELECT s.staffid,
               TRIM(COALESCE(s.staff_identifi, '')) AS staff_identifi,
               TRIM(COALESCE(i.empid, '')) AS empid,
               CONCAT(s.firstname, ' ', s.lastname) AS fullname
        FROM tblstaff s
        LEFT JOIN tblstaff_info i ON i.staffid = s.staffid
        WHERE s.active = 1
    """
    mapping: dict[str, dict] = {}
    with conn.cursor() as cur:
        cur.execute(sql)
        for row in cur.fetchall():
            staffid, ident, empid, fullname = row
            for code in (norm_emp(ident), norm_emp(empid)):
                if code and code not in mapping:
                    mapping[code] = {
                        "staffid": int(staffid),
                        "fullname": fullname,
                        "staff_identifi": ident,
                        "empid": empid,
                    }
    return mapping


def main() -> int:
    dry_run = "--dry-run" in sys.argv
    args = [a for a in sys.argv[1:] if a != "--dry-run"]
    if not args:
        print("Usage: python3 _import_tushar_leave_balance.py <excel.xlsx> [--dry-run]", file=sys.stderr)
        return 1

    xlsx = Path(args[0])
    if not xlsx.is_file():
        print(f"File not found: {xlsx}", file=sys.stderr)
        return 1

    app_root = Path("/var/www/html/t2gworkroom")
    if not app_root.is_dir():
        app_root = Path(__file__).resolve().parent

    excel_rows = read_excel(xlsx)
    print(f"Excel rows: {len(excel_rows)}")

    if pymysql is None:
        print("pymysql required: pip install pymysql", file=sys.stderr)
        return 1

    db = load_db_config(app_root)
    conn = pymysql.connect(**db)
    staff_map = build_staff_map(conn)
    print(f"Active staff codes in DB: {len(staff_map)}")

    year = "2026"
    leave_type = "8"
    updated = 0
    inserted = 0
    missing = []

    with conn.cursor() as cur:
        for row in excel_rows:
            staff = staff_map.get(row["emp_id"])
            if not staff:
                missing.append(row)
                continue

            total = round(row["balance"] + row["taken"], 2)
            remain = round(row["balance"], 2)
            days_off = round(row["taken"], 2)
            staffid = staff["staffid"]

            cur.execute(
                """
                SELECT id, total, remain, days_off
                FROM tbltimesheets_day_off
                WHERE staffid = %s AND year = %s AND type_of_leave = %s
                LIMIT 1
                """,
                (staffid, year, leave_type),
            )
            existing = cur.fetchone()

            if existing:
                eid, old_total, old_remain, old_days = existing
                print(
                    f"UPDATE staffid={staffid} emp={row['emp_id']} {row['name']!r}: "
                    f"total {old_total}->{total}, remain {old_remain}->{remain}, days_off {old_days}->{days_off}"
                )
                if not dry_run:
                    cur.execute(
                        """
                        UPDATE tbltimesheets_day_off
                        SET total = %s, remain = %s, days_off = %s
                        WHERE id = %s
                        """,
                        (str(total), str(remain), days_off, eid),
                    )
                updated += 1
            else:
                print(
                    f"INSERT staffid={staffid} emp={row['emp_id']} {row['name']!r}: "
                    f"total={total}, remain={remain}, days_off={days_off}"
                )
                if not dry_run:
                    cur.execute(
                        """
                        INSERT INTO tbltimesheets_day_off
                            (staffid, year, total, remain, accumulated, days_off, type_of_leave)
                        VALUES (%s, %s, %s, %s, NULL, %s, %s)
                        """,
                        (staffid, year, str(total), str(remain), days_off, leave_type),
                    )
                inserted += 1

        if not dry_run:
            conn.commit()

    conn.close()

    print(f"\nDone ({'DRY RUN' if dry_run else 'LIVE'}): updated={updated}, inserted={inserted}, missing={len(missing)}")
    if missing:
        print("Unmatched Excel Emp IDs:")
        for m in missing:
            print(f"  {m['emp_id']} {m['name']}")

    return 0 if not missing else 2


if __name__ == "__main__":
    raise SystemExit(main())
