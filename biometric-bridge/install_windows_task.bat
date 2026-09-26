@echo off
REM Creates a Windows Scheduled Task to keep the bridge running at logon.
cd /d "%~dp0"
set TASK_NAME=T2G_Biometric_Bridge
set PYTHON=
for %%I in (python.exe) do set PYTHON=%%~$PATH:I
if "%PYTHON%"=="" (
  echo Python not in PATH. Install Python first.
  pause
  exit /b 1
)
schtasks /Create /F /TN "%TASK_NAME%" /SC ONLOGON /RL HIGHEST /TR "\"%PYTHON%\" \"%~dp0bridge.py\""
if errorlevel 1 (
  echo Failed to create task. Run this bat as Administrator.
  pause
  exit /b 1
)
echo Task "%TASK_NAME%" created. It will start at Windows logon.
echo Start now? 
schtasks /Run /TN "%TASK_NAME%"
pause
