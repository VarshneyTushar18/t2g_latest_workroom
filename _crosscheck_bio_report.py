#!/usr/bin/env python3
"""Cross-check DailyAttendance xls vs live tblbiometric_report."""
import json
import re
import subprocess
import sys
from collections import defaultdict
from pathlib import Path

import pandas as pd

XLS = Path(r"C:\Users\Morning\Downloads\DailyAttendance_DetailedReportForDepartment (9).xls")
PEM = Path(r"C:\Users\Morning\Downloads\Workroom_T2g_2026.pem")
REMOTE_PHP = "/tmp/_tmp_export_bio_sep.php"
LOCAL_PHP = Path(__file__).resolve().parent / "_tmp_export_bio_sep.php"


def norm_code(code):
    s = str(code or "").strip()
    if s.isdigit():
        return str(int(s))  # 2162 not 02162
    return s


def norm_time(t):
    t = str(t or "").strip()
    if not t or t in ("00:00", "00:00:00", "nan", "NaN"):
        return ""
    t = re.sub(r"\(SE\)$", "", t, flags=re.I).strip()
    m = re.match(r"(\d{1,2}):(\d{2})(?::(\d{2}))?", t)
    if not m:
        return t.lower()
    h, mi, se = int(m.group(1)), m.group(2), (m.group(3) or "00")
    return f"{h:02d}:{mi}:{se}"


def parse_punches(raw):
    raw = str(raw or "").strip()
    if not raw or raw.lower() == "nan":
        return []
    out = []
    for m in re.finditer(
        r"(\d{1,2}:\d{2}(?::\d{2})?)\s*(?:\()?[\s\-]*(in|out)(?:\))?|"
        r"\b(in|out)\b\s*[:\-]?\s*(\d{1,2}:\d{2}(?::\d{2})?)",
        raw,
        re.I,
    ):
        if m.group(1):
            t, d = m.group(1), m.group(2)
        else:
            d, t = m.group(3), m.group(4)
        out.append((norm_time(t), d.upper()))
    return out


def punch_key_list(punches, minute_only=False):
    out = []
    for t, d in punches:
        if minute_only and t:
            parts = t.split(":")
            t = f"{int(parts[0]):02d}:{parts[1]}" if len(parts) >= 2 else t
        out.append(f"{t} {d}")
    return out


def load_excel():
    df = pd.read_excel(XLS, sheet_name=0, header=1)
    df = df[df["AttendanceDate"].notna()].copy()
    df["code"] = df["Employee Code"].map(norm_code)
    df["date"] = df["AttendanceDate"].astype(str).str.strip()
    df["punches"] = df["Punch Records"].map(parse_punches)
    df["punch_n"] = df["punches"].map(len)
    df["a_in"] = df["A. InTime"].map(norm_time)
    df["a_out"] = df["A.OutTime"].map(norm_time)
    return df


def load_db():
    subprocess.run(
        [
            "scp",
            "-i",
            str(PEM),
            str(LOCAL_PHP),
            f"ubuntu@3.221.241.235:{REMOTE_PHP}",
        ],
        check=True,
    )
    res = subprocess.run(
        [
            "ssh",
            "-i",
            str(PEM),
            "ubuntu@3.221.241.235",
            f"php {REMOTE_PHP}",
        ],
        capture_output=True,
        text=True,
        check=True,
    )
    rows = json.loads(res.stdout)
    by_key = {}
    for r in rows:
        code = norm_code(r.get("employee_code"))
        date = str(r.get("attendance_date", "")).strip()
        key = (code, date)
        punches = parse_punches(r.get("punch_records"))
        item = {
            "id": r.get("id"),
            "name": r.get("employee_name"),
            "staffid": r.get("staffid"),
            "active": r.get("active"),
            "a_in": norm_time(r.get("a_in_time")),
            "a_out": norm_time(r.get("a_out_time")),
            "punches": punches,
            "punch_n": len(punches),
            "raw": str(r.get("punch_records") or ""),
        }
        if key not in by_key or item["punch_n"] > by_key[key]["punch_n"]:
            by_key[key] = item
    return by_key


