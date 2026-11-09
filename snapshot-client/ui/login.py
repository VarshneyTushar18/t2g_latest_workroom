import logging
import threading

from PyQt5.QtCore import Qt, QUrl
from PyQt5.QtWidgets import QApplication
from PyQt5.QtGui import QDesktopServices, QFont
from PyQt5.QtWidgets import (
    QCheckBox,
    QFrame,
    QLabel,
    QLineEdit,
    QMessageBox,
    QPushButton,
    QVBoxLayout,
    QWidget,
)

from utils.constants import FORGOT_PASSWORD_URL
from utils.credentials import clear_credentials, load_credentials, save_credentials


class LoginPage(QWidget):
    def __init__(self):
        super().__init__()
        self.setWindowTitle("Snapshot Tool - Login")
        self.setFixedSize(420, 520)
        self.setStyleSheet(
            """
            QWidget {
                background-color: #F9FAFB;
            }
            """
        )

        layout = QVBoxLayout(self)
        layout.setContentsMargins(24, 24, 24, 24)

        login_box = QFrame()
        login_box.setFixedSize(360, 430)
        login_box.setFrameShape(QFrame.NoFrame)
        login_box.setStyleSheet(
            """
            QFrame {
                background-color: white;
                border-radius: 12px;
                padding: 20px;
            }
            """
        )

        main_layout = QVBoxLayout(login_box)
        main_layout.setSpacing(12)

        user_label = QLabel("Email Address:")
        user_label.setFont(QFont("Arial", 11, QFont.Medium))
        user_label.setStyleSheet("color: #4B5563; margin: 0; padding: 0;")

        self.user_input = QLineEdit()
        self.user_input.setPlaceholderText("Enter your email")
        self.user_input.setStyleSheet(
            """
            QLineEdit {
                font-size: 14px;
                border: 1px solid #E5E7EB;
                border-radius: 8px;
                padding: 12px 15px;
                background-color: #FFFFFF;
                color: #111827;
                margin: 0;
            }
            QLineEdit:focus {
                border-color: #3B82F6;
                background-color: #FFFFFF;
            }
            """
        )

        pass_label = QLabel("Password:")
        pass_label.setFont(QFont("Arial", 11, QFont.Medium))
        pass_label.setStyleSheet("color: #4B5563; margin: 0; padding: 0;")

        self.pass_input = QLineEdit()
        self.pass_input.setEchoMode(QLineEdit.Password)
        self.pass_input.setPlaceholderText("Enter your password")
        self.pass_input.setStyleSheet(self.user_input.styleSheet())

        self.remember_me = QCheckBox("Remember Me")
        self.remember_me.setStyleSheet(
            """
            QCheckBox {
                color: #4B5563;
                spacing: 8px;
                background: transparent;
                padding: 0;
                margin: 0;
            }
            QCheckBox::indicator {
                width: 18px;
                height: 18px;
                border-radius: 4px;
            }
            QCheckBox::indicator:checked {
                background-color: #3B82F6;
                border: 1px solid #2563EB;
            }
            QCheckBox::indicator:unchecked {
                background-color: #F3F4F6;
                border: 1px solid #D1D5DB;
            }
            """
        )

        self.login_button = QPushButton("Login")
        self.login_button.setStyleSheet(
            """
            QPushButton {
                background-color: #3B82F6;
                color: white;
                border-radius: 8px;
                padding: 12px;
                margin-top: 10px;
            }
            QPushButton:hover {
                background-color: #2563EB;
            }
            QPushButton:pressed {
                background-color: #1D4ED8;
            }
            """
        )
        self.login_button.clicked.connect(self.validate_credentials)

        forgot_label = QLabel(
            f'<a href="{FORGOT_PASSWORD_URL}" style="text-decoration:none; color: #3B82F6;">Forgot Password?</a>'
        )
        forgot_label.setOpenExternalLinks(True)
        forgot_label.setAlignment(Qt.AlignCenter)
        forgot_label.setStyleSheet("color: #A1A1AA; background-color: transparent;")

        main_layout.addWidget(user_label)
        main_layout.addWidget(self.user_input)
        main_layout.addWidget(pass_label)
        main_layout.addWidget(self.pass_input)
        main_layout.addWidget(self.remember_me)
        main_layout.addWidget(self.login_button)
        main_layout.addWidget(forgot_label)

        layout.addWidget(login_box, alignment=Qt.AlignCenter)
        self.load_saved_credentials()

    def load_saved_credentials(self):
        credentials = load_credentials()
        self.user_input.setText(credentials.get("username", ""))
        self.pass_input.setText(credentials.get("password", ""))
        if credentials.get("username") or credentials.get("password"):
            self.remember_me.setChecked(True)

    def validate_credentials(self):
        username = self.user_input.text().strip()
        password = self.pass_input.text()
        remember_me = self.remember_me.isChecked()
        main_app = self.parentWidget()

        if not username or not password:
            self._show_message(
                QMessageBox.Warning,
                "Login Failed",
                "Login unsuccessful",
                "Enter your Workroom email and password.",
            )
            return

        self.login_button.setEnabled(False)
        self.login_button.setText("Please wait...")
        app = QApplication.instance()
        if app:
            app.processEvents()

        try:
            response = main_app.loginApi(username, password)
            success = response.get("success")
            checked_in = response.get("check-in")
            server_message = (response.get("message") or "").strip()

            if success and checked_in:
                if remember_me:
                    save_credentials(username, password)
                else:
                    clear_credentials()

                folder_name = username.split("@")[0]
                main_app.staff_id = response.get("staffid") or main_app.staff_id
                main_app.current_username = username
                main_app.current_password = password

                if not main_app.wait_for_drive_service():
                    self._show_message(
                        QMessageBox.Warning,
                        "Connection",
                        "Could not connect to upload storage.",
                        "Check internet, wait a few seconds, and try Login again.",
                    )
                    return

                try:
                    parent_id, today_folder_id = main_app.ensure_folder_exists_on_drive(folder_name)
                except Exception as exc:
                    logging.exception("Drive folder setup failed: %s", exc)
                    self._show_message(
                        QMessageBox.Warning,
                        "Upload setup failed",
                        "Could not prepare Google Drive folder.",
                        str(exc)[:200],
                    )
                    return

                main_app.FLAG = True
                main_app.setCurrentIndex(1)
                main_app.home_page.start_timer()

                thread = threading.Thread(
                    target=main_app.start_screenshot_capture,
                    args=(folder_name, parent_id, today_folder_id, password),
                    daemon=True,
                )
                thread.start()

                self._show_message(
                    QMessageBox.Information,
                    "Login Successful",
                    f"Welcome, {folder_name}!",
                    "Continue",
                )
                return
        finally:
            self.login_button.setEnabled(True)
            self.login_button.setText("Login")

        if success in (0, "0", False):
            detail = server_message or "Wrong email or password."
        elif checked_in in (False, 0, "0", None):
            detail = server_message or "Please check in on T2G Workroom or biometric device first."
        else:
            detail = server_message or "Login unsuccessful."

        self._show_message(
            QMessageBox.Warning,
            "Login Failed",
            "Login unsuccessful",
            detail,
            forgot=(success in (0, "0", False)),
        )

    def _show_message(self, icon, title, text, button_text, forgot=False):
        msg = QMessageBox(self)
        msg.setIcon(icon)
        msg.setWindowTitle(title)
        msg.setText(text)
        msg.setStyleSheet(
            """
            QMessageBox {
                font-family: 'Arial';
            }
            QMessageBox QLabel {
                color: #1F2937;
                font-size: 14px;
                background-color: transparent;
            }
            QMessageBox QPushButton {
                background-color: #4B5563;
                color: white;
                border-radius: 6px;
                padding: 8px 16px;
                font-size: 14px;
                min-width: 80px;
            }
            QMessageBox QPushButton:hover {
                background-color: #374151;
            }
            """
        )

        ok_button = msg.addButton(button_text, QMessageBox.AcceptRole)
        msg.setDefaultButton(ok_button)

        if forgot:
            forgot_button = msg.addButton("Forgot Password?", QMessageBox.ActionRole)
            forgot_button.setCursor(Qt.PointingHandCursor)
            msg.exec_()
            if msg.clickedButton() == forgot_button:
                QDesktopServices.openUrl(QUrl(FORGOT_PASSWORD_URL))
            return

        msg.exec_()
