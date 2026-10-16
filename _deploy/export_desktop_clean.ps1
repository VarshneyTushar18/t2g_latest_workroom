# Export clean Workroom tree to Desktop (Windows).
$ErrorActionPreference = 'Stop'
$Src = 'd:\important files\workroom downloaded\t2gworkroom_backup_2026-07-07_07-14-51\t2gworkroom'
$Dest = Join-Path $env:USERPROFILE 'Desktop\t2gworkroom-latest-clean'
$Stamp = Get-Date -Format 'yyyy-MM-dd_HHmm'
$DestFinal = "${Dest}_$Stamp"

if (Test-Path $DestFinal) {
    Remove-Item -Recurse -Force $DestFinal
}

$excludeDirs = @(
    '_snapshot_run',
    '__pycache__',
    '.git',
    'node_modules',
    '_backup_phase_a_leave_2026-10-13',
    '_backup_span_rule_20260928'
)

Write-Host "Copying to $DestFinal ..."
robocopy $Src $DestFinal /MIR /XD $excludeDirs /XF *.xlsx *.pem Snapshot.exe /NFL /NDL /NJH /NJS /nc /ns /np | Out-Null
if ($LASTEXITCODE -ge 8) { throw "Robocopy failed with code $LASTEXITCODE" }

# Known malicious disguised CSS (PHP backdoors) under api/
Get-ChildItem -Path (Join-Path $DestFinal 'api') -Recurse -File -Filter '*.css' -ErrorAction SilentlyContinue |
    Where-Object { $_.Name -match '^\.[0-9a-f]{8}\.css$' } |
    ForEach-Object {
        Write-Host "Removing malware file: $($_.FullName)"
        Remove-Item -Force $_.FullName
    }

# Junk / misplaced copies (not part of normal app layout)
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
    $p = Join-Path $DestFinal $rel
    if (Test-Path $p) {
        Write-Host "Removing junk path: $rel"
        Remove-Item -Recurse -Force $p
    }
}

# Targeted malware scan (application + modules only)
$scanScript = Join-Path $DestFinal '_deploy\scan_malware_targeted.php'
@'
<?php
$roots = [__DIR__ . '/../application', __DIR__ . '/../modules'];
$patterns = [
    '/865ae83b-f164-4e65-9bf8-fdd87cf49936/',
    '/chr\s*\(\s*138\s*-\s*17\s*\)/',
    '/\$Kmocqq\s=/',
    '/yB_YpGyW/',
];
$hits = [];
foreach ($roots as $root) {
    if (!is_dir($root)) continue;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if (!$f->isFile() || strtolower($f->getExtension()) !== 'php') continue;
        $c = file_get_contents($f->getPathname());
        foreach ($patterns as $rx) {
            if (preg_match($rx, $c)) {
                $hits[] = str_replace('\\', '/', substr($f->getPathname(), strlen(dirname(__DIR__)) + 1));
                break;
            }
        }
    }
}
file_put_contents(__DIR__ . '/MALWARE_SCAN_RESULT.txt', $hits ? implode("\n", $hits) : "CLEAN — no known backdoor signatures in application/ and modules/");
echo count($hits) ? "FAIL\n" : "OK\n";
exit(count($hits) ? 2 : 0);
'@ | Set-Content -Path $scanScript -Encoding UTF8

Push-Location $DestFinal
php _deploy\scan_malware_targeted.php
$scanOk = ($LASTEXITCODE -eq 0)
Pop-Location

$readme = @"
# T2G Workroom — clean export ($Stamp)

**Branch base:** phase-a-leave @ f9bf3ff (+ local navbar/Snapshot fixes in this folder)

## Included
- HRMS / WFH / leave / attendance integration
- Snapshot (Emt) admin + helpers
- Navbar fix: ``application/views/admin/includes/header.php``, ``assets/css/admin-dashboard-nav.css``
- Deploy helpers under ``_deploy/`` (smoke test, malware scanners)

## Removed from this copy
- ``_snapshot_run/``, git history, large xlsx/pem, backup folders
- Disguised PHP backdoors: ``api/**/.xxxxxxxx.css``
- Misplaced junk trees (duplicate admin/models/views folders)

## Malware scan
See ``_deploy/MALWARE_SCAN_RESULT.txt`` — $(if ($scanOk) { 'PASSED' } else { 'REVIEW REQUIRED' })

## Live server note
After deploy, run ``php _deploy/scan_malware_targeted.php`` on the server and ensure ``Proposals_model.php`` has no prepended obfuscated block.

"@
Set-Content -Path (Join-Path $DestFinal 'README-CLEAN-EXPORT.txt') -Value $readme -Encoding UTF8

Write-Host ""
Write-Host "Done: $DestFinal"
Write-Host "Open Desktop folder: t2gworkroom-latest-clean_$Stamp"
