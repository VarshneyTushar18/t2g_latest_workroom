import ctypes
import logging
import os
import sys
from datetime import datetime, timedelta

import psutil
import winreg
from PyQt5.QtGui import QIcon
from PyQt5.QtWidgets import QApplication, QMessageBox

from ui.main_app import MainApp
from ui.welcome import WelcomeWindow
from utils.constants import APP_NAME, EXECUTABLE_PATH
from utils.platform_check import compatibility_message


def resource_path(relative_path):
    base_path = getattr(sys, "_MEIPASS", os.path.dirname(os.path.abspath(__file__)))
    return os.path.join(base_path, relative_path)


def setup_logging():
    local_snapshot_dir = os.path.join(os.getenv("LOCALAPPDATA", ""), ".snapshot")
    log_dir = os.path.join(local_snapshot_dir, "logs")
    os.makedirs(log_dir, exist_ok=True)

    cutoff = datetime.now() - timedelta(days=7)
    for filename in os.listdir(log_dir):
        file_path = os.path.join(log_dir, filename)
        if not os.path.isfile(file_path):
            continue
        try:
            file_time = datetime.fromtimestamp(os.path.getmtime(file_path))
            if file_time < cutoff:
                os.remove(file_path)
        except Exception as exc:
            print(f"Failed to delete old log {filename}: {exc}")

    log_file = os.path.join(
        log_dir,
        f"snapshot_log_{datetime.now().strftime('%Y-%m-%d')}.log",
    )
    logging.basicConfig(
        level=logging.INFO,
        format="%(asctime)s | %(levelname)s | %(message)s",
        handlers=[
            logging.FileHandler(log_file, encoding="utf-8"),
            logging.StreamHandler(sys.stdout),
        ],
    )


def is_process_running(process_name):
    current_pid = os.getpid()
    for proc in psutil.process_iter(["pid", "name"]):
        try:
            if proc.info["pid"] == current_pid:
                continue
            if proc.info["name"] and proc.info["name"].lower() == process_name.lower():
                return True
        except (psutil.NoSuchProcess, psutil.AccessDenied, psutil.ZombieProcess):
            continue
    return False


def register_startup(app_name, exe_path):
    key = r"Software\Microsoft\Windows\CurrentVersion\Run"
    try:
        with winreg.OpenKey(winreg.HKEY_CURRENT_USER, key, 0, winreg.KEY_SET_VALUE) as handle:
            winreg.SetValueEx(handle, app_name, 0, winreg.REG_SZ, exe_path)
        print(f"Auto-start registered: {exe_path}")
    except Exception as exc:
        print(f"Failed to register auto-start: {exc}")


def is_registered(app_name):
    key = r"Software\Microsoft\Windows\CurrentVersion\Run"
    try:
        with winreg.OpenKey(winreg.HKEY_CURRENT_USER, key, 0, winreg.KEY_READ) as handle:
            winreg.QueryValueEx(handle, app_name)
        return True
    except FileNotFoundError:
        return False
    except Exception as exc:
        print(f"Registry check error: {exc}")
        return False


def setup_watchdog_bat(app_name):
    snapshot_folder = os.path.join(os.getenv("LOCALAPPDATA", ""), ".snapshot")
    os.makedirs(snapshot_folder, exist_ok=True)

    bat_path = os.path.join(snapshot_folder, "watchdog.bat")
    vbs_path = os.path.join(snapshot_folder, "watchdog_launcher.vbs")
    exe_name = os.path.basename(EXECUTABLE_PATH)

    bat_content = f"""@echo off
setlocal
set EXENAME={exe_name}

rem Get the EXE path from registry
for /f "tokens=3*" %%a in ('reg query "HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\Run" /v {app_name} 2^>nul') do set EXEPATH=%%a %%b

rem Check if process is running
tasklist /FI "IMAGENAME eq %EXENAME%" 2>nul | find /I "%EXENAME%" >nul
if errorlevel 1 (
    if defined EXEPATH (
        start "" "%EXEPATH%"
    )
)

rem Wait 600 seconds then re-run
timeout /t 600 >nul
call "%~f0"
"""

    vbs_content = (
        'Set WshShell = CreateObject("WScript.Shell")\n'
        f'WshShell.Run chr(34) & "{bat_path}" & chr(34), 0\n'
        "Set WshShell = Nothing\n"
    )

    with open(bat_path, "w", encoding="utf-8") as handle:
        handle.write(bat_content)
    with open(vbs_path, "w", encoding="utf-8") as handle:
        handle.write(vbs_content)

    key = r"Software\Microsoft\Windows\CurrentVersion\Run"
    try:
        with winreg.OpenKey(winreg.HKEY_CURRENT_USER, key, 0, winreg.KEY_SET_VALUE) as handle:
            winreg.SetValueEx(handle, "SnapshotWatchdog", 0, winreg.REG_SZ, f'wscript.exe "{vbs_path}"')
        print("Silent watchdog registered in startup.")
    except Exception as exc:
        print(f"Failed to register watchdog: {exc}")


