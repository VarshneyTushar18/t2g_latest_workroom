import os
import sys

APP_NAME = "SnapshotTool"
APP_VERSION = "1.2.1.0"

if getattr(sys, "frozen", False):
    EXECUTABLE_PATH = os.path.realpath(sys.executable)
else:
    EXECUTABLE_PATH = os.path.realpath(sys.argv[0])

SCOPES = ["https://www.googleapis.com/auth/drive"]
TOKEN_JSON_URL = "https://t2gworkroom.com/token.json"

API_STAFF_URL = "https://t2gworkroom.com/api/api/staff"
API_INTERVAL_URL = "https://t2gworkroom.com/api/api/interval/"
API_DRIVE_URL = "https://t2gworkroom.com/api/api/Drive"
FORGOT_PASSWORD_URL = "https://t2gworkroom.com/admin/authentication/forgot_password"

HTTP_HEADERS = {
    "User-Agent": (
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
        "(KHTML, like Gecko) Chrome/127.0.0.0 Safari/537.36 Edg/127.0.0.0"
    ),
    "Accept": "application/json, text/plain, */*",
}

MIN_WINDOWS_MAJOR = 6
MIN_WINDOWS_MINOR = 1
