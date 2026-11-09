@echo off
setlocal EnableExtensions
cd /d "%~dp0"

REM One launcher for all PCs: picks 64-bit or 32-bit Snapshot automatically.
REM Always unzip first, then run THIS file — do not run from inside the zip.

REM Clear hung old instances that block a new window from opening
taskkill /F /IM Snapshot.exe /T >nul 2>&1
taskkill /F /IM Snapshot-x64.exe /T >nul 2>&1
taskkill /F /IM Snapshot-x86.exe /T >nul 2>&1
timeout /t 1 /nobreak >nul

set "ARCH=%PROCESSOR_ARCHITECTURE%"
if /I "%PROCESSOR_ARCHITEW6432%"=="AMD64" set "ARCH=AMD64"
if /I "%PROCESSOR_ARCHITEW6432%"=="ARM64" set "ARCH=ARM64"

if /I "%ARCH%"=="AMD64" goto run64
if /I "%ARCH%"=="ARM64" goto run64
if /I "%ARCH%"=="x86" goto run32

echo Snapshot Tool is not compatible with this Windows architecture: %ARCH%
echo Open a Command Prompt and run:  systeminfo ^| findstr /C:"System Type"
echo Then contact IT with that line.
pause
exit /b 1

:run64
if exist "%~dp0Snapshot-x64.exe" (
    call :open "%~dp0Snapshot-x64.exe"
    exit /b 0
)
if exist "%~dp0Snapshot.exe" (
    call :open "%~dp0Snapshot.exe"
    exit /b 0
)
echo Missing Snapshot-x64.exe in this folder.
echo Re-download Snapshot.zip from Workroom, unzip to a new folder, then run Snapshot.bat.
pause
exit /b 1

:run32
if exist "%~dp0Snapshot-x86.exe" (
    call :open "%~dp0Snapshot-x86.exe"
    exit /b 0
)
if exist "%~dp0Snapshot.exe" (
    call :open "%~dp0Snapshot.exe"
    exit /b 0
)
echo This PC is 32-bit Windows and Snapshot-x86.exe is missing.
echo Re-download the latest Snapshot.zip from Workroom (it includes both 64-bit and 32-bit).
pause
exit /b 1

:open
REM Start the app, then pull its window in front. Windows otherwise leaves it on the taskbar.
start "" "%~1"
if exist "%~dp0focus-snapshot.ps1" (
    powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0focus-snapshot.ps1"
)
exit /b 0
