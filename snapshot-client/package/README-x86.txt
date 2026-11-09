Snapshot Tool — 32-bit Windows ONLY package
============================================

Use this package if:
  - Windows shows "32-bit operating system" in System Type, OR
  - IT asked you to use the 32-bit build, OR
  - The main Snapshot download crashes when you click Login (even on 64-bit Windows).

Some 64-bit PCs run the 32-bit build more reliably — try this zip before calling IT.

Most office PCs are 64-bit — use the main "Snapshot" link on Workroom instead.

Install steps
-------------
1. Close Snapshot in Task Manager if it is stuck.
2. Delete any OLD Snapshot folder on Desktop/Downloads.
3. Download Snapshot-x86.zip from Workroom (32-bit link).
4. RIGHT-CLICK the zip → Extract All → NEW folder (example: Desktop\Snapshot32).
5. Double-click Snapshot-x86.bat  (NOT Snapshot-x64.exe).

Login
-----
- Same Workroom email and password.
- You must be checked in on Workroom or biometric first.

If it still crashes on Login
----------------------------
1. Send IT the newest file from:
   %LOCALAPPDATA%\.snapshot\logs\
2. In Command Prompt run:  systeminfo | findstr /C:"System Type"
   and send that line to IT.

Requirements
------------
- 32-bit Windows 10 (or Windows 7 SP1+)
- Internet connection
