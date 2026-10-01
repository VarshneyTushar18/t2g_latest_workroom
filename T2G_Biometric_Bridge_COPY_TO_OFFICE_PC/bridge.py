#!/usr/bin/env python3
"""
T2G Workroom ↔ Biometric live sync bridge.

Runs on an office PC that can reach the Biometric LAN API (e.g. 192.168.1.9:82).
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


# Match Workroom Timesheets_model::ATTENDANCE_SESSION_WINDOW_HOURS
DEFAULT_SESSION_WINDOW_HOURS = 13
DEDUP_PUNCH_SECONDS = 120
# Device often sends blank PunchDirection — reset IN/OUT guessing after idle gaps.
GAP_RESET_MINUTES = 240
NIGHT_CLUSTER_MINUTES = 120
MORNING_SHIFT_HOUR = 6
# Typical day-shift arrival window (9:30am / 11am shifts punch ~08:00-12:59).
MORNING_DAY_SHIFT_START_HOUR = 8
MORNING_DAY_SHIFT_END_HOUR = 13
# After evening OUT, a lone 00:xx–05:xx swipe 90min–4h later is usually final exit (not break return).
NIGHT_EXIT_AFTER_OUT_MIN_MINUTES = 90


def is_night_final_exit_after_out(prev_dt: datetime, prev_label: str, dt: datetime) -> bool:
    """True when a post-midnight swipe likely ends a night shift (not a short break return)."""
    if prev_label != "out":
        return False
    gap = dt - prev_dt
    min_gap = timedelta(minutes=NIGHT_EXIT_AFTER_OUT_MIN_MINUTES)
    max_gap = timedelta(minutes=GAP_RESET_MINUTES)
    return (
        dt.date() != prev_dt.date()
        and dt.hour < MORNING_SHIFT_HOUR
        and prev_dt.hour >= 15
        and min_gap <= gap < max_gap
    )


def known_punch_direction(raw: Any) -> str | None:
    text = str(raw or "").strip().lower()
    if text in ("in", "i", "0", "checkin", "check-in", "entry"):
        return "in"
    if text in ("out", "o", "1", "checkout", "check-out", "exit"):
        return "out"
    return None


def label_punch_sequence(punches: list[tuple[datetime, str]]) -> list[tuple[datetime, str]]:
    """
    Label each punch IN/OUT. Device direction wins; otherwise alternate with
    gap-aware resets (blank PunchDirection is common on office devices).
    """
    labeled: list[tuple[datetime, str]] = []
    expect = "in"
    prev_dt: datetime | None = None
    gap_reset = timedelta(minutes=GAP_RESET_MINUTES)
    night_cluster = timedelta(minutes=NIGHT_CLUSTER_MINUTES)

    for dt, raw_dir in punches:
        known = known_punch_direction(raw_dir)
        if not known and prev_dt is not None and labeled:
            gap = dt - prev_dt
            prev_label = labeled[-1][1]
            if prev_label == "in":
                if dt.date() == prev_dt.date() and dt.hour < MORNING_SHIFT_HOUR:
                    # Midnight re-swipes vs leaving before dawn on the same sheet-day.
                    expect = "in" if gap < night_cluster else "out"
                elif gap >= gap_reset:
                    if (
                        dt.date() == prev_dt.date()
                        and dt.hour >= MORNING_SHIFT_HOUR
                        and prev_dt.hour < MORNING_SHIFT_HOUR
                    ):
                        # Midnight carryover IN + later daytime arrival.
                        expect = "in"
                    elif (
                        dt.date() != prev_dt.date()
                        and MORNING_DAY_SHIFT_START_HOUR <= dt.hour < MORNING_DAY_SHIFT_END_HOUR
                    ):
                        # Next-day morning arrival (e.g. prior evening IN still open on sheet).
                        expect = "in"
                    elif (
                        dt.date() != prev_dt.date()
                        and dt.hour < 12
                        and prev_dt.hour >= 15
                    ):
                        # Night shift end (e.g. 18:00 IN → 03:00/06:00 OUT next day).
                        expect = "out"
                    elif dt.date() == prev_dt.date() and prev_dt.hour >= MORNING_SHIFT_HOUR:
                        # Normal day shift: long gap after daytime IN → leaving for the day.
                        expect = "out"
                    else:
                        expect = "in"
                elif dt.date() != prev_dt.date() and dt.hour >= MORNING_SHIFT_HOUR:
                    expect = "in"
            elif prev_label == "out":
                if is_night_final_exit_after_out(prev_dt, prev_label, dt):
                    # Evening OUT → final exit swipe after midnight (blank device direction).
                    expect = "out"
                elif gap >= gap_reset:
                    expect = "in"

        if known:
            label = known
            expect = "out" if label == "in" else "in"
        else:
            label = expect
            expect = "out" if label == "in" else "in"
        labeled.append((dt, label))
        prev_dt = dt
    return labeled


def dedupe_adjacent_punches(
    labeled: list[tuple[datetime, str]], window_sec: int = DEDUP_PUNCH_SECONDS
) -> list[tuple[datetime, str]]:
    """Drop duplicate swipes within a short window (same direction)."""
    if not labeled:
        return []
    deduped: list[tuple[datetime, str]] = [labeled[0]]
    for dt, label in labeled[1:]:
        prev_dt, prev_label = deduped[-1]
        if label == prev_label and abs(int((dt - prev_dt).total_seconds())) <= window_sec:
            continue
        deduped.append((dt, label))
    return deduped


def collapse_duplicate_open_in_punches(labeled: list[tuple[datetime, str]]) -> list[tuple[datetime, str]]:
    """Drop extra IN punches while already checked in (e.g. midnight re-swipe)."""
    collapsed: list[tuple[datetime, str]] = []
    open_in = False
    last_in_dt: datetime | None = None
    gap_reset = timedelta(minutes=GAP_RESET_MINUTES)

    for dt, label in labeled:
        if label == "in":
            if open_in and last_in_dt is not None:
                gap = dt - last_in_dt
                # Long gap after a carryover IN → new work segment (morning users).
                if gap >= gap_reset and dt.hour >= MORNING_SHIFT_HOUR:
                    open_in = False
            if open_in:
                continue
            open_in = True
            last_in_dt = dt
            collapsed.append((dt, label))
            continue
        if label == "out":
            open_in = False
            last_in_dt = None
        collapsed.append((dt, label))
    return collapsed


def fix_trailing_night_exit_mistake_in(
    labeled: list[tuple[datetime, str]],
) -> list[tuple[datetime, str]]:
    """Flip a terminal early-morning IN that should have been OUT (night shift exit)."""
    if len(labeled) < 2:
        return labeled
    prev_dt, prev_label = labeled[-2]
    last_dt, last_label = labeled[-1]
    if last_label == "in" and is_night_final_exit_after_out(prev_dt, prev_label, last_dt):
        return labeled[:-1] + [(last_dt, "out")]
    return labeled


def build_attendance_sessions(
    labeled: list[tuple[datetime, str]], window_hours: int = DEFAULT_SESSION_WINDOW_HOURS
) -> list[dict[str, Any]]:
    """
    Group punches into work sessions: first IN starts a session; punches within
    window_hours belong to that day (night carryover matches department report).
    """
    window = timedelta(hours=max(1, window_hours))
    sessions: list[dict[str, Any]] = []
    i = 0
    n = len(labeled)

    while i < n:
        while i < n and labeled[i][1] != "in":
            i += 1
        if i >= n:
            break

        start_at = labeled[i][0]
        end_at = start_at + window
        events: list[tuple[datetime, str]] = []

        while i < n and labeled[i][0] <= end_at:
            events.append(labeled[i])
            i += 1

        if events:
            sessions.append(
                {
                    "start_at": start_at,
                    "start_date": format_workroom_date(start_at),
                    "events": events,
                }
            )

    return sessions


def build_attendance_record_from_events(
    code: str,
    day_key: str,
    events: list[tuple[datetime, str]],
    sample_row: dict[str, Any],
) -> dict[str, Any]:
    """One Workroom row from a session's punch list."""
    labeled = events
    punch_bits = [f"{format_hhmm(dt)} ({label})" for dt, label in labeled]

    first_in = next((dt for dt, label in labeled if label == "in"), None)
    last_out = None
    if labeled and labeled[-1][1] != "in":
        last_out = next((dt for dt, label in reversed(labeled) if label == "out"), None)

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
        sample_row.get("EmployeeName") or sample_row.get("employee_name") or ""
    ).strip()

    return {
        "employee_code": code,
        "employee_name": name,
        "attendance_date": day_key,
        "a_in_time": format_hhmm(first_in) if first_in else "",
        "a_out_time": format_hhmm(last_out) if last_out else "",
        "work_duration": work_hh if work_secs > 0 else "",
        "punch_records": ", ".join(punch_bits),
        "status": "Present" if labeled else "",
        "location": str(sample_row.get("Location") or ""),
        "remark": "biometric-bridge",
        "last_punch_time": labeled[-1][0].isoformat(sep=" "),
    }


