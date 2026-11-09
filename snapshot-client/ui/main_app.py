import io
import json
import logging
import os
import socket
import time
from pathlib import Path

import requests
from google.auth.transport.requests import Request
from google.oauth2.credentials import Credentials
from googleapiclient.discovery import build
from googleapiclient.errors import HttpError
from googleapiclient.http import MediaIoBaseUpload
from PIL import ImageGrab
from PyQt5.QtCore import Qt, QTimer, pyqtSignal
from PyQt5.QtWidgets import QApplication, QStackedWidget

from ui.home import HomePage
from ui.login import LoginPage
from utils.constants import (
    API_DRIVE_URL,
    API_INTERVAL_URL,
    API_STAFF_URL,
    HTTP_HEADERS,
    SCOPES,
    TOKEN_JSON_URL,
)
from utils.internet_notification_popup import NotificationPopup


class MainApp(QStackedWidget):
    connection_status_signal = pyqtSignal(str)
    hide_connection_signal = pyqtSignal()
    internet_popup_signal = pyqtSignal(str)

    def __init__(self):
        super().__init__()
        self.setWindowFlags(
            Qt.Window
            | Qt.WindowTitleHint
            | Qt.WindowSystemMenuHint
            | Qt.WindowMinimizeButtonHint
            | Qt.WindowCloseButtonHint
        )
        self.setFixedSize(420, 520)

        self.FLAG = False
        self.staff_id = None
        self.current_username = ""
        self.current_password = ""
        self.drive_service = None
        self.tracking_alert = None

        self.login_page = LoginPage()
        self.home_page = HomePage(self)
        self.addWidget(self.login_page)
        self.addWidget(self.home_page)
        self.setCurrentIndex(0)

        self.connection_status_signal.connect(self.home_page.show_connection_status)
        self.hide_connection_signal.connect(self.home_page.hide_connection_status)
        self.internet_popup_signal.connect(self._show_internet_popup)

        # Defer Drive auth so the login window appears immediately.
        # Doing this in __init__ blocked the UI and looked like a crash.
        QTimer.singleShot(0, self.authenticate_personal_drive)

    def _show_internet_popup(self, message):
        popup = NotificationPopup(message, parent=self)
        popup.show()

    def wait_for_drive_service(self, timeout_sec=60):
        """Block until Drive API is ready (login runs on UI thread)."""
        deadline = time.time() + timeout_sec
        while time.time() < deadline:
            if self.drive_service:
                return True
            self.authenticate_personal_drive()
            if self.drive_service:
                return True
            app = QApplication.instance()
            if app:
                app.processEvents()
            time.sleep(0.5)
        return False

    def check_internet_connection(self, timeout=5):
        test_urls = ["https://www.google.com", "https://t2gworkroom.com"]
        for url in test_urls:
            try:
                response = requests.head(url, timeout=timeout)
                if response.status_code < 500:
                    return True
            except requests.RequestException:
                pass
        try:
            socket.gethostbyname("google.com")
            return True
        except socket.error:
            return False

    def refresh_drive_service(self):
        self.authenticate_personal_drive()

    def authenticate_personal_drive(self):
        hidden_token_dir = Path(os.getenv("LOCALAPPDATA", "")) / ".snapshot"
        token_path = hidden_token_dir / "token.json"
        hidden_token_dir.mkdir(parents=True, exist_ok=True)

        creds = None
        try:
            if not token_path.exists():
                print("Downloading token.json from server...")
                self.download_token_from_server(TOKEN_JSON_URL, token_path)

            creds = Credentials.from_authorized_user_file(str(token_path), SCOPES)
            if creds and creds.valid:
                self.drive_service = build("drive", "v3", credentials=creds)
                return

            if creds and creds.expired and creds.refresh_token:
                print("Refreshing token...")
                creds.refresh(Request())
                token_path.write_text(creds.to_json(), encoding="utf-8")
                self.drive_service = build("drive", "v3", credentials=creds)
                return

            print("Token invalid and no refresh token available.")
            self.drive_service = None
        except Exception as exc:
            print(f"Auth error: {exc}")
            self.drive_service = None

    def download_token_from_server(self, url, save_path):
        headers = dict(HTTP_HEADERS)
        response = requests.get(url, headers=headers, timeout=30)
        if response.status_code != 200:
            raise Exception(f"HTTP {response.status_code} while downloading token.json")
        save_path.write_text(response.text, encoding="utf-8")

    def create_folder_on_drive(self, folder_name, parent_id=None):
        file_metadata = {
            "name": folder_name,
            "mimeType": "application/vnd.google-apps.folder",
        }
        if parent_id:
            file_metadata["parents"] = [parent_id]

        folder = (
            self.drive_service.files()
            .create(body=file_metadata, fields="id")
            .execute()
        )
        return folder.get("id")

    def get_folder_id_by_name(self, service, folder_name, parent_id=None):
        query = f"name = '{folder_name}' and mimeType = 'application/vnd.google-apps.folder'"
        if parent_id:
            query += f" and '{parent_id}' in parents"

        try:
            response = (
                service.files()
                .list(q=query, spaces="drive", fields="files(id, name)")
                .execute()
            )
            folders = response.get("files", [])
            if folders:
                return folders[0]["id"]
        except HttpError as error:
            print(error)
        return None

    def create_or_get_today_folder(self, service, username_folder_id):
        today_str = time.strftime("%Y-%m-%d")
        today_folder_id = self.get_folder_id_by_name(service, today_str, username_folder_id)
        if today_folder_id:
            return today_folder_id
        return self.create_folder_on_drive(today_str, username_folder_id)

    def upload_file_to_drive(self, username, folder_id, image_bytes):
        date_str = time.strftime("%Y-%m-%d")
        timestamp = time.strftime("%Y%m%d_%H%M%S")
        file_name = f"screenshot_{timestamp}.png"

        file_metadata = {
            "name": file_name,
            "parents": [folder_id],
        }
        media = MediaIoBaseUpload(image_bytes, mimetype="image/png", resumable=False)
        uploaded = (
            self.drive_service.files()
            .create(body=file_metadata, media_body=media, fields="id, webViewLink")
            .execute()
        )
        return uploaded

    def take_screenshot(self):
        try:
            screenshot = ImageGrab.grab(all_screens=True)
        except Exception:
            screenshot = ImageGrab.grab()
        image_bytes = io.BytesIO()
        screenshot.save(image_bytes, format="PNG")
        image_bytes.seek(0)
        return image_bytes

    def push_data(self, staff_id, file_id, directory_id, status=0, parent_directory_id=None):
        payload = {
            "staff_id": staff_id,
            "file_id": file_id,
            "directory_id": directory_id,
            "status": status,
            "parent_directory_id": parent_directory_id or "",
        }
        headers = dict(HTTP_HEADERS)
        headers["Content-Type"] = "application/x-www-form-urlencoded"
        response = requests.post(API_DRIVE_URL, data=payload, headers=headers, timeout=30)
        print(response.text)
        return response

    def start_screenshot_capture(self, username, parent_id, today_folder_id, password):
        checked_in = True
        while self.FLAG and checked_in:
            try:
                if not self.check_internet_connection():
                    self.connection_status_signal.emit("No Internet Connection")
                    self.internet_popup_signal.emit("No Internet Connection")
                    time.sleep(30)
                    continue

                self.hide_connection_signal.emit()

                if not self.drive_service:
                    self.refresh_drive_service()
                    time.sleep(5)
                    continue

                image_bytes = self.take_screenshot()
                uploaded = self.upload_file_to_drive(username, today_folder_id, image_bytes)
                file_id = uploaded.get("id")
                web_view_link = uploaded.get("webViewLink")

                staff_id, interval_seconds = self.get_timings(self.current_username or username)
                if staff_id:
                    self.staff_id = staff_id

                self.push_data(
                    staff_id=self.staff_id,
                    file_id=file_id,
                    directory_id=today_folder_id,
                    status=0,
                    parent_directory_id=parent_id,
                )

                login_response = self.loginApi(self.current_username, password)
                checked_in = bool(login_response.get("success") and login_response.get("check-in"))
                self.FLAG = checked_in
                if not checked_in:
                    QTimer.singleShot(0, self.home_page.logout)
                    break

                sleep_seconds = max(60, int(interval_seconds or (10 * 60)))
                time.sleep(sleep_seconds)
            except Exception as exc:
                print(f"Screenshot loop error: {exc}")
                self.refresh_drive_service()
                time.sleep(15)

    def get_timings(self, username):
        payload = {"email": username}
        headers = dict(HTTP_HEADERS)
        headers["Content-Type"] = "application/x-www-form-urlencoded"
        response = requests.post(API_INTERVAL_URL, data=payload, headers=headers, timeout=30)
        response_json = response.json()
        data_list = response_json.get("data") or []
        if not data_list:
            # Default: every 10 minutes
            return self.staff_id, 10 * 60

        row = data_list[0]
        staff_id = row.get("staffid")
        interval_text = str(row.get("time_interval") or "00:10:00")
        parts = list(map(int, interval_text.split(":")))
        while len(parts) < 3:
            parts.insert(0, 0)
        hours, minutes, seconds = parts[-3:]
        total_seconds = hours * 3600 + minutes * 60 + seconds
        return staff_id, total_seconds

    def read_json_values(self, file_path):
        try:
            with open(file_path, "r", encoding="utf-8") as handle:
                return json.load(handle)
        except FileNotFoundError:
            return {}

    def loginApi(self, username, password):
        payload = {"email": username, "password": password}
        headers = dict(HTTP_HEADERS)
        headers["Content-Type"] = "application/x-www-form-urlencoded"

        try:
            self.read_json_values("cred.json")
            response = requests.post(API_STAFF_URL, data=payload, headers=headers, timeout=30)
            response_json = response.json()
            print(response_json)

            if response_json.get("success") and response_json.get("check-in"):
                if self.staff_id is None:
                    _, _ = self.get_timings(username)
            return response_json
        except Exception as exc:
            print(exc)
            return {"success": 0, "check-in": False, "message": str(exc)}

    def ensure_folder_exists_on_drive(self, username):
        if not self.drive_service:
            raise RuntimeError("Google Drive is not connected yet. Check internet and try again.")
        user_folder_id = self.get_folder_id_by_name(self.drive_service, username)
        if not user_folder_id:
            user_folder_id = self.create_folder_on_drive(username)

        today_folder_id = self.create_or_get_today_folder(self.drive_service, user_folder_id)
        return user_folder_id, today_folder_id