def show_fatal(message):
    app = QApplication.instance() or QApplication(sys.argv)
    QMessageBox.critical(None, "Snapshot Tool", message)
    sys.exit(1)


def snapshot_process_names():
    """All known Snapshot exe names (old and new packages)."""
    return {
        "snapshot.exe",
        "snapshot-x64.exe",
        "snapshot-x86.exe",
        os.path.basename(EXECUTABLE_PATH).lower(),
    }


def protected_snapshot_pids():
    """This process and the PyInstaller bootloader that launched it.

    The onefile exe stays running as the parent and has the same name.
    Killing that parent closes the window that just opened.
    """
    protected = {os.getpid()}
    try:
        parent = psutil.Process(os.getpid()).parent()
        hops = 0
        while parent is not None and hops < 6:
            protected.add(parent.pid)
            parent = parent.parent()
            hops += 1
    except (psutil.NoSuchProcess, psutil.AccessDenied, psutil.ZombieProcess):
        pass
    return protected


def find_other_snapshot_pid():
    current_pid = os.getpid()
    protected = protected_snapshot_pids()
    names = snapshot_process_names()
    for proc in psutil.process_iter(["pid", "name"]):
        try:
            pid = proc.info["pid"]
            if pid == current_pid or pid in protected:
                continue
            name = (proc.info["name"] or "").lower()
            if name in names:
                return pid, name
        except (psutil.NoSuchProcess, psutil.AccessDenied, psutil.ZombieProcess):
            continue
    return None, None


def window_process_name(hwnd):
    """Executable name that owns a window. Explorer folders named Snapshot are ignored."""
    user32 = ctypes.windll.user32
    pid = ctypes.c_ulong()
    user32.GetWindowThreadProcessId(hwnd, ctypes.byref(pid))
    if not pid.value:
        return ""
    try:
        return (psutil.Process(pid.value).name() or "").lower()
    except (psutil.NoSuchProcess, psutil.AccessDenied):
        return ""


def bring_snapshot_to_front():
    """Raise a real Snapshot app window. A folder named Snapshot does not count."""
    user32 = ctypes.windll.user32
    names = snapshot_process_names()
    found = []

    @ctypes.WINFUNCTYPE(ctypes.c_bool, ctypes.c_void_p, ctypes.c_void_p)
    def enum_proc(hwnd, _lparam):
        if not user32.IsWindowVisible(hwnd):
            return True
        if window_process_name(hwnd) in names:
            found.append(hwnd)
        return True

    user32.EnumWindows(enum_proc, 0)
    if not found:
        return False
    hwnd = found[0]
    user32.ShowWindow(hwnd, 9)  # SW_RESTORE
    user32.keybd_event(0x12, 0, 0, 0)
    user32.SetWindowPos(hwnd, ctypes.c_void_p(-1), 0, 0, 0, 0, 0x0001 | 0x0002 | 0x0040)
    user32.BringWindowToTop(hwnd)
    brought = bool(user32.SetForegroundWindow(hwnd))
    user32.keybd_event(0x12, 0, 2, 0)
    user32.SetWindowPos(hwnd, ctypes.c_void_p(-2), 0, 0, 0, 0, 0x0001 | 0x0002 | 0x0040)
    return brought


