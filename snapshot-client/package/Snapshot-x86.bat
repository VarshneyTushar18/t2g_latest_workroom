@echo off
setlocal EnableExtensions
cd /d "%~dp0"

REM 32-bit Snapshot only — for old 32-bit Windows or when the main Snapshot.zip crashes.
REM Extract the zip to a NEW folder, then run THIS file (not from inside the zip).

taskkill /F /IM Snapshot.exe /T >nul 2>&1
taskkill /F /IM Snapshot-x86.exe /T >nul 2>&1
taskkill /F /IM Snapshot-x64.exe /T >nul 2>&1
timeout /t 1 /nobreak >nul

if not exist "%~dp0Snapshot-x86.exe" (
    echo Missing Snapshot-x86.exe in this folder.
    echo Download Snapshot-x86.zip from Workroom and extract fully.
    pause
    exit /b 1
)

start "" "%~dp0Snapshot-x86.exe"
if exist "%~dp0focus-snapshot.ps1" (
    powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0focus-snapshot.ps1"
)
exit /b 0
