Snapshot Tool — install on any Windows 10 PC
============================================

1. Delete any OLD Snapshot folder.
2. Download Snapshot.zip from Workroom (header link).
3. RIGHT-CLICK the zip → Extract All → choose a NEW folder
   (example: Desktop\Snapshot). Do NOT open files from inside the zip.
4. Double-click Snapshot.bat

Snapshot.bat will:
  - Close any old hung Snapshot process
  - Start Snapshot-x64.exe on 64-bit Windows
  - Start Snapshot-x86.exe on 32-bit Windows

If Login closes the app (64-bit PC)
-----------------------------------
- Download fresh Snapshot.zip from Workroom (v1.2.1+ fixes most login crashes).
- Or try "Snapshot 32-bit" link — some 64-bit PCs run that build better.
- Send IT the log file from %LOCALAPPDATA%\.snapshot\logs\

If nothing opens
----------------
- Make sure you extracted the zip (do not run from inside WinRAR/zip)
- Run Snapshot.bat again
- Open Task Manager → end Snapshot / Snapshot-x64 / Snapshot-x86
- Check antivirus did not quarantine the .exe files
- Contact IT with a screenshot

Logs (for IT)
-------------
%LOCALAPPDATA%\.snapshot\logs\

Requirements
------------
- Windows 10 / 11 (or Windows 7 SP1+)
- Internet connection
- Workroom login (same email/password)
