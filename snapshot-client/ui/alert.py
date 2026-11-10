from PyQt5.QtCore import Qt
from PyQt5.QtWidgets import QDialog, QLabel, QPushButton, QVBoxLayout


class TopAlertDialog(QDialog):
    def __init__(self, parent=None):
        super().__init__(parent)
        self.setModal(True)
        self.setWindowFlags(Qt.Dialog | Qt.FramelessWindowHint)
        self.setStyleSheet(
            """
            QDialog {
                background-color: rgba(0, 0, 0, 0.6);
            }
            QLabel#mainLabel {
                background-color: #FEE2E2;
                border: 2px solid #F87171;
                border-radius: 12px;
                padding: 40px;
                font-size: 20px;
                color: #B91C1C;
            }
            QPushButton {
                background-color: #EF4444;
                color: white;
                padding: 12px 24px;
                font-size: 16px;
                border-radius: 8px;
            }
            QPushButton:hover {
                background-color: #DC2626;
            }
            """
        )

        layout = QVBoxLayout(self)
        label = QLabel(
            "🔒 Please start tracking your work.\n\nClick OK to return to the app."
        )
        label.setObjectName("mainLabel")
        label.setAlignment(Qt.AlignCenter)

        ok_button = QPushButton("OK")
        ok_button.clicked.connect(self.accept)

        layout.addWidget(label)
        layout.addWidget(ok_button, alignment=Qt.AlignCenter)
