@echo off
cd /d "%~dp0"
where python >nul 2>&1
if errorlevel 1 (
  echo Python not found. Install Python 3.11+ and tick "Add to PATH".
  pause
  exit /b 1
)
python -c "import requests" 2>nul
if errorlevel 1 (
  echo Installing requests...
  python -m pip install requests
)
echo Starting Biometric bridge...
python bridge.py
pause
