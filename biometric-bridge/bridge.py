#!/usr/bin/env python3
"""
T2G Workroom ↔ Biomax live sync bridge.

Runs on an office PC that can reach the Biomax LAN API (e.g. 192.168.1.9:82).
Polls GetDeviceLogs, aggregates punches per employee/day, pushes to Workroom.
"""

from __future__ import annotations

import json
import logging
import sys
import time
from collections import defaultdict
from datetime import datetime, timedelta, timezone
from pathlib import Path
from typing import Any

try:
    import requests
except ImportError:
    print("Install dependencies: pip install requests", file=sys.stderr)
    raise

ROOT = Path(__file__).resolve().parent
CONFIG_PATH = ROOT / "config.json"
CHECKPOINT_PATH = ROOT / "checkpoint.json"


def load_json(path: Path, default: dict | None = None) -> dict:
    if not path.exists():
        return default or {}
    with path.open("r", encoding="utf-8") as fh:
        return json.load(fh)


def save_json(path: Path, data: dict) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    with path.open("w", encoding="utf-8") as fh:
        json.dump(data, fh, indent=2)


def setup_logging(log_file: str) -> None:
    log_path = ROOT / log_file
    log_path.parent.mkdir(parents=True, exist_ok=True)
    logging.basicConfig(
        level=logging.INFO,
        format="%(asctime)s [%(levelname)s] %(message)s",
        handlers=[
            logging.FileHandler(log_path, encoding="utf-8"),
            logging.StreamHandler(sys.stdout),
        ],
    )


def parse_log_datetime(value: Any) -> datetime | None:
    if value is None:
        return None
    text = str(value).strip()
    if not text:
        return None

    # Microsoft JSON date: /Date(1694073000000)/ or /Date(1694073000000+0530)/
    if text.startswith("/Date("):
        try:
            inner = text[6 : text.index(")")]
            for sep in ("+", "-"):
                if sep in inner[1:]:
                    inner = inner.split(sep if sep == "+" else sep, 1)[0]
                    break
            ms = int(inner)
            return datetime.fromtimestamp(ms / 1000.0)
        except Exception:
            pass

    formats = [
        "%Y-%m-%d %H:%M:%S",
        "%Y-%m-%dT%H:%M:%S",
        "%d/%m/%Y %H:%M:%S",
        "%m/%d/%Y %H:%M:%S",
        "%d-%m-%Y %H:%M:%S",
        "%Y-%m-%d %H:%M",
        "%d/%m/%Y %I:%M:%S %p",
        "%m/%d/%Y %I:%M:%S %p",
        "%d/%m/%Y %H:%M",
        "%m/%d/%Y %H:%M",
        "%d-%b-%Y %H:%M:%S",
        "%d-%b-%Y %I:%M:%S %p",
        "%d %b %Y %H:%M:%S",
        "%d-%m-%Y %I:%M:%S %p",
    ]
    cleaned = text.replace("T", " ")
    if "." in cleaned and cleaned.count(":") >= 2:
        cleaned = cleaned.split(".")[0]  # drop milliseconds
    candidates = [cleaned, cleaned[:19], text]
    for candidate in candidates:
        for fmt in formats:
            try:
                return datetime.strptime(candidate, fmt)
            except ValueError:
                continue
    try:
        return datetime.fromisoformat(text.replace("Z", "+00:00")).replace(tzinfo=None)
    except ValueError:
        return None


def punch_direction_label(raw: Any, index: int) -> str:
    text = str(raw or "").strip().lower()
    if text in ("in", "i", "0", "checkin", "check-in", "entry"):
        return "in"
    if text in ("out", "o", "1", "checkout", "check-out", "exit"):
        return "out"
    # Alternate when direction missing
    return "in" if index % 2 == 0 else "out"


def format_workroom_date(dt: datetime) -> str:
    # Always English month abbr so Workroom filters match (d-M-Y).
    months = ("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec")
    return f"{dt.day:02d}-{months[dt.month - 1]}-{dt.year}"


def format_hhmm(dt: datetime) -> str:
    return dt.strftime("%H:%M")


def duration_hhmm(start: datetime, end: datetime) -> str:
    seconds = max(0, int((end - start).total_seconds()))
    hours, rem = divmod(seconds, 3600)
    minutes = rem // 60
    return f"{hours:02d}:{minutes:02d}"