def show_in_front(widget):
    """Open the window on screen. A background launch otherwise stays on the taskbar.

    Do not toggle Qt window flags after show(). On some PCs that destroys the
    native window and the app looks like it opened and then crashed.
    """
    widget.showNormal()
    widget.show()
    widget.raise_()
    widget.activateWindow()
    hwnd = int(widget.winId())
    if not hwnd:
        return
    user32 = ctypes.windll.user32
    user32.keybd_event(0x12, 0, 0, 0)
    user32.SetWindowPos(hwnd, ctypes.c_void_p(-1), 0, 0, 0, 0, 0x0001 | 0x0002 | 0x0040)
    user32.SetForegroundWindow(hwnd)
    user32.keybd_event(0x12, 0, 2, 0)
    QtCore = __import__("PyQt5.QtCore", fromlist=["QTimer"])
    QtCore.QTimer.singleShot(
        700,
        lambda: user32.SetWindowPos(hwnd, ctypes.c_void_p(-2), 0, 0, 0, 0, 0x0001 | 0x0002 | 0x0040),
    )


def kill_orphan_snapshot_processes():
    """Kill other Snapshot processes that have no visible window (hung launches)."""
    current_pid = os.getpid()
    protected = protected_snapshot_pids()
    names = snapshot_process_names()
    killed = []
    for proc in psutil.process_iter(["pid", "name"]):
        try:
            pid = proc.info["pid"]
            if pid == current_pid or pid in protected:
                continue
            name = (proc.info["name"] or "").lower()
            if name not in names:
                continue
            logging.warning("Killing orphan Snapshot process %s pid=%s", name, pid)
            proc.kill()
            try:
                proc.wait(timeout=5)
            except (psutil.TimeoutExpired, psutil.NoSuchProcess):
                pass
            killed.append(f"{name}:{pid}")
        except (psutil.NoSuchProcess, psutil.AccessDenied, psutil.ZombieProcess) as exc:
            logging.warning("Could not kill process: %s", exc)
    return killed


def main():
    compat = compatibility_message()
    if compat:
        show_fatal(compat)

    setup_logging()
    logging.info("Starting Snapshot Tool: %s", EXECUTABLE_PATH)

    other_pid, other_name = find_other_snapshot_pid()
    if other_pid:
        # A hidden copy used to block a new window and only say "look on the taskbar".
        logging.info("Closing previous Snapshot so this window can open: %s pid=%s", other_name, other_pid)
        killed = kill_orphan_snapshot_processes()
        logging.info("Cleared previous Snapshot processes: %s", killed or "none")
        import time

        time.sleep(0.8)

    if not is_registered(APP_NAME):
        logging.info("App not registered in startup. Registering...")
        register_startup(APP_NAME, EXECUTABLE_PATH)
        setup_watchdog_bat(APP_NAME)

    app = QApplication(sys.argv)
    app.setApplicationName(APP_NAME)
    app.setApplicationDisplayName("Snapshot Tool")

    try:
        ctypes.windll.shell32.SetCurrentProcessExplicitAppUserModelID(
            "tech2globe.snapshot.snapshot.1.0.0.0"
        )
    except Exception:
        pass

    icon_path = resource_path(os.path.join("assets", "icon.ico"))
    if os.path.isfile(icon_path):
        app.setWindowIcon(QIcon(icon_path))

    welcome = WelcomeWindow()
    if welcome.is_first_run():
        logging.info("First run detected. Launching welcome screen.")
        show_in_front(welcome)
        app.exec_()
        if not welcome.continued:
            logging.info("Welcome screen dismissed. Exiting.")
            sys.exit(0)

    logging.info("Launching main window.")
    main_window = MainApp()
    main_window.setWindowTitle("Snapshot Tool - Login")
    main_window.setGeometry(100, 100, 420, 520)
    if os.path.isfile(icon_path):
        main_window.setWindowIcon(QIcon(icon_path))
    show_in_front(main_window)

    try:
        sys.exit(app.exec_())
    except Exception as exc:
        logging.exception("Crash detected: %s", exc)
        raise


if __name__ == "__main__":
    try:
        main()
    except Exception as exc:
        try:
            setup_logging()
            logging.exception("Fatal startup error: %s", exc)
        except Exception:
            pass
        try:
            show_fatal(
                "Snapshot failed to start:\n\n"
                f"{exc}\n\n"
                "Tell IT, or check log at:\n"
                "%LOCALAPPDATA%\\.snapshot\\logs\\"
            )
        except Exception:
            sys.exit(1)