def main():
    print("Loading Excel original report...")
    xl = load_excel()
    xl_with = xl[xl["punch_n"] > 0].copy()
    print(f"Excel rows: {len(xl)} | with punches: {len(xl_with)}")

    print("Loading live DB (Sep 2026)...")
    db = load_db()
    print(f"DB unique employee/day keys: {len(db)}")

    # Focus on mapped active staff (4-digit codes typical employees)
    staff_codes = {k[0] for k in db.keys() if db[k].get("staffid")}
    xl_staff = xl_with[xl_with["code"].isin(staff_codes)].copy()
    print(f"Excel rows for staff mapped in DB: {len(xl_staff)}")

    exact = 0
    punch_mismatch = []
    missing_db = []
    missing_xl = []
    inout_summary_mismatch = []
    open_in_mismatch = []

    for _, row in xl_staff.iterrows():
        key = (row["code"], row["date"])
        dbrow = db.get(key)
        if not dbrow:
            missing_db.append(key)
            continue
        xp = row["punches"]
        dp = dbrow["punches"]
        exact_sec = punch_key_list(xp) == punch_key_list(dp)
        exact_min = punch_key_list(xp, True) == punch_key_list(dp, True)
        if exact_sec:
            exact += 1
        elif exact_min:
            exact += 1  # only seconds differ
        else:
            punch_mismatch.append(
                {
                    "code": row["code"],
                    "name": row.get("EmployeeName"),
                    "date": row["date"],
                    "xl_n": len(xp),
                    "db_n": len(dp),
                    "xl": punch_key_list(xp, True),
                    "db": punch_key_list(dp, True),
                    "xl_raw": punch_key_list(xp),
                    "db_raw": punch_key_list(dp),
                }
            )

        # first IN / last OUT from punches vs summary columns
        if xp:
            xl_first_in = next((t for t, d in xp if d == "IN"), "")
            xl_last = xp[-1]
            xl_last_out = xp[-1][0] if xp[-1][1] == "OUT" else ""
            if row["a_in"] and xl_first_in and row["a_in"] != xl_first_in:
                inout_summary_mismatch.append(
                    (key, "excel A.InTime", row["a_in"], xl_first_in)
                )
            if dbrow["a_in"] and xl_first_in and dbrow["a_in"] != xl_first_in:
                inout_summary_mismatch.append(
                    (key, "db a_in_time", dbrow["a_in"], xl_first_in)
                )
            if xl_last[1] == "IN" and row["a_out"] and row["a_out"] != xl_last_out:
                open_in_mismatch.append(
                    {
                        "code": row["code"],
                        "date": row["date"],
                        "name": row.get("EmployeeName"),
                        "issue": "Last punch IN but A.OutTime set",
                        "a_out": row["a_out"],
                        "last": xl_last,
                    }
                )

    db_staff_keys = {(c, d) for (c, d), v in db.items() if v.get("staffid") and v["punch_n"] > 0}
    xl_keys = set(zip(xl_staff["code"], xl_staff["date"]))
    missing_xl = sorted(db_staff_keys - xl_keys)

    print("\n=== SUMMARY ===")
    exact_min_only = 0
    exact_full = 0
    for _, row in xl_staff.iterrows():
        key = (row["code"], row["date"])
        dbrow = db.get(key)
        if not dbrow:
            continue
        xp, dp = row["punches"], dbrow["punches"]
        if punch_key_list(xp) == punch_key_list(dp):
            exact_full += 1
        if punch_key_list(xp, True) == punch_key_list(dp, True):
            exact_min_only += 1

    print(f"Exact punch match (to the second): {exact_full} / {len(xl_staff)}")
    print(f"Exact punch match (minute-level): {exact_min_only} / {len(xl_staff)}")
    print(f"Punch list mismatch: {len(punch_mismatch)}")
    print(f"In Excel but missing in DB: {len(missing_db)}")
    print(f"In DB but missing in Excel: {len(missing_xl)}")
    print(f"Summary IN vs punch first IN issues: {len(inout_summary_mismatch)}")
    print(f"Open IN but A.OutTime set (Excel): {len(open_in_mismatch)}")

    if punch_mismatch:
        print("\n=== PUNCH MISMATCHES (first 25) ===")
        for m in punch_mismatch[:25]:
            print(
                f"{m['date']} | {m['code']} {m['name']} | xl={m['xl_n']} db={m['db_n']}"
            )
            if m["xl_n"] != m["db_n"]:
                print(f"  count diff")
            # show first diff
            for i, (a, b) in enumerate(zip(m["xl"], m["db"])):
                if a != b:
                    print(f"  first diff @#{i+1}: xl={a} db={b}")
                    break
            else:
                if len(m["xl"]) != len(m["db"]):
                    print(f"  xl tail: {m['xl'][-3:]}")
                    print(f"  db tail: {m['db'][-3:]}")

    if missing_db[:10]:
        print("\n=== MISSING IN DB (sample) ===")
        for k in missing_db[:15]:
            print(k)

    if missing_xl[:10]:
        print("\n=== IN DB NOT IN EXCEL (sample) ===")
        for k in missing_xl[:15]:
            v = db[k]
            print(k, v["name"], f"punches={v['punch_n']}")

    if open_in_mismatch:
        print("\n=== OPEN IN BUT OUT TIME SET (first 15) ===")
        for m in open_in_mismatch[:15]:
            print(f"{m['date']} {m['code']} {m['name']}: a_out={m['a_out']} last={m['last']}")

    # Category breakdown for real mismatches (minute-level)
    cats = defaultdict(int)
    night_shift_like = 0
    seconds_only = 0
    for _, row in xl_staff.iterrows():
        key = (row["code"], row["date"])
        dbrow = db.get(key)
        if not dbrow:
            continue
        xp, dp = row["punches"], dbrow["punches"]
        if punch_key_list(xp, True) == punch_key_list(dp, True):
            if punch_key_list(xp) != punch_key_list(dp):
                seconds_only += 1
            continue
        if dp and xp and dp[0][1] == "IN":
            h = int(dp[0][0].split(":")[0]) if dp[0][0] else 99
            if h < 6 and xp[0][1] == "IN" and int(xp[0][0].split(":")[0]) >= 12:
                night_shift_like += 1
                cats["night_shift_date_bucket"] += 1
                continue
        if dbrow["punch_n"] == 0:
            cats["db_empty"] += 1
        elif len(xp) > len(dp):
            cats["db_missing_punches"] += 1
        elif len(xp) < len(dp):
            cats["db_extra_punches"] += 1
        else:
            cats["same_count_wrong_times"] += 1

    print(f"Seconds-only difference (OK): {seconds_only}")
    print(f"Night-shift date bucket mismatch: {night_shift_like}")
    if cats:
        print("\n=== REAL MISMATCH CATEGORIES (minute-level) ===")
        for k, v in sorted(cats.items(), key=lambda x: -x[1]):
            print(f"  {k}: {v}")

    return 0


if __name__ == "__main__":
    sys.exit(main())
