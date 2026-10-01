#!/usr/bin/env python3
"""Quick checks for bridge session bucketing (no API needed)."""
import sys
from datetime import datetime
from pathlib import Path

ROOT = Path(__file__).resolve().parent / "biometric-bridge"
sys.path.insert(0, str(ROOT))

from bridge import aggregate_device_logs  # noqa: E402


def row(code: str, when: datetime, direction: str = "") -> dict:
    return {
        "EmployeeCode": code,
        "LogDate": when.strftime("%Y-%m-%d %H:%M:%S"),
        "PunchDirection": direction,
        "EmployeeName": "Test User",
    }


def test_night_shift_splits_morning_carryover():
    """Morning punches after midnight belong to prior shift-day, not calendar day."""
    logs = [
        row("2141", datetime(2026, 9, 29, 18, 34, 0), "in"),
        row("2141", datetime(2026, 9, 29, 23, 15, 0), "out"),
        row("2141", datetime(2026, 9, 30, 1, 8, 0), "in"),
        row("2141", datetime(2026, 9, 30, 3, 15, 0), "out"),
        row("2141", datetime(2026, 9, 30, 18, 34, 0), "in"),
        row("2141", datetime(2026, 9, 30, 20, 19, 0), "out"),
    ]
    records = {r["attendance_date"]: r for r in aggregate_device_logs(logs)}
    assert "29-Sep-2026" in records
    assert "30-Sep-2026" in records

    mon = records["29-Sep-2026"]["punch_records"]
    tue = records["30-Sep-2026"]["punch_records"]
    assert "01:08:00 (in)" in mon
    assert "03:15:00 (out)" in mon
    assert "01:08:00" not in tue
    assert "18:34:00 (in)" in tue
    assert "20:19:00 (out)" in tue
    assert records["30-Sep-2026"]["a_out_time"] == "20:19:00"
    print("OK night_shift_splits_morning_carryover")


def test_day_shift_stays_on_one_day():
    logs = [
        row("1001", datetime(2026, 9, 30, 9, 10, 0), "in"),
        row("1001", datetime(2026, 9, 30, 13, 5, 0), "out"),
        row("1001", datetime(2026, 9, 30, 13, 42, 0), "in"),
        row("1001", datetime(2026, 9, 30, 18, 30, 0), "out"),
    ]
    records = aggregate_device_logs(logs)
    assert len(records) == 1
    r = records[0]
    assert r["attendance_date"] == "30-Sep-2026"
    assert r["a_in_time"] == "09:10:00"
    assert r["a_out_time"] == "18:30:00"
    print("OK day_shift_stays_on_one_day")


def test_open_in_has_no_out_time():
    logs = [
        row("1002", datetime(2026, 9, 30, 9, 0, 0), "in"),
        row("1002", datetime(2026, 9, 30, 18, 42, 0), "out"),
        row("1002", datetime(2026, 9, 30, 18, 54, 0), "in"),
    ]
    records = aggregate_device_logs(logs)
    r = records[0]
    assert r["a_out_time"] == ""
    assert "18:54:00 (in)" in r["punch_records"]
    print("OK open_in_has_no_out_time")


def test_morning_user_after_midnight_carryover_in():
    """Blank direction: afternoon punch must stay IN, not phantom OUT."""
    logs = [
        row("2155", datetime(2026, 9, 30, 0, 21, 0)),
        row("2155", datetime(2026, 9, 30, 15, 21, 0)),
        row("2155", datetime(2026, 9, 30, 18, 34, 0)),
        row("2155", datetime(2026, 9, 30, 20, 19, 0)),
    ]
    records = {r["attendance_date"]: r for r in aggregate_device_logs(logs)}
    punches = records["30-Sep-2026"]["punch_records"]
    assert "15:21:00 (in)" in punches
    assert "15:21:00 (out)" not in punches
    print("OK morning_user_after_midnight_carryover_in")


def test_day_shift_blank_direction_lunch_break():
    logs = [
        row("2144", datetime(2026, 9, 30, 10, 53, 0)),
        row("2144", datetime(2026, 9, 30, 13, 31, 0)),
        row("2144", datetime(2026, 9, 30, 14, 5, 0)),
        row("2144", datetime(2026, 9, 30, 18, 30, 0)),
    ]
    records = aggregate_device_logs(logs)
    r = records[0]
    assert r["a_in_time"] == "10:53:00"
    assert r["a_out_time"] == "18:30:00"
    assert "13:31:00 (out)" in r["punch_records"]
    assert "14:05:00 (in)" in r["punch_records"]
    print("OK day_shift_blank_direction_lunch_break")


