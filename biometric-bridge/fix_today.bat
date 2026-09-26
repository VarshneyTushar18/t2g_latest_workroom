@echo off
title T2G Bridge — FIX TODAY
cd /d "%~dp0"
color 0A
echo.
echo ============================================
echo   T2G Biometric Bridge — FIX TODAY
echo ============================================
echo.
echo Step 1: DNS / internet check...
echo Open browser and confirm this loads:
echo   https://t2gworkroom.com
echo.
pause

echo.
echo Step 2: Checking Python...
where python >nul 2>&1
if errorlevel 1 (
  echo ERROR: Python not found. Install Python 3.11+ with "Add to PATH".
  pause
  exit /b 1
)
python -c "import requests" 2>nul
if errorlevel 1 (
  echo Installing requests...
  python -m pip install requests
)

echo.
echo Step 3: Testing Biometric + pushing TODAY to Workroom...
echo (Leave this window open — read the messages)
echo.
python bridge.py --fix-today
echo.
echo Step 4: Starting LIVE bridge (every 2 min)...
echo Close the OLD bridge window first if it is still open.
echo.
pause
echo Starting live bridge now. Keep this window OPEN.
python bridge.py
pause
