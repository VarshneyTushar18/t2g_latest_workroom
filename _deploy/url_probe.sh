#!/bin/bash
BASE=https://t2gworkroom.com
paths=(
  admin/authentication
  admin/dashboard
  admin/Emt/getEmployeeId
  admin/Emt/monitoring
  admin/Emt/report
  admin/timesheets/my_attendance
  admin/holiday/manage_wfh
  admin/company_policies
)
for p in "${paths[@]}"; do
  body=$(mktemp)
  code=$(curl -s -o "$body" -w '%{http_code}' "$BASE/$p")
  if grep -qiE 'fatal error|parse error|Composer detected|Uncaught|Exception:' "$body" 2>/dev/null; then
    err=BODY-ERROR
  else
    err=OK
  fi
  echo "$code $p $err"
  rm -f "$body"
done