def aggregate_device_logs(logs: list[dict[str, Any]]) -> list[dict[str, Any]]:
    """
    Biomax GetDeviceLogs returns punch rows.
    Workroom expects one attendance row per employee per day.
    """
    buckets: dict[tuple[str, str], list[tuple[datetime, str, dict]]] = defaultdict(list)

    for row in logs:
        if not isinstance(row, dict):
            continue
        if row.get("status") is False:
            continue

        code = str(
            row.get("EmployeeCode")
            or row.get("employee_code")
            or row.get("UserId")
            or row.get("EmpCode")
            or ""
        ).strip()
        if not code:
            continue

        dt = parse_log_datetime(
            row.get("LogDate")
            or row.get("log_date")
            or row.get("LogDateTime")
            or row.get("AttendanceDateTime")
            or row.get("PunchTime")
            or row.get("DateTime")
            or row.get("punch_time")
        )
        if not dt:
            continue

        day_key = format_workroom_date(dt)
        buckets[(code, day_key)].append((dt, str(row.get("PunchDirection") or row.get("Direction") or ""), row))

    # Help diagnose missing "today" rows
    if buckets:
        by_day: dict[str, int] = defaultdict(int)
        for (_code, day_key), punches in buckets.items():
            by_day[day_key] += 1
        logging.info(
            "Aggregated %s employee-day row(s) across dates: %s",
            len(buckets),
            ", ".join(f"{d}={by_day[d]}" for d in sorted(by_day.keys())),
        )
    else:
        skipped_no_date = 0
        sample_keys = []
        for row in logs[:5]:
            if isinstance(row, dict):
                sample_keys.append(sorted(row.keys()))
                if not parse_log_datetime(
                    row.get("LogDate")
                    or row.get("log_date")
                    or row.get("LogDateTime")
                    or row.get("AttendanceDateTime")
                    or row.get("PunchTime")
                    or row.get("DateTime")
                ):
                    skipped_no_date += 1
        logging.warning(
            "No punches could be aggregated (sample keys=%s). Check LogDate format.",
            sample_keys[:2],
        )

    records: list[dict[str, Any]] = []
    latest_punch: datetime | None = None

    for (code, day_key), punches in buckets.items():
        punches.sort(key=lambda x: x[0])
        punch_bits = []
        labeled: list[tuple[datetime, str]] = []
        for i, (dt, direction, _row) in enumerate(punches):
            label = punch_direction_label(direction, i)
            labeled.append((dt, label))
            punch_bits.append(f"{format_hhmm(dt)} ({label})")
            if latest_punch is None or dt > latest_punch:
                latest_punch = dt

        first = punches[0][0]
        last = punches[-1][0]
        # Out time = last real OUT only (never treat a final IN as Out).
        last_out = None
        for dt, label in reversed(labeled):
            if label == "out":
                last_out = dt
                break
        # Work duration = sum of completed In→Out sessions (open IN ignored).
        worked = timedelta(0)
        open_in: datetime | None = None
        for dt, label in labeled:
            if label == "in":
                open_in = dt
            elif label == "out" and open_in is not None:
                worked += dt - open_in
                open_in = None
        work_secs = max(0, int(worked.total_seconds()))
        work_hh = f"{work_secs // 3600:02d}:{(work_secs % 3600) // 60:02d}"

        name = str(
            punches[0][2].get("EmployeeName")
            or punches[0][2].get("employee_name")
            or ""
        ).strip()

        records.append(
            {
                "employee_code": code,
                "employee_name": name,
                "attendance_date": day_key,
                "a_in_time": format_hhmm(first),
                "a_out_time": format_hhmm(last_out) if last_out else "",
                "work_duration": work_hh if work_secs > 0 else "",
                "punch_records": ", ".join(punch_bits),
                "status": "Present" if punches else "",
                "location": str(punches[0][2].get("Location") or ""),
                "remark": "biomax-bridge",
                "last_punch_time": last.isoformat(sep=" "),
            }
        )

    # Keep global latest punch for checkpoint (do not overwrite per-row last punch).
    records.sort(key=lambda r: (r["attendance_date"], r["employee_code"]))
    if latest_punch:
        # Stash on every row only as transport metadata for checkpoint update.
        for row in records:
            row["_checkpoint_last_punch"] = latest_punch.isoformat(sep=" ")
    return records


