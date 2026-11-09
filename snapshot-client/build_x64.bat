@echo off
setlocal EnableExtensions
cd /d "%~dp0"

echo Building Snapshot x64 (Windows 7 SP1+ on 64-bit PCs)...
python -m pip install -r requirements.txt
python -m PyInstaller snapshot.spec --noconfirm --clean

if not exist "dist\Snapshot.exe" (
    echo Build failed.
    exit /b 1
)

if not exist "package" mkdir "package"
copy /Y "dist\Snapshot.exe" "package\Snapshot-x64.exe" >nul

if exist "package\Snapshot-x86.exe" (
    powershell -NoProfile -Command "Compress-Archive -Path 'package\Snapshot.bat','package\README.txt','package\focus-snapshot.ps1','package\Snapshot-x64.exe','package\Snapshot-x86.exe' -DestinationPath '..\Snapshot.zip' -Force"
) else (
    powershell -NoProfile -Command "Compress-Archive -Path 'package\Snapshot.bat','package\README.txt','package\focus-snapshot.ps1','package\Snapshot-x64.exe' -DestinationPath '..\Snapshot.zip' -Force"
)

echo.
echo Built:
echo   snapshot-client\dist\Snapshot.exe
echo   snapshot-client\package\Snapshot-x64.exe
echo   Snapshot.zip
echo.
echo For 32-bit PCs, run build_x86.bat on a machine with 32-bit Python 3.11.