def test_morning_arrival_after_prior_evening_open_in():
    """Tushar case: Sep 29 ends with evening IN; Sep 30 ~11:00 must stay IN."""
    logs = [
        row("2144", datetime(2026, 9, 29, 20, 7, 13)),
        row("2144", datetime(2026, 9, 30, 11, 0, 0)),
        row("2144", datetime(2026, 9, 30, 13, 31, 34)),
        row("2144", datetime(2026, 9, 30, 18, 30, 0)),
    ]
    records = {r["attendance_date"]: r for r in aggregate_device_logs(logs)}
    tue = records["30-Sep-2026"]
    assert "11:00:00 (in)" in tue["punch_records"]
    assert tue["a_in_time"] == "11:00:00"
    print("OK morning_arrival_after_prior_evening_open_in")


def test_night_shift_ends_next_morning():
    logs = [
        row("2155", datetime(2026, 9, 29, 18, 34, 0)),
        row("2155", datetime(2026, 9, 30, 3, 15, 0)),
    ]
    records = {r["attendance_date"]: r for r in aggregate_device_logs(logs)}
    mon = records["29-Sep-2026"]["punch_records"]
    assert "18:34:00 (in)" in mon
    assert "03:15:00 (out)" in mon
    print("OK night_shift_ends_next_morning")


def test_night_exit_after_evening_out_and_midnight_swipe():
    """Naveen case: OUT ~23:11 then lone 00:48 exit must be OUT, not IN."""
    logs = [
        row("2141", datetime(2026, 9, 30, 15, 46, 35)),
        row("2141", datetime(2026, 9, 30, 15, 59, 50)),
        row("2141", datetime(2026, 9, 30, 16, 16, 7)),
        row("2141", datetime(2026, 9, 30, 17, 23, 39)),
        row("2141", datetime(2026, 9, 30, 17, 35, 58)),
        row("2141", datetime(2026, 9, 30, 19, 51, 35)),
        row("2141", datetime(2026, 9, 30, 19, 55, 14)),
        row("2141", datetime(2026, 9, 30, 21, 25, 39)),
        row("2141", datetime(2026, 9, 30, 21, 50, 45)),
        row("2141", datetime(2026, 9, 30, 23, 11, 33)),
        row("2141", datetime(2026, 10, 1, 0, 48, 23)),
    ]
    records = aggregate_device_logs(logs)
    r = records[0]
    assert r["attendance_date"] == "30-Sep-2026"
    assert "00:48:23 (out)" in r["punch_records"]
    assert "00:48:23 (in)" not in r["punch_records"]
    assert r["a_out_time"] == "00:48:23"
    print("OK night_exit_after_evening_out_and_midnight_swipe")


def test_short_night_break_return_stays_in():
    """OUT 23:11 then IN 00:30 within 90 min = break return, not final exit."""
    logs = [
        row("2141", datetime(2026, 9, 30, 18, 0, 0)),
        row("2141", datetime(2026, 9, 30, 23, 11, 0)),
        row("2141", datetime(2026, 10, 1, 0, 30, 0)),
        row("2141", datetime(2026, 10, 1, 3, 0, 0)),
    ]
    records = aggregate_device_logs(logs)
    r = records[0]
    assert "00:30:00 (in)" in r["punch_records"]
    assert "03:00:00 (out)" in r["punch_records"]
    assert r["a_out_time"] == "03:00:00"
    print("OK short_night_break_return_stays_in")


if __name__ == "__main__":
    test_night_shift_splits_morning_carryover()
    test_day_shift_stays_on_one_day()
    test_open_in_has_no_out_time()
    test_morning_user_after_midnight_carryover_in()
    test_day_shift_blank_direction_lunch_break()
    test_morning_arrival_after_prior_evening_open_in()
    test_night_shift_ends_next_morning()
    test_night_exit_after_evening_out_and_midnight_swipe()
    test_short_night_break_return_stays_in()
    print("All bridge session tests passed.")
