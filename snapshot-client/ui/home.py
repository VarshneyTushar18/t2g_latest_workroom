from PyQt5.QtCore import QTimer, Qt, QTime
from PyQt5.QtGui import QFont
from PyQt5.QtWidgets import QLabel, QPushButton, QSizePolicy, QVBoxLayout, QWidget

from ui.alert import TopAlertDialog


class HomePage(QWidget):
    def __init__(self, main_app):
        super().__init__()
        self.main_app = main_app
        self.time_elapsed = QTime(0, 0, 0)

        self.setWindowTitle("Snapshot Tool - Home")
        self.setStyleSheet(
            """
            QWidget {
                background-color: #F7FAFC;
            }
            """
        )

        layout = QVBoxLayout(self)
        layout.setContentsMargins(24, 24, 24, 24)
        layout.setSpacing(16)

        self.title_label = QLabel("Welcome Back!")
        self.title_label.setAlignment(Qt.AlignCenter)
        self.title_label.setFont(QFont("Arial", 18, QFont.Bold))
        self.title_label.setStyleSheet(
            """
            QLabel {
                color: #2D3748;
                background-color: transparent;
            }
            """
        )

        self.timer_card = QWidget()
        self.timer_card.setStyleSheet(
            """
            QWidget {
                background-color: #FFFFFF;
                border-radius: 20px;
            }
            """
        )
        timer_layout = QVBoxLayout(self.timer_card)
        timer_layout.setContentsMargins(20, 24, 20, 24)
        timer_layout.setSpacing(8)

        self.timer_caption = QLabel("Timer")
        self.timer_caption.setAlignment(Qt.AlignCenter)
        self.timer_caption.setFont(QFont("Arial", 12, QFont.Normal))
        self.timer_caption.setStyleSheet("color: #718096; background: transparent;")

        self.timer_label = QLabel("00:00:00")
        self.timer_label.setAlignment(Qt.AlignCenter)
        self.timer_label.setFont(QFont("Consolas", 32, QFont.Bold))
        self.timer_label.setStyleSheet("color: #4A5568; background: transparent;")
        self.timer_label.setMinimumWidth(300)
        self.timer_label.setSizePolicy(QSizePolicy.Expanding, QSizePolicy.Fixed)

        timer_layout.addWidget(self.timer_caption)
        timer_layout.addWidget(self.timer_label)

        self.connection_status_label = QLabel("")
        self.connection_status_label.setAlignment(Qt.AlignCenter)
        self.connection_status_label.setStyleSheet(
            """
            QLabel {
                color: #E53E3E;
                background-color: #FED7D7;
                padding: 12px 20px;
                border-radius: 8px;
                margin: 15px 0;
            }
            """
        )
        self.connection_status_label.hide()

        logout_button = QPushButton("Logout")
        logout_button.setStyleSheet(
            """
            QPushButton {
                background-color: #E53E3E;
                color: white;
                border-radius: 10px;
                padding: 12px 24px;
            }
            QPushButton:hover {
                background-color: #C53030;
            }
            QPushButton:pressed {
                background-color: #9B2C2C;
            }
            """
        )
        logout_button.clicked.connect(self.logout)

        self.timer = QTimer(self)
        self.timer.timeout.connect(self.update_timer)

        layout.addWidget(self.title_label)
        layout.addWidget(self.timer_card)
        layout.addWidget(self.connection_status_label)
        layout.addStretch()
        layout.addWidget(logout_button)

    def check_tracking_status(self):
        if hasattr(self.main_app, "tracking_alert") and self.main_app.tracking_alert.isVisible():
            return
        self.show_tracking_alert()

    def show_tracking_alert(self):
        self.stop_timer()
        alert = TopAlertDialog(self.main_app)
        self.main_app.tracking_alert = alert
        alert.exec_()
        QTimer.singleShot(1000, self.start_timer)

    def update_timer(self):
        self.time_elapsed = self.time_elapsed.addSecs(1)
        self.timer_label.setText(self.time_elapsed.toString("hh:mm:ss"))

    def start_timer(self):
        self.timer.start(1000)

    def stop_timer(self):
        self.timer.stop()

    def reset_timer(self):
        self.time_elapsed = QTime(0, 0, 0)
        self.timer_label.setText("00:00:00")

    def logout(self):
        self.stop_timer()
        self.reset_timer()
        self.main_app.FLAG = False
        self.main_app.setCurrentIndex(0)

    def show_connection_status(self, message):
        if not self.connection_status_label.isVisible():
            self.connection_status_label.setText(message)
            self.connection_status_label.show()
            self.timer_card.hide()

    def hide_connection_status(self):
        self.connection_status_label.hide()
        if not self.timer_card.isVisible():
            self.timer_card.show()
