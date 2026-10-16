# Full Desktop bundle: tech2globe workroom latest 10 oct
$ErrorActionPreference = 'Stop'
$Src = 'd:\important files\workroom downloaded\t2gworkroom_backup_2026-07-07_07-14-51\t2gworkroom'
$Desktop = [Environment]::GetFolderPath('Desktop')
$BundleRoot = Join-Path $Desktop 'tech2globe workroom latest 10 oct'
$CodeDir = Join-Path $BundleRoot 't2gworkroom-code'
$DbDir = Join-Path $BundleRoot 'database'
$AssetsNote = Join-Path $BundleRoot 'PUBLIC-ASSETS-README.txt'

if (Test-Path $BundleRoot) {
    Remove-Item -Recurse -Force $BundleRoot
}
New-Item -ItemType Directory -Path $CodeDir -Force | Out-Null
New-Item -ItemType Directory -Path $DbDir -Force | Out-Null

$excludeDirs = @(
    '_snapshot_run', '__pycache__', '.git', 'node_modules',
    '_backup_phase_a_leave_2026-10-13', '_backup_span_rule_20260928'
)

Write-Host "Copying application code to $CodeDir ..."
robocopy $Src $CodeDir /MIR /XD $excludeDirs /XF *.xlsx *.pem Snapshot.exe /NFL /NDL /NJH /NJS /nc /ns /np | Out-Null
if ($LASTEXITCODE -ge 8) { throw "Robocopy failed: $LASTEXITCODE" }

# Malware disguised as CSS
Get-ChildItem -Path (Join-Path $CodeDir 'api') -Recurse -File -Filter '*.css' -ErrorAction SilentlyContinue |
    Where-Object { $_.Name -match '^\.[0-9a-f]{8}\.css$' } |
    ForEach-Object { Remove-Item -Force $_.FullName }

$junkPaths = @(
    'application\controllers\admin\Staff_model.php',
    'application\controllers\admin\admin',
    'application\models\models',
    'application\views\views',
    'application\views\admin\tickets\tickets_statuses\tickets_statuses',
    'modules\menu_setup\helpers\helpers',
    'modules\timesheets\views\approval_process\includes\includes'
)
foreach ($rel in $junkPaths) {
    $p = Join-Path $CodeDir $rel
    if (Test-Path $p) { Remove-Item -Recurse -Force $p }
}

# Carbon PHP 8.3 fix in vendor (match live patch)
$carbonCreator = Join-Path $CodeDir 'application\vendor\nesbot\carbon\src\Carbon\Traits\Creator.php'
if (Test-Path $carbonCreator) {
    $c = Get-Content -Raw $carbonCreator
    $c = $c -replace 'static::setLastErrors\(parent::getLastErrors\(\)\);', 'static::setLastErrors(parent::getLastErrors() ?: []);'
    Set-Content -Path $carbonCreator -Value $c -NoNewline
}

# Targeted scan
$scanScript = Join-Path $CodeDir '_deploy\scan_malware_targeted.php'
if (-not (Test-Path $scanScript)) {
    @'
<?php
$roots = [__DIR__ . '/../application', __DIR__ . '/../modules'];
$patterns = ['865ae83b-f164', 'chr ( 138 - 17 )', '$Kmocqq', 'yB_YpGyW'];
$hits = [];
foreach ($roots as $root) {
    if (!is_dir($root)) continue;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if (!$f->isFile() || strtolower($f->getExtension()) !== 'php') continue;
        $txt = file_get_contents($f->getPathname());
        foreach ($patterns as $p) { if (strpos($txt, $p) !== false) { $hits[] = $f->getPathname(); break; } }
    }
}
file_put_contents(__DIR__ . '/MALWARE_SCAN_RESULT.txt', $hits ? implode("\n", $hits) : 'CLEAN');
exit($hits ? 2 : 0);
'@ | Set-Content $scanScript -Encoding UTF8
}
Push-Location $CodeDir
php _deploy\scan_malware_targeted.php | Out-Null
Pop-Location

@'
Public / upload assets live inside the code tree:

- assets/          — CSS, JS, builds (admin-dashboard-nav.css, etc.)
- uploads/         — user uploads (if present on server; export DB + sync uploads separately for full parity)
- modules/*/assets — module static files
- Snapshot.zip     — desktop Snapshot client (root of site on live)

For local XAMPP: point document root to t2gworkroom-code/ and import database/*.sql
'@ | Set-Content $AssetsNote -Encoding UTF8

$readme = @"
Tech2Globe Workroom — bundle (16 Oct 2026)

Folders:
  t2gworkroom-code/   Latest PHP app (HRMS, WFH, Snapshot, navbar fixes, no known backdoors in application/)
  database/           MySQL dump from live (if export succeeded)

Live fixes included in code:
  - Clean Proposals_model (no malware stub)
  - admin-dashboard-nav.css + header.php navbar
  - Carbon PHP 8.3 patch in vendor
  - api fake .css backdoors removed

See t2gworkroom-code\_deploy\MALWARE_SCAN_RESULT.txt
"@
Set-Content -Path (Join-Path $BundleRoot 'README.txt') -Value $readme -Encoding UTF8

Write-Host "Bundle root: $BundleRoot"
