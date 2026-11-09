from PyQt5.QtCore import QSettings


def save_credentials(username, password):
    """Save credentials using QSettings."""
    settings = QSettings("T2G", "SnapshotTool")
    settings.setValue("username", username)
    settings.setValue("password", password)


def load_credentials():
    """Load credentials using QSettings."""
    settings = QSettings("T2G", "SnapshotTool")
    return {
        "username": settings.value("username", "", type=str),
        "password": settings.value("password", "", type=str),
    }


def clear_credentials():
    """Clear saved credentials using QSettings."""
    settings = QSettings("T2G", "SnapshotTool")
    settings.remove("username")
    settings.remove("password")