def format_workroom_date(dt: datetime) -> str:
    # Always English month abbr so Workroom filters match (d-M-Y).
    months = ("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec")
    return f"{dt.day:02d}-{months[dt.month - 1]}-{dt.year}"


def format_hhmm(dt: datetime) -> str:
    return dt.strftime("%H:%M:%S")


def format_hhmm_short(dt: datetime) -> str:
    return dt.strftime("%H:%M")


def duration_hhmm(start: datetime, end: datetime) -> str:
    seconds = max(0, int((end - start).total_seconds()))
    hours, rem = divmod(seconds, 3600)
    minutes = rem // 60
    return f"{hours:02d}:{minutes:02d}"


def aggregate_device_logs(
    logs: list[dict[str, Any]], session_window_hours: int = DEFAULT_SESSION_WINDOW_HOURS
) -> list[dict[str, Any]]:
    """
    Biometric GetDeviceLogs returns punch rows.
    Workroom expects one attendance row per employee per shift-day.

    Punches are grouped into 13h sessions from first IN (not calendar midnight),
    matching Workroom calendar / department report night-shift behaviour.
    """
    by_employee: dict[str, list[tuple[datetime, str, dict]]] = defaultdict(list)
    seen_keys: set[tuple[str, datetime]] = set()

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

        dedupe_key = (code, dt.replace(microsecond=0))
        if dedupe_key in seen_keys:
            continue
        seen_keys.add(dedupe_key)

        direction = str(row.get("PunchDirection") or row.get("Direction") or "")
        by_employee[code].append((dt, direction, row))

    if not by_employee:
        sample_keys = []
        for row in logs[:5]:
            if isinstance(row, dict):
                sample_keys.append(sorted(row.keys()))
        logging.warning(
            "No punches could be aggregated (sample keys=%s). Check LogDate format.",
            sample_keys[:2],
        )
        return []

    day_buckets: dict[tuple[str, str], list[tuple[datetime, str, dict]]] = defaultdict(list)
    latest_punch: datetime | None = None

    for code, punches in by_employee.items():
        punches.sort(key=lambda x: x[0])
        raw_seq = [(dt, direction) for dt, direction, _row in punches]
        labeled = label_punch_sequence(raw_seq)
        labeled = dedupe_adjacent_punches(labeled)
        labeled = collapse_duplicate_open_in_punches(labeled)
        labeled = fix_trailing_night_exit_mistake_in(labeled)

        for dt, _label in labeled:
            if latest_punch is None or dt > latest_punch:
                latest_punch = dt

        sessions = build_attendance_sessions(labeled, session_window_hours)
        for session in sessions:
            day_key = session["start_date"]
            events = session["events"]
            if not events:
                continue
            # Re-attach sample row dict for name/location (nearest punch row).
            event_times = {dt for dt, _ in events}
            sample_row = next(
                (row for dt, _d, row in punches if dt in event_times),
                punches[0][2],
            )
            for dt, label in events:
                day_buckets[(code, day_key)].append((dt, label, sample_row))

    records: list[dict[str, Any]] = []
    for (code, day_key), bucket in day_buckets.items():
        bucket.sort(key=lambda x: x[0])
        events = [(dt, label) for dt, label, _row in bucket]
        sample_row = bucket[0][2]
        records.append(build_attendance_record_from_events(code, day_key, events, sample_row))

    by_day: dict[str, int] = defaultdict(int)
    for _code, day_key in day_buckets:
        by_day[day_key] += 1
    logging.info(
        "Aggregated %s employee-day row(s) across dates: %s",
        len(records),
        ", ".join(f"{d}={by_day[d]}" for d in sorted(by_day.keys())),
    )

    records.sort(key=lambda r: (r["attendance_date"], r["employee_code"]))
    if latest_punch:
        for row in records:
            row["_checkpoint_last_punch"] = latest_punch.isoformat(sep=" ")
    return records


