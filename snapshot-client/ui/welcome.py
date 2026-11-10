from PyQt5.QtCore import Qt, QSettings
from PyQt5.QtGui import QFont
from PyQt5.QtWidgets import (
    QCheckBox,
    QHBoxLayout,
    QLabel,
    QPushButton,
    QVBoxLayout,
    QWidget,
)


class WelcomeWindow(QWidget):
    def __init__(self):
        super().__init__()
        self.continued = False
        self.setWindowTitle("Snapshot Tool - Login")
        self.setFixedSize(560, 420)

        layout = QVBoxLayout(self)
        layout.setContentsMargins(40, 30, 40, 30)

        title = QLabel("Welcome to Snapshot Tool")
        title.setStyleSheet(
            "font-size: 26px; font-weight: bold; color: #141E46; font-family: Arial, sans-serif;"
        )

        subtitle = QLabel("Secure work tracking solution")
        subtitle.setStyleSheet(
            "font-size: 18px; font-family: Arial, sans-serif; color: #555; "
            "padding-bottom: 10px; border-bottom: 1px solid #e0e0e0;"
        )

        steps = QLabel("To get started:")
        steps.setStyleSheet(
            "font-size: 18px; font-weight: bold; color: #333;font-family: Arial, sans-serif;"
        )

        body = QLabel(
            "1. Check in on T2G Workroom (web or biometric).\n"
            "2. Log in here with your Workroom email and password.\n"
            "3. Keep this app running while you work."
        )
        body.setWordWrap(True)
        body.setStyleSheet(
            "font-size: 16px; color: #444; font-family: Arial, sans-serif;"
        )

        self.skip_checkbox = QCheckBox("Don't show this welcome screen again")
        self.skip_checkbox.setStyleSheet(
            """
            font-size: 14px;
            color: #666;
            font-family: Arial, sans-serif;
            padding: 15px 0 5px 0;
            """
        )

        continue_button = QPushButton("Continue to Login")
        continue_button.setStyleSheet(
            """
            QPushButton {
                font-size: 16px;
                font-weight: bold;
                padding: 10px 35px;
                background-color: #2563EB;
                color: white;
                border-radius: 5px;
                min-width: 180px;
            }
            QPushButton:hover {
                background-color: #1d4ed8;
            }
            """
        )
        continue_button.clicked.connect(self.accept_continue)

        layout.addWidget(title)
        layout.addWidget(subtitle)
        layout.addSpacing(12)
        layout.addWidget(steps)
        layout.addWidget(body)
        layout.addWidget(self.skip_checkbox)
        layout.addStretch()
        layout.addWidget(continue_button, alignment=Qt.AlignCenter)

    def accept_continue(self):
        if self.skip_checkbox.isChecked():
            settings = QSettings("T2G", "SnapshotTool")
            settings.setValue("first_run", False)
        self.continued = True
        self.close()

    def closeEvent(self, event):
        if not self.continued:
            event.accept()
            return
        event.accept()

    def is_first_run(self):
        settings = QSettings("T2G", "SnapshotTool")
        value = settings.value("first_run", True)
        if isinstance(value, str):
            return value.lower() not in ("0", "false", "no")
        return bool(value)
