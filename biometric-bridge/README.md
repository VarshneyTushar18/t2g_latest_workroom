# T2G Biometric Live Sync Bridge — GO LIVE

## Status

| Piece | Status |
|-------|--------|
| Workroom sync API | **LIVE** (`/biometric_sync/health` + `/attendance`) |
| CSRF allow for sync | **LIVE** |
| Bridge code + config | Ready in this folder |
| Biometric API `192.168.1.9:82` | Must run from **office LAN PC** |

## On the office PC (must open 192.168.1.9)

1. Copy this whole `biometric-bridge` folder to the PC (USB / shared drive).
2. Confirm browser works:
   ```
   http://192.168.1.9:82/api/v2/WebAPI/GetDeviceLogs?APIKey=490813092603&FromDate=2026-09-01&ToDate=2026-09-03
   ```
3. Install Python 3.11+ (tick **Add to PATH**).
4. Double-click **`test_once.bat`**  
   - Fetches Biometric once and pushes to Workroom  
   - Check `logs/bridge.log`
5. If OK, double-click **`start_bridge.bat`** (keeps running every 2 minutes)  
   **or** run **`install_windows_task.bat` as Administrator** for auto-start at login.

## Files

- `bridge.py` — sync loop
- `config.json` — already filled with your Biometric URL/key + Workroom token
- `test_once.bat` — one-shot test
- `start_bridge.bat` — continuous
- `install_windows_task.bat` — Windows Task Scheduler

## Mapping note

`EmployeeCode` from Biometric must match Workroom staff Emp ID (`staff_identifi`).
