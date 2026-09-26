@echo off
REM One-shot FULL backfill: pull Biometric from start date → TODAY (weekly chunks) → Workroom
cd /d "%~dp0"
python -c "import requests" 2>nul || python -m pip install requests
echo.
echo === BACKFILL all punches into live Workroom ===
if "%~1"=="" (
  python bridge.py --backfill 2025-07-01
) else (
  python bridge.py --backfill %~1
)
echo.
pause