def fetch_biometric_logs_range(cfg: dict, from_date, to_date) -> list[dict[str, Any]]:
    """One Biometric GetDeviceLogs call for an inclusive date range."""
    # Prefer biometric_* keys; fall back to legacy biomax_* if present.
    api_url = (cfg.get("biometric_api_url") or cfg.get("biomax_api_url") or "").strip().rstrip("?")
    api_key = (cfg.get("biometric_api_key") or cfg.get("biomax_api_key") or "").strip()
    if not api_url or not api_key:
        logging.info("Biometric API URL/key not configured — skipping fetch")
        return []

    params = {
        "APIKey": api_key,
        "FromDate": from_date.isoformat() if hasattr(from_date, "isoformat") else str(from_date),
        "ToDate": to_date.isoformat() if hasattr(to_date, "isoformat") else str(to_date),
    }
    serial = cfg.get("biometric_serial_number") or cfg.get("biomax_serial_number")
    if serial:
        params["SerialNumber"] = serial

    logging.info("Fetching Biometric logs %s → %s", params["FromDate"], params["ToDate"])
    resp = requests.get(api_url, params=params, timeout=120)
    resp.raise_for_status()

    try:
        payload = resp.json()
    except Exception:
        logging.error("Biometric response is not JSON: %s", resp.text[:500])
        return []

    if isinstance(payload, dict) and payload.get("status") is False:
        logging.error("Biometric API error: %s", payload.get("message") or payload)
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
        logging.error("Unexpected Biometric payload type: %s", type(payload))
        return []

    logging.info("Biometric returned %s punch log(s)", len(logs))
    return logs


