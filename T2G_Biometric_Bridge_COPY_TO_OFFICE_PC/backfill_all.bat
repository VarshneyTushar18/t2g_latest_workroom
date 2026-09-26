@echo off
REM One-shot FULL backfill: pull Biometric from start date → TODAY (weekly chunks) → Workroom
REM Edit start date below if you need older history.
cd /d "%~dp0"
python -c "import requests" 2>nul || python -m pip install requests
echo.
echo === BACKFILL all punches into live Workroom ===
echo Default start: 2025-07-01  (change by: backfill_all.bat 2026-01-01)
echo Keep this window open until it says Done.
echo.
if "%~1"=="" (
  python bridge.py --backfill 2025-07-01
) else (
  python bridge.py --backfill %~1
)
echo.
echo Check logs\bridge.log and https://t2gworkroom.com/admin/biometric
pause
