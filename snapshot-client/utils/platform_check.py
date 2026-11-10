import platform
import sys


def windows_version():
    if sys.platform != "win32":
        return None
    try:
        ver = platform.version().split(".")
        return tuple(int(part) for part in ver[:3])
    except (TypeError, ValueError):
        return None


def compatibility_message():
    if sys.platform != "win32":
        return "Snapshot Tool runs on Windows only."

    version = windows_version()
    if version is None:
        return None

    major, minor = version[0], version[1]
    if (major, minor) < (6, 1):
        return (
            "Snapshot Tool requires Windows 7 SP1 or newer.\n"
            "Your Windows version is too old for this build."
        )

    if platform.machine().endswith("64") and sys.maxsize <= 2**32:
        return (
            "This is the 32-bit Snapshot build on 64-bit Windows.\n"
            "If startup fails, download the package again and run Snapshot.bat."
        )

    return None