def fetch_biomax_logs_range(cfg: dict, from_date, to_date) -> list[dict[str, Any]]:
    """One Biomax GetDeviceLogs call for an inclusive date range."""
    api_url = (cfg.get("biomax_api_url") or "").strip().rstrip("?")
    api_key = (cfg.get("biomax_api_key") or "").strip()
    if not api_url or not api_key:
        logging.info("Biomax API URL/key not configured — skipping fetch")
        return []

    params = {
        "APIKey": api_key,
        "FromDate": from_date.isoformat() if hasattr(from_date, "isoformat") else str(from_date),
        "ToDate": to_date.isoformat() if hasattr(to_date, "isoformat") else str(to_date),
    }
    if cfg.get("biomax_serial_number"):
        params["SerialNumber"] = cfg["biomax_serial_number"]

    logging.info("Fetching Biomax logs %s → %s", params["FromDate"], params["ToDate"])
    resp = requests.get(api_url, params=params, timeout=120)
    resp.raise_for_status()

    try:
        payload = resp.json()
    except Exception:
        logging.error("Biomax response is not JSON: %s", resp.text[:500])
        return []

    if isinstance(payload, dict) and payload.get("status") is False:
        logging.error("Biomax API error: %s", payload.get("message") or payload)
        return []

    if isinstance(payload, dict):
        logs = (
            payload.get("data")
            or payload.get("DeviceLogs")
            or payload.get("Logs")
            or payload.get("records")
            or []
        )
    else:
        logs = payload

    if not isinstance(logs, list):
        logging.error("Unexpected Biomax payload type: %s", type(payload))
        return []

    logging.info("Biomax returned %s punch log(s)", len(logs))
    return logs


def fetch_biomax_records(cfg: dict, checkpoint: dict, *, force_from=None, force_to=None) -> list[dict[str, Any]]:
    lookback_days = max(1, int(cfg.get("lookback_days") or 2))
    chunk_days = max(1, int(cfg.get("chunk_days") or 7))
    # Office is IST — do not use UTC midnight for "today".
    try:
        from zoneinfo import ZoneInfo
        today = datetime.now(ZoneInfo("Asia/Kolkata")).date()
    except Exception:
        today = datetime.now().date()

    if force_from is not None:
        from_date = force_from if hasattr(force_from, "year") else datetime.strptime(str(force_from)[:10], "%Y-%m-%d").date()
    elif cfg.get("backfill_from_date"):
        from_date = datetime.strptime(str(cfg["backfill_from_date"])[:10], "%Y-%m-%d").date()
    else:
        from_date = today - timedelta(days=lookback_days)

    if force_to is not None:
        to_date = force_to if hasattr(force_to, "year") else datetime.strptime(str(force_to)[:10], "%Y-%m-%d").date()
    else:
        # Biomax GetDeviceLogs often treats ToDate as end-exclusive / incomplete for
        # the current day. Fetch through today+2 so today's punches always return.
        to_date = today + timedelta(days=2)

    # Normal live mode: also include checkpoint day so we don't miss late punches
    if force_from is None and not cfg.get("backfill_from_date"):
        last_punch = checkpoint.get("last_punch_time")
        if last_punch:
            try:
                last_dt = parse_log_datetime(last_punch) or datetime.fromisoformat(str(last_punch))
                from_date = min(from_date, last_dt.date())
            except Exception:
                pass

    if from_date > to_date:
        from_date, to_date = to_date, from_date

    # Chunk long ranges — Biomax often truncates large windows
    all_logs: list[dict[str, Any]] = []
    cursor = from_date
    while cursor <= to_date:
        chunk_end = min(cursor + timedelta(days=chunk_days - 1), to_date)
        chunk_logs = fetch_biomax_logs_range(cfg, cursor, chunk_end)
        all_logs.extend(chunk_logs)
        cursor = chunk_end + timedelta(days=1)
        if cursor <= to_date:
            time.sleep(0.5)

    logging.info("Total raw punch logs across chunks: %s (today=%s, fetch to %s)", len(all_logs), today.isoformat(), to_date.isoformat())
    records = aggregate_device_logs(all_logs)

    # Push newest days first so latest-day data lands even if a later batch fails.
    def _day_sort_key(row: dict) -> tuple:
        try:
            return datetime.strptime(row.get("attendance_date") or "", "%d-%b-%Y")
        except Exception:
            return datetime.min

    records.sort(key=lambda r: (_day_sort_key(r), r.get("employee_code") or ""), reverse=True)
    return records