def fetch_biometric_records(cfg: dict, checkpoint: dict, *, force_from=None, force_to=None) -> list[dict[str, Any]]:
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
        # Biometric GetDeviceLogs often treats ToDate as end-exclusive / incomplete for
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

    # Chunk long ranges — Biometric often truncates large windows
    all_logs: list[dict[str, Any]] = []
    cursor = from_date
    while cursor <= to_date:
        chunk_end = min(cursor + timedelta(days=chunk_days - 1), to_date)
        chunk_logs = fetch_biometric_logs_range(cfg, cursor, chunk_end)
        all_logs.extend(chunk_logs)
        cursor = chunk_end + timedelta(days=1)
        if cursor <= to_date:
            time.sleep(0.5)

    logging.info("Total raw punch logs across chunks: %s (today=%s, fetch to %s)", len(all_logs), today.isoformat(), to_date.isoformat())
    window_hours = max(1, int(cfg.get("session_window_hours") or DEFAULT_SESSION_WINDOW_HOURS))
    records = aggregate_device_logs(all_logs, session_window_hours=window_hours)

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
    logging.info("Will fetch Biometric %s → %s", from_date.isoformat(), to_date.isoformat())

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

    # 2) Biometric raw for today window
    try:
        raw = fetch_biometric_logs_range(cfg, from_date, to_date)
        logging.info("Biometric raw punches in window: %s", len(raw))
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
                "Biometric returned ZERO punches for %s→%s. "
                "Open SmartOffice and sync device logs for TODAY, then run fix_today.bat again.",
                from_date,
                to_date,
            )
            return
    except Exception as exc:
        logging.exception("Biometric fetch failed: %s", exc)
        logging.error("Is SmartOffice running at http://127.0.0.1:82 ?")
        return

    # 3) Aggregate + show dates
    window_hours = max(1, int(cfg.get("session_window_hours") or DEFAULT_SESSION_WINDOW_HOURS))
    records = aggregate_device_logs(raw, session_window_hours=window_hours)
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
            "NO ROWS FOR TODAY (%s). Biometric has punches but none dated today. "
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
        print("WARNING: Biometric had no punches dated", today_key)
        print("Fix SmartOffice device sync first, then run fix_today.bat again.")


def run_once(cfg: dict) -> None:
    cp_name = cfg.get("checkpoint_file") or "checkpoint.json"
    checkpoint = load_json(ROOT / cp_name, {})
    # Live mode ignores one-shot backfill_from_date unless explicitly left set
    live_cfg = dict(cfg)
    live_cfg.pop("backfill_from_date", None)
    records = fetch_biometric_records(live_cfg, checkpoint)
    if not records:
        logging.info("No Biometric attendance rows to sync")
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

    records = fetch_biometric_records(cfg, {}, force_from=from_date, force_to=to_date)
    if not records:
        logging.warning(
            "Backfill got 0 rows. Open Biometric API in browser for this range — "
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
    logging.info("Biometric API: %s", cfg.get("biometric_api_url") or cfg.get("biomax_api_url"))

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
