# Bring the Snapshot window to the front after Snapshot.bat starts it.
# Windows often leaves a newly started app on the taskbar instead of opening it.
$ErrorActionPreference = 'SilentlyContinue'

Add-Type @"
using System;
using System.Runtime.InteropServices;
public static class SnapForeground {
  [DllImport("user32.dll")] public static extern bool SetForegroundWindow(IntPtr hWnd);
  [DllImport("user32.dll")] public static extern bool ShowWindow(IntPtr hWnd, int nCmdShow);
  [DllImport("user32.dll")] public static extern bool BringWindowToTop(IntPtr hWnd);
  [DllImport("user32.dll")] public static extern bool SetWindowPos(IntPtr hWnd, IntPtr hWndInsertAfter, int X, int Y, int cx, int cy, uint uFlags);
  [DllImport("user32.dll")] public static extern bool GetWindowRect(IntPtr hWnd, out RECT lpRect);
  [DllImport("user32.dll")] public static extern void keybd_event(byte bVk, byte bScan, uint dwFlags, UIntPtr dwExtraInfo);
  public struct RECT { public int Left; public int Top; public int Right; public int Bottom; }
}
"@

Add-Type -AssemblyName System.Windows.Forms

$names = @('Snapshot-x64', 'Snapshot-x86', 'Snapshot')
$deadline = (Get-Date).AddSeconds(25)
$hwnd = [IntPtr]::Zero

do {
    Start-Sleep -Milliseconds 300
    foreach ($name in $names) {
        $proc = Get-Process -Name $name -ErrorAction SilentlyContinue |
            Where-Object { $_.MainWindowHandle -ne 0 } |
            Select-Object -First 1
        if ($proc) {
            $hwnd = $proc.MainWindowHandle
            break
        }
    }
} while ($hwnd -eq [IntPtr]::Zero -and (Get-Date) -lt $deadline)

if ($hwnd -eq [IntPtr]::Zero) {
    exit 0
}

$rect = New-Object SnapForeground+RECT
[void][SnapForeground]::GetWindowRect($hwnd, [ref]$rect)
$area = [System.Windows.Forms.Screen]::PrimaryScreen.WorkingArea
$offScreen = ($rect.Right -lt ($area.Left + 40)) -or ($rect.Left -gt ($area.Right - 40)) -or ($rect.Bottom -lt ($area.Top + 40)) -or ($rect.Top -gt ($area.Bottom - 40))
if ($offScreen) {
    # NOSIZE | NOZORDER
    [void][SnapForeground]::SetWindowPos($hwnd, [IntPtr]::Zero, 80, 80, 0, 0, 0x0005)
}

# SW_RESTORE, then raise above other windows long enough to be seen.
[void][SnapForeground]::ShowWindow($hwnd, 9)
[void][SnapForeground]::keybd_event(0x12, 0, 0, [UIntPtr]::Zero)
$show = 0x0001 -bor 0x0002 -bor 0x0040
[void][SnapForeground]::SetWindowPos($hwnd, [IntPtr](-1), 0, 0, 0, 0, $show)
[void][SnapForeground]::BringWindowToTop($hwnd)
[void][SnapForeground]::SetForegroundWindow($hwnd)
[void][SnapForeground]::keybd_event(0x12, 0, 2, [UIntPtr]::Zero)
Start-Sleep -Milliseconds 900
[void][SnapForeground]::SetWindowPos($hwnd, [IntPtr](-2), 0, 0, 0, 0, $show)
exit 0
