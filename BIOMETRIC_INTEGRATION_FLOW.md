# Biometric Integration Flow (Step-by-Step)

This document explains how to connect a real biometric system to Workroom safely.

## 1) Current State (What already exists)

Workroom already has biometric storage and UI:

- Import/view controller: `application/controllers/admin/Biometric.php`
- Storage model: `application/models/Biometric_model.php`
- UI page: `application/views/admin/biometric/attendance_import_view.php`
- Target table: `tblbiometric_report`

Today, data is synced via the **Biomax bridge API** (`/biometric_sync/attendance`).  
Manual Excel import is **disabled**. Target sync interval: **2 minutes** (GreytHR-style systems often use 15–30 minutes).

---

## 2) Recommended Architecture

Use a small **Bridge Service** on the biometric computer:

1. Pull incremental data from biometric source (API/DB/file).
2. Transform into Workroom format.
3. Push to Workroom secure endpoint.
4. Save checkpoint locally.
5. Repeat every **2 minutes** (Task Scheduler or continuous Python bridge loop).

Why this is best:
- No direct public DB exposure.
- Easy to monitor/retry.
- Supports API/DB/file vendors.

---

## 3) Integration Modes (choose one)

### Mode A: Vendor API (best)
- Biometric software/device provides API.
- Bridge calls API for new punches.

### Mode B: Vendor Local DB
- Biometric software stores data in local SQL.
- Bridge reads new rows from source DB.

### Mode C: Scheduled File Export
- Vendor only exports CSV/XLSX.
- Bridge reads file drop folder and imports new rows.

---

## 4) Field Mapping (must be correct)

Minimum required mapping to Workroom:

- `employee_code` -> must match `tblstaff.staff_identifi`
- `attendance_date` -> expected format used now: `d-M-Y` (e.g. `28-Jun-2025`)
- `a_in_time`
- `a_out_time`
- `punch_records` (example: `09:10 (in), 13:05 (out), 13:42 (in), ...`)
- optional: status, shift, durations, remarks

If `employee_code` does not match staff identifier, record links will fail.

---

## 5) Security Plan

Create a secure ingestion endpoint in Workroom (recommended):

- URL example: `/admin/biometric/sync_api`
- Controls:
  - static token or HMAC signature
  - IP allowlist (biometric PC public IP/VPN IP)
  - rate limit
  - request size limit
  - audit logs per request

Do **not** expose MySQL publicly unless absolutely necessary.

---

## 6) Workroom Changes Needed

1. Add API endpoint in `Biometric` controller:
   - Accept JSON array of attendance rows.
   - Validate auth token/IP.
   - Validate required fields.
   - Call `Biometric_model::insert_attendance_bulk()` (or a JSON-ready upsert variant).

2. Improve dedupe key:
   - Current logic dedupes by `employee_code + attendance_date`.
   - If you want per-punch updates, consider extended key strategy.

3. Add sync logs table (recommended):
   - source
   - from_checkpoint / to_checkpoint
   - rows_received
   - rows_inserted
   - rows_updated
   - errors
   - created_at

---

## 7) Biometric Computer Setup (Windows)

1. Install Python 3.11+.
2. Create folder, e.g. `C:\workroom-biometric-bridge`.
3. Add:
   - `bridge.py`
   - `config.json` (credentials/endpoints)
   - `checkpoint.json`
   - `logs\bridge.log`
4. Test run manually:
   - Read small dataset.
   - Push to staging/live endpoint.
5. Add Windows Task Scheduler job:
   - Every 10 minutes
   - Run whether user logged in or not
   - Retry on failure

---

## 8) Checkpoint Strategy

Use checkpoint to avoid duplicate processing:

- API mode: use `last_event_id` or `last_punch_time`
- DB mode: use max source `id` or timestamp
- File mode: use file name + row hash

Update checkpoint **only after successful push**.

---

## 9) End-to-End Data Flow

1. Scheduler triggers bridge.
2. Bridge reads `checkpoint.json`.
3. Bridge fetches new records from source.
4. Bridge maps fields to Workroom schema.
5. Bridge sends batched payload to Workroom endpoint.
6. Workroom validates + upserts into `tblbiometric_report`.
7. Bridge writes updated checkpoint and logs summary.
8. Admin sees updated rows in Biometric UI.

---

## 10) Rollout Plan

### Phase 1: Dry Run
- Run bridge against test endpoint or test DB.
- Verify employee mapping and date formats.

### Phase 2: Pilot
- Enable one department for 3-5 days.
- Compare source attendance vs Workroom reports daily.

### Phase 3: Full
- Enable all departments.
- Enable alerting for sync failures.

---

## 11) Validation Checklist

- [ ] Employee code mapping verified (`staff_identifi`)
- [ ] Attendance date format accepted
- [ ] Duplicate behavior tested
- [ ] In/out and punch records show correctly in UI
- [ ] Scheduler runs every 10 min
- [ ] Checkpoint advances correctly
- [ ] Logs retained and readable
- [ ] Failure alert mechanism enabled

---

## 12) Common Failure Cases

- Employee code mismatch -> no staff join in reports
- Wrong date format -> skipped/invalid rows
- Timezone mismatch -> wrong day assignment
- Bridge crash before checkpoint write -> repeated sync attempts
- Endpoint auth failure -> data never reaches Workroom

---

## 13) What to collect from vendor/IT now

- Vendor name/model
- API docs OR source DB schema
- Credential method
- Timezone and shift rules
- Whether punches are mutable/backfilled
- Expected daily volume

---

## 14) Suggested Next Implementation Task

Implement secure endpoint + Python bridge skeleton first, then run 1-day pilot.

