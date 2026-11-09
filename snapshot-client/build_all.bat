@echo off
setlocal EnableExtensions
cd /d "%~dp0"

echo === Build Snapshot x64 ===
call build_x64.bat
if errorlevel 1 exit /b 1

echo.
echo === Build Snapshot x86 (needs Python 3.11 32-bit) ===
call build_x86.bat
if errorlevel 1 (
    echo WARNING: x86 build failed. Zip will be x64-only until 32-bit Python is installed.
    echo Install with: winget install Python.Python.3.11 --architecture x86
)

if not exist "package" mkdir "package"
if exist "package\Snapshot-x86.exe" (
    powershell -NoProfile -Command "Compress-Archive -Path 'package\Snapshot.bat','package\README.txt','package\focus-snapshot.ps1','package\Snapshot-x64.exe','package\Snapshot-x86.exe' -DestinationPath '..\Snapshot.zip' -Force"
) else (
    powershell -NoProfile -Command "Compress-Archive -Path 'package\Snapshot.bat','package\README.txt','package\focus-snapshot.ps1','package\Snapshot-x64.exe' -DestinationPath '..\Snapshot.zip' -Force"
)

if exist "package\Snapshot-x86.exe" (
    call build_x86_zip.bat
)

echo.
echo Final packages:
dir "..\Snapshot.zip" 2>nul
dir "..\Snapshot-x86.zip" 2>nul
