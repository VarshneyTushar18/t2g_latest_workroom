@echo off
setlocal EnableExtensions
cd /d "%~dp0"

if not exist "package\Snapshot-x86.exe" (
    echo package\Snapshot-x86.exe missing. Run build_x86.bat first.
    exit /b 1
)

powershell -NoProfile -Command ^
  "Compress-Archive -Path 'package\Snapshot-x86.bat','package\README-x86.txt','package\focus-snapshot.ps1','package\Snapshot-x86.exe' -DestinationPath '..\Snapshot-x86.zip' -Force"

echo Created ..\Snapshot-x86.zip
dir "..\Snapshot-x86.zip"
