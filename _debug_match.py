import re
import pandas as pd

def norm_time(t):
    t = str(t or "").strip()
    if not t:
        return ""
    m = re.match(r"(\d{1,2}):(\d{2})(?::(\d{2}))?", t)
    if not m:
        return t
    return f"{int(m.group(1)):02d}:{m.group(2)}:{m.group(3) or '00'}"

def parse_punches(raw):
    raw = str(raw or "").strip()
    out = []
    for m in re.finditer(
        r"(\d{1,2}:\d{2}(?::\d{2})?)\s*(?:\()?[\s\-]*(in|out)(?:\))?|"
        r"\b(in|out)\b\s*[:\-]?\s*(\d{1,2}:\d{2}(?::\d{2})?)",
        raw,
        re.I,
    ):
        if m.group(1):
            t, d = m.group(1), m.group(2)
        else:
            d, t = m.group(3), m.group(4)
        out.append((norm_time(t), d.upper()))
    return out

def minute_keys(p):
    return [f"{t.split(':')[0]}:{t.split(':')[1]} {d}" for t, d in p]

xl = pd.read_excel(
    r"C:\Users\Morning\Downloads\DailyAttendance_DetailedReportForDepartment (9).xls",
    header=1,
)
row = xl[
    (xl["Employee Code"].astype(str).str.strip() == "2162")
    & (xl["AttendanceDate"] == "01-Sep-2026")
].iloc[0]
db = "09:38 (in), 11:57 (out), 12:00 (in), 14:21 (out), 14:26 (in), 14:33 (out), 14:54 (in), 17:32 (out), 17:34 (in), 18:00 (out), 18:27 (in), 18:44 (out), 18:46 (in), 18:47 (out)"
xp = parse_punches(row["Punch Records"])
dp = parse_punches(db)
print("xl n", len(xp), "db n", len(dp))
print("match", minute_keys(xp) == minute_keys(dp))
for a, b in zip(minute_keys(xp), minute_keys(dp)):
    if a != b:
        print("diff", a, b)
