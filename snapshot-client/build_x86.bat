@echo off
setlocal EnableExtensions
cd /d "%~dp0"

set "PY32=C:\Python311-32\python.exe"
if not exist "%PY32%" (
    where py >nul 2>&1 && (
        for /f "delims=" %%I in ('py -0p 2^>nul ^| findstr /I "32-bit \\-32"') do set "PY32=%%I"
    )
)
if not exist "%PY32%" (
    echo 32-bit Python 3.11 not found.
    echo Install to C:\Python311-32 or: winget install Python.Python.3.11 --architecture x86
    exit /b 1
)

echo Building Snapshot x86 with: %PY32%
"%PY32%" -c "import struct; assert struct.calcsize('P')*8==32, 'Not 32-bit Python'"
if errorlevel 1 exit /b 1

"%PY32%" -m pip install -r requirements.txt
"%PY32%" -m PyInstaller snapshot.spec --noconfirm --clean

if not exist "dist\Snapshot.exe" (
    echo Build failed.
    exit /b 1
)

if not exist "package" mkdir "package"
copy /Y "dist\Snapshot.exe" "package\Snapshot-x86.exe" >nul
echo Built package\Snapshot-x86.exe
