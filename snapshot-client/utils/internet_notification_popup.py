import os

from PyQt5.QtCore import Qt, QTimer
from PyQt5.QtGui import QIcon
from PyQt5.QtWidgets import QLabel, QVBoxLayout, QWidget


class NotificationPopup(QWidget):
    def __init__(self, message, parent=None):
        super().__init__(parent)
        self.setWindowFlags(
            Qt.FramelessWindowHint | Qt.WindowStaysOnTopHint | Qt.Tool
        )
        self.setAttribute(Qt.WA_TranslucentBackground)

        icon_path = os.path.join(os.path.dirname(os.path.dirname(__file__)), "assets", "icon.ico")
        if os.path.isfile(icon_path):
            self.setWindowIcon(QIcon(icon_path))

        layout = QVBoxLayout(self)
        layout.setContentsMargins(18, 16, 18, 16)

        title = QLabel("Snapshot Tool")
        title.setStyleSheet(
            """
            color: white;
            font-weight: 700;
            font-size: 22px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            """
        )

        body = QLabel(message)
        body.setWordWrap(True)
        body.setStyleSheet(
            """
            color: #dce6ff;
            font-size: 16px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            """
        )

        container = QWidget()
        container.setStyleSheet(
            """
            background-color: rgba(30, 36, 50, 230);
            border-top-right-radius: 14px;
            border-bottom-right-radius: 14px;
            margin-left: 8px;
            """
        )
        inner = QVBoxLayout(container)
        inner.addWidget(title)
        inner.addWidget(body)
        layout.addWidget(container)

        self.adjustSize()
        screen = self.screen().availableGeometry()
        self.move(screen.right() - self.width() - 20, screen.bottom() - self.height() - 20)

        QTimer.singleShot(5000, self.close)
