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
        for i, (dt, direction, _row) in enumerate(punches):
            label = punch_direction_label(direction, i)
            punch_bits.append(f"{format_hhmm(dt)} ({label})")
            if latest_punch is None or dt > latest_punch:
                latest_punch = dt

        first = punches[0][0]
        last = punches[-1][0]
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
                "a_out_time": format_hhmm(last) if len(punches) > 1 else "",
                "work_duration": duration_hhmm(first, last) if len(punches) > 1 else "",
                "punch_records": ", ".join(punch_bits),
                "status": "Present" if punches else "",
                "location": str(punches[0][2].get("Location") or ""),
                "remark": "biomax-bridge",
                "last_punch_time": latest_punch.isoformat(sep=" ") if latest_punch else "",
            }
        )

    records.sort(key=lambda r: (r["attendance_date"], r["employee_code"]))
    if latest_punch:
        for row in records:
            row["last_punch_time"] = latest_punch.isoformat(sep=" ")
    return records


def fetch_biomax_records(cfg: dict, checkpoint: dict) -> list[dict[str, Any]]:
    api_url = (cfg.get("biomax_api_url") or "").strip().rstrip("?")
    api_key = (cfg.get("biomax_api_key") or "").strip()
    if not api_url or not api_key:
        logging.info("Biomax API URL/key not configured — skipping fetch")
        return []

    lookback_days = max(1, int(cfg.get("lookback_days") or 2))
    today = datetime.now().date()
    from_date = today - timedelta(days=lookback_days)
    to_date = today

    # Resume from checkpoint day when available
    last_punch = checkpoint.get("last_punch_time")
    if last_punch:
        try:
            last_dt = parse_log_datetime(last_punch) or datetime.fromisoformat(str(last_punch))
            from_date = min(from_date, last_dt.date())
        except Exception:
            pass

    params = {
        "APIKey": api_key,
        "FromDate": from_date.isoformat(),
        "ToDate": to_date.isoformat(),
    }
    if cfg.get("biomax_serial_number"):
        params["SerialNumber"] = cfg["biomax_serial_number"]

    logging.info("Fetching Biomax logs %s → %s", params["FromDate"], params["ToDate"])
    resp = requests.get(api_url, params=params, timeout=90)
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
    return aggregate_device_logs(logs)


def push_to_workroom(cfg: dict, records: list[dict[str, Any]]) -> dict:
    url = cfg["workroom_url"]
    token = cfg["workroom_token"]
    # Strip helper field before push
    clean = []
    for row in records:
        item = dict(row)
        item.pop("last_punch_time", None)
        clean.append(item)

    resp = requests.post(
        url,
        headers={
            "Content-Type": "application/json",
            "X-Workroom-Token": token,
        },
        json={"records": clean, "checkpoint": datetime.now(timezone.utc).isoformat()},
        timeout=120,
    )
    resp.raise_for_status()
    return resp.json()


def run_once(cfg: dict) -> None:
    cp_name = cfg.get("checkpoint_file") or "checkpoint.json"
    checkpoint = load_json(ROOT / cp_name, {})
    records = fetch_biomax_records(cfg, checkpoint)
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
    last_punch = records[-1].get("last_punch_time") if records else None
    if last_punch:
        checkpoint["last_punch_time"] = last_punch
    save_json(ROOT / cp_name, checkpoint)


def main() -> None:
    if not CONFIG_PATH.exists():
        print(f"Copy config.example.json to {CONFIG_PATH.name} and edit values.", file=sys.stderr)
        sys.exit(1)

    cfg = load_json(CONFIG_PATH)
    setup_logging(cfg.get("log_file") or "logs/bridge.log")
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
