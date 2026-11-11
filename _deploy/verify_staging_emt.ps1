#Requires -Version 5.1
$ErrorActionPreference = 'Stop'
$LocalRoot = Split-Path $PSScriptRoot -Parent
$localFile = Join-Path $LocalRoot 'application\models\Emt_model.php'
$localLen = (Get-Item $localFile).Length
$localHash = (Get-FileHash $localFile -Algorithm SHA256).Hash
$marker = 'snapshotFirstCheckinByStaffForDay'

$fz = Join-Path $env:APPDATA 'FileZilla\sitemanager.xml'
[xml]$xml = Get-Content -LiteralPath $fz -Encoding UTF8
$s = $xml.FileZilla3.Servers.Server | Where-Object { $_.Name -eq 'tech2globe' } | Select-Object -First 1
if (-not $s) { throw 'FileZilla site tech2globe not found' }
$pass = $s.Pass
if ($pass.encoding -eq 'base64') {
    $pass = [Text.Encoding]::UTF8.GetString([Convert]::FromBase64String([string]$pass.'#text'))
} else {
    $pass = [string]$pass.'#text'
}
$cred = New-Object System.Net.NetworkCredential([string]$s.User, $pass)
$hostUri = 'ftp://' + $s.Host + ':' + $s.Port

Write-Host "LOCAL Emt_model.php  size=$localLen  sha256=$localHash"
Write-Host "Looking for marker: $marker"
Write-Host ''

$relFiles = @(
    'application/models/Emt_model.php',
    'application/views/admin/emt/_snapshot_ui_loader_script.php',
    'application/views/admin/emt/snapshot_daily_report.php'
)

$paths = @(
    '/application/models/Emt_model.php',
    '/public_html/application/models/Emt_model.php',
    '/t2gworkroom/application/models/Emt_model.php',
    '/workroom/application/models/Emt_model.php',
    '/html/application/models/Emt_model.php',
    '/var/www/workroom/application/models/Emt_model.php'
)

function Get-FtpBytes {
    param([string]$FtpUri, [System.Net.NetworkCredential]$Cred)
    $req = [System.Net.FtpWebRequest]::Create($FtpUri)
    $req.Method = [System.Net.WebRequestMethods+Ftp]::DownloadFile
    $req.Credentials = $Cred
    $req.UsePassive = $true
    $resp = $req.GetResponse()
    $stream = $resp.GetResponseStream()
    $ms = New-Object System.IO.MemoryStream
    $stream.CopyTo($ms)
    $resp.Close()
    return $ms.ToArray()
}

foreach ($p in $paths) {
    $ftpFile = $hostUri + $p
    try {
        $bytes = Get-FtpBytes -FtpUri $ftpFile -Cred $cred
        $hash = [BitConverter]::ToString([System.Security.Cryptography.SHA256]::Create().ComputeHash($bytes)).Replace('-', '').ToLower()
        $text = [Text.Encoding]::UTF8.GetString($bytes)
        $hasMarker = $text.Contains($marker)
        $match = ($hash -eq $localHash)
        Write-Host "FOUND $p"
        Write-Host "  size=$($bytes.Length)  sha256=$hash  matches_local=$match  has_marker=$hasMarker"
    } catch {
        Write-Host "MISS  $p"
    }
}

# List FTP root (top level)
Write-Host ''
Write-Host 'FTP LIST / (top-level names):'
$req = [System.Net.FtpWebRequest]::Create($hostUri + '/')
$req.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
$req.Credentials = $cred
$req.UsePassive = $true
$resp = $req.GetResponse()
$reader = New-Object System.IO.StreamReader($resp.GetResponseStream())
$listing = $reader.ReadToEnd()
$resp.Close()
($listing -split "`r?`n" | Where-Object { $_.Trim() }) | ForEach-Object { Write-Host "  $_" }

Write-Host ''
Write-Host 'Compare key files: FTP root vs public_html'
foreach ($rel in $relFiles) {
    $local = Join-Path $LocalRoot ($rel.Replace('/', '\'))
    if (-not (Test-Path $local)) { continue }
    $localH = (Get-FileHash $local -Algorithm SHA256).Hash.ToLower()
    foreach ($base in @('', '/public_html')) {
        $ftp = $hostUri + $base + '/' + $rel.Replace('\', '/')
        try {
            $bytes = Get-FtpBytes -FtpUri $ftp -Cred $cred
            $h = [BitConverter]::ToString([System.Security.Cryptography.SHA256]::Create().ComputeHash($bytes)).Replace('-', '').ToLower()
            $tag = if ($base -eq '') { 'ROOT' } else { 'public_html' }
            $match = ($h -eq $localH)
            Write-Host "${tag} ${rel}  size=$($bytes.Length)  matches_local=$match"
        } catch {
            $tag = if ($base -eq '') { 'ROOT' } else { 'public_html' }
            Write-Host "${tag} ${rel}  MISSING"
        }
    }
}

Write-Host ''
Write-Host 'public_html/ listing:'
$req2 = [System.Net.FtpWebRequest]::Create($hostUri + '/public_html/')
$req2.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
$req2.Credentials = $cred
$req2.UsePassive = $true
$resp2 = $req2.GetResponse()
$reader2 = New-Object System.IO.StreamReader($resp2.GetResponseStream())
$listing2 = $reader2.ReadToEnd()
$resp2.Close()
($listing2 -split "`r?`n" | Where-Object { $_.Trim() }) | ForEach-Object { Write-Host "  $_" }
