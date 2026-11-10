# Extract origin/main Karan snapshot/PEDMA bundle + local navbar files for live deploy.
$ErrorActionPreference = "Stop"
$root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $root

$out = Join-Path $root "_deploy\live_push"
if (Test-Path $out) { Remove-Item -Recurse -Force $out }
New-Item -ItemType Directory -Path $out | Out-Null

$karanFiles = @(
    "api/application/config/database.php",
    "application/config/app-config-sample.php",
    "application/config/app_email_policy.php",
    "application/config/config.php",
    "application/config/database.php",
    "application/controllers/Cron.php",
    "application/controllers/admin/Emt.php",
    "application/controllers/admin/Staff.php",
    "application/helpers/custom_helper.php",
    "application/helpers/menu_helper.php",
    "application/libraries/App_Email.php",
    "application/libraries/mails/App_mail_template.php",
    "application/models/Cron_model.php",
    "application/models/Emails_model.php",
    "application/models/Staff_model.php",
    "application/views/admin/emt/monitoring.php",
    "application/views/admin/emt/snapshot_staff_list.php",
    "application/views/admin/includes/aside.php",
    "application/views/admin/staff/manage_pedma.php",
    "application/views/admin/staff/pedma.php",
    "application/views/admin/staff/pedma_admin.php",
    "application/views/admin/staff/pedma_admin_evaluation_workspace.php",
    "modules/timesheets/models/Timesheets_model.php",
    "scripts/send_snapshot_not_logged_in_sample.php"
)

foreach ($f in $karanFiles) {
    $dest = Join-Path $out $f
    $dir = Split-Path -Parent $dest
    if (-not (Test-Path $dir)) { New-Item -ItemType Directory -Path $dir -Force | Out-Null }
    cmd /c "git show origin/main:$f > `"$dest`""
    if ($LASTEXITCODE -ne 0) { throw "git show failed for $f" }
}

$localFiles = @(
    "application/views/admin/includes/header.php",
    "assets/css/admin-dashboard-nav.css",
    "modules/ultimate_orange_theme/assets/css/theme_styles.css"
)
foreach ($f in $localFiles) {
    $dest = Join-Path $out $f
    $dir = Split-Path -Parent $dest
    if (-not (Test-Path $dir)) { New-Item -ItemType Directory -Path $dir -Force | Out-Null }
    Copy-Item -Path (Join-Path $root $f) -Destination $dest -Force
}

Write-Output "Prepared $(($karanFiles.Count + $localFiles.Count)) files in $out"