def push_to_workroom(cfg: dict, records: list[dict[str, Any]]) -> dict:
    url = cfg["workroom_url"]
    token = cfg["workroom_token"]
    batch_size = max(50, int(cfg.get("push_batch_size") or 300))

    totals = {"received": 0, "inserted": 0, "updated": 0, "skipped": 0}
    for i in range(0, len(records), batch_size):
        batch = records[i : i + batch_size]
        clean = []
        for row in batch:
            item = dict(row)
            item.pop("last_punch_time", None)
            item.pop("_checkpoint_last_punch", None)
            clean.append(item)

        resp = requests.post(
            url,
            headers={
                "Content-Type": "application/json",
                "X-Workroom-Token": token,
            },
            json={"records": clean, "checkpoint": datetime.now(timezone.utc).isoformat()},
            timeout=180,
        )
        resp.raise_for_status()
        result = resp.json()
        for k in totals:
            totals[k] += int(result.get(k) or 0)
        logging.info(
            "Pushed batch %s–%s / %s → inserted=%s updated=%s",
            i + 1,
            min(i + batch_size, len(records)),
            len(records),
            result.get("inserted"),
            result.get("updated"),
        )
    return totals


def run_fix_today(cfg: dict) -> None:
    """Diagnose + force-push last few days including today (IST)."""
    try:
        from zoneinfo import ZoneInfo
        today = datetime.now(ZoneInfo("Asia/Kolkata")).date()
    except Exception:
        today = datetime.now().date()

    from_date = today - timedelta(days=3)
    to_date = today + timedelta(days=2)

    logging.info("=== FIX TODAY ===")
    logging.info("PC thinks today (IST) = %s", today.isoformat())
    logging.info("Will fetch Biomax %s → %s", from_date.isoformat(), to_date.isoformat())

    # 1) Workroom reachable?
    health_url = (cfg.get("workroom_health_url") or "").strip()
    if health_url:
        try:
            health = requests.get(health_url, timeout=20)
            logging.info("Workroom health: HTTP %s", health.status_code)
            if not health.ok:
                logging.error("Workroom not OK — fix DNS/internet first (open https://t2gworkroom.com)")
                return
        except Exception as exc:
            logging.error("Cannot reach Workroom (%s). Fix DNS/internet, then retry.", exc)
            return
    else:
        logging.warning("No workroom_health_url in config")

    # 2) Biomax raw for today window
    try:
        raw = fetch_biomax_logs_range(cfg, from_date, to_date)
        logging.info("Biomax raw punches in window: %s", len(raw))
        if raw:
            sample = raw[0] if isinstance(raw[0], dict) else {}
            logging.info(
                "Sample punch keys=%s LogDate=%s Emp=%s",
                sorted(sample.keys())[:12] if isinstance(sample, dict) else type(sample),
                sample.get("LogDate") or sample.get("log_date") or sample.get("LogDateTime"),
                sample.get("EmployeeCode") or sample.get("EmpCode"),
            )
        else:
            logging.error(
                "Biomax returned ZERO punches for %s→%s. "
                "Open SmartOffice and sync device logs for TODAY, then run fix_today.bat again.",
                from_date,
                to_date,
            )
            return
    except Exception as exc:
        logging.exception("Biomax fetch failed: %s", exc)
        logging.error("Is SmartOffice running at http://127.0.0.1:82 ?")
        return

    # 3) Aggregate + show dates
    records = aggregate_device_logs(raw)
    by_day: dict[str, int] = defaultdict(int)
    for row in records:
        by_day[row.get("attendance_date") or "?"] += 1
    logging.info(
        "Employee-day rows: %s | dates: %s",
        len(records),
        ", ".join(f"{d}={by_day[d]}" for d in sorted(by_day.keys())),
    )
    today_key = format_workroom_date(datetime(today.year, today.month, today.day))
    if today_key not in by_day:
        logging.error(
            "NO ROWS FOR TODAY (%s). Biomax has punches but none dated today. "
            "Check office PC date/time and SmartOffice device download.",
            today_key,
        )
    else:
        logging.info("TODAY (%s) has %s employee row(s) — pushing to Workroom…", today_key, by_day[today_key])

    if not records:
        return

    result = push_to_workroom(cfg, records)
    logging.info(
        "FIX TODAY done — received=%s inserted=%s updated=%s skipped=%s",
        result.get("received"),
        result.get("inserted"),
        result.get("updated"),
        result.get("skipped"),
    )
    print("")
    print("Done. Check https://t2gworkroom.com/admin/biometric for", today_key)
    if today_key not in by_day:
        print("WARNING: Biomax had no punches dated", today_key)
        print("Fix SmartOffice device sync first, then run fix_today.bat again.")


def run_once(cfg: dict) -> None:
    cp_name = cfg.get("checkpoint_file") or "checkpoint.json"
    checkpoint = load_json(ROOT / cp_name, {})
    # Live mode ignores one-shot backfill_from_date unless explicitly left set
    live_cfg = dict(cfg)
    live_cfg.pop("backfill_from_date", None)
    records = fetch_biomax_records(live_cfg, checkpoint)
    if not records:
        logging.info("No Biomax attendance rows to sync")
        return

    result = push_to_workroom(cfg, records)
    logging.info(
        "Workroom sync ok — received=%s inserted=%s updated=%s skipped=%s",
        result.get("received"),
        result.get("inserted"),
        result.get("updated"),
        result.get("skipped"),
    )

    checkpoint["last_run"] = datetime.now(timezone.utc).isoformat()
    last_punch = None
    for row in records:
        last_punch = row.get("_checkpoint_last_punch") or row.get("last_punch_time") or last_punch
    if last_punch:
        checkpoint["last_punch_time"] = last_punch
    save_json(ROOT / cp_name, checkpoint)


def run_backfill(cfg: dict, from_date_str: str | None = None) -> None:
    """One-shot: pull FromDate → today in weekly chunks and push everything."""
    from_str = from_date_str or cfg.get("backfill_from_date") or "2025-07-01"
    from_date = datetime.strptime(str(from_str)[:10], "%Y-%m-%d").date()
    to_date = datetime.now().date()
    logging.info("BACKFILL starting %s → %s (chunked)", from_date, to_date)

    records = fetch_biomax_records(cfg, {}, force_from=from_date, force_to=to_date)
    if not records:
        logging.warning(
            "Backfill got 0 rows. Open Biomax API in browser for this range — "
            "if empty, devices are not downloading into SmartOffice."
        )
        return

    # Show which dates we actually got
    by_day: dict[str, int] = defaultdict(int)
    for row in records:
        by_day[row["attendance_date"]] += 1
    logging.info(
        "Backfill ready to push %s employee-days. Date span: %s … %s (%s distinct days)",
        len(records),
        min(by_day.keys()) if by_day else "-",
        max(by_day.keys()) if by_day else "-",
        len(by_day),
    )

    result = push_to_workroom(cfg, records)
    logging.info(
        "BACKFILL done — received=%s inserted=%s updated=%s skipped=%s",
        result.get("received"),
        result.get("inserted"),
        result.get("updated"),
        result.get("skipped"),
    )

    cp_name = cfg.get("checkpoint_file") or "checkpoint.json"
    checkpoint = load_json(ROOT / cp_name, {})
    checkpoint["last_run"] = datetime.now(timezone.utc).isoformat()
    checkpoint["last_backfill"] = {
        "from": from_date.isoformat(),
        "to": to_date.isoformat(),
        "rows": len(records),
    }
    last_punch = None
    for row in records:
        last_punch = row.get("_checkpoint_last_punch") or row.get("last_punch_time") or last_punch
    if last_punch:
        checkpoint["last_punch_time"] = last_punch
    save_json(ROOT / cp_name, checkpoint)


def main() -> None:
    if not CONFIG_PATH.exists():
        print(f"Copy config.example.json to {CONFIG_PATH.name} and edit values.", file=sys.stderr)
        sys.exit(1)

    cfg = load_json(CONFIG_PATH)
    setup_logging(cfg.get("log_file") or "logs/bridge.log")

    # python bridge.py --fix-today   (diagnose + force push last few days)
    if len(sys.argv) >= 2 and sys.argv[1] in ("--fix-today", "fix-today", "--today", "today"):
        run_fix_today(cfg)
        return

    # python bridge.py --backfill [YYYY-MM-DD]
    if len(sys.argv) >= 2 and sys.argv[1] in ("--backfill", "backfill"):
        from_arg = sys.argv[2] if len(sys.argv) >= 3 else None
        run_backfill(cfg, from_arg)
        return

    interval = max(60, int(cfg.get("poll_interval_seconds") or 120))

    logging.info("T2G biometric bridge started — interval %ss", interval)
    logging.info("Biomax API: %s", cfg.get("biomax_api_url"))

    while True:
        try:
            health_url = cfg.get("workroom_health_url") or ""
            if health_url:
                health = requests.get(health_url, timeout=15)
                if not health.ok:
                    logging.warning("Workroom health check failed: %s", health.status_code)
                    time.sleep(interval)
                    continue
            run_once(cfg)
        except Exception as exc:
            logging.exception("Bridge cycle failed: %s", exc)
        time.sleep(interval)


if __name__ == "__main__":
    main()
