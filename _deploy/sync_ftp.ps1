#Requires -Version 5.1
<#
.SYNOPSIS
  Mirror local t2g_latest_workroom to FTP (tech2globe FileZilla site or env vars).

.PARAMETER SiteName
  FileZilla Site Manager entry name (default: tech2globe).

.PARAMETER RemotePath
  Remote folder, e.g. /public_html or /t2gworkroom

.PARAMETER WhatIf
  List files that would be uploaded without uploading.

.EXAMPLE
  .\_deploy\sync_ftp.ps1 -RemotePath /public_html/t2gworkroom -WhatIf
  .\_deploy\sync_ftp.ps1 -RemotePath /public_html/t2gworkroom
#>
param(
    [string] $SiteName = 'tech2globe',
    [Parameter(Mandatory = $true)]
    [string] $RemotePath,
    [switch] $WhatIf,
    [switch] $DryRun
)

$ErrorActionPreference = 'Stop'
if ($DryRun) { $WhatIf = $true }

$LocalRoot = Split-Path $PSScriptRoot -Parent
if (-not (Test-Path (Join-Path $LocalRoot 'index.php'))) {
    throw "Could not find project root (index.php). Run from t2g_latest_workroom."
}

function Get-FtpSettings {
    param([string] $Name)

  if ($env:T2G_FTP_HOST -and $env:T2G_FTP_USER -and $env:T2G_FTP_PASS) {
        return [pscustomobject]@{
            Host = $env:T2G_FTP_HOST
            Port = if ($env:T2G_FTP_PORT) { [int]$env:T2G_FTP_PORT } else { 21 }
            User = $env:T2G_FTP_USER
            Pass = $env:T2G_FTP_PASS
        }
    }

    $fz = Join-Path $env:APPDATA 'FileZilla\sitemanager.xml'
    if (-not (Test-Path $fz)) {
        throw 'Set T2G_FTP_HOST, T2G_FTP_USER, T2G_FTP_PASS or install FileZilla site "' + $Name + '".'
    }
    [xml] $xml = Get-Content -LiteralPath $fz -Encoding UTF8
    $server = $xml.FileZilla3.Servers.Server | Where-Object { $_.Name -eq $Name } | Select-Object -First 1
    if (-not $server) {
        throw "FileZilla site '$Name' not found in sitemanager.xml"
    }
    $pass = $server.Pass
    if ($pass.encoding -eq 'base64') {
        $pass = [Text.Encoding]::UTF8.GetString([Convert]::FromBase64String([string]$pass.'#text'))
    } else {
        $pass = [string]$pass.'#text'
    }

    return [pscustomobject]@{
        Host = [string]$server.Host
        Port = if ($server.Port) { [int]$server.Port } else { 21 }
        User = [string]$server.User
        Pass = $pass
    }
}

$ExcludeDirPattern = '(\\|^)(\.git|node_modules|temp|application\\cache|application\\logs)(\\|$)'
$ExcludeFilePattern = '\.(log|tmp|bak|sql)$'

function Test-ShouldSkip {
    param([string] $FullPath, [string] $Root)
    $rel = $FullPath.Substring($Root.Length).TrimStart('\')
    if ($rel -match $ExcludeDirPattern) { return $true }
    if ($rel -match $ExcludeFilePattern) { return $true }
    return $false
}

function Ensure-FtpDirectory {
    param(
        [string] $FtpUri,
        [System.Net.NetworkCredential] $Cred
    )
    $req = [System.Net.FtpWebRequest]::Create($FtpUri)
    $req.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
    $req.Credentials = $Cred
    $req.UsePassive = $true
    try {
        $resp = $req.GetResponse()
        $resp.Close()
    } catch {
        if ($_.Exception.Message -notmatch '550') {
            throw
        }
    }
}

function Send-FtpFile {
    param(
        [string] $LocalFile,
        [string] $FtpUri,
        [System.Net.NetworkCredential] $Cred
    )
    $req = [System.Net.FtpWebRequest]::Create($FtpUri)
    $req.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $req.Credentials = $Cred
    $req.UseBinary = $true
    $req.UsePassive = $true
    $bytes = [System.IO.File]::ReadAllBytes($LocalFile)
    $req.ContentLength = $bytes.Length
    $stream = $req.GetRequestStream()
    $stream.Write($bytes, 0, $bytes.Length)
    $stream.Close()
    $resp = $req.GetResponse()
    $resp.Close()
}

$creds = Get-FtpSettings -Name $SiteName
$remoteBase = $RemotePath.TrimEnd('/') -replace '\\', '/'
if ($remoteBase -notmatch '^/') { $remoteBase = '/' + $remoteBase }

$ftpCred = New-Object System.Net.NetworkCredential($creds.User, $creds.Pass)
$hostUri = 'ftp://' + $creds.Host + ':' + $creds.Port

Write-Host "Local:  $LocalRoot"
Write-Host "Remote: $hostUri$remoteBase"
Write-Host "Site:   $SiteName ($($creds.User))"
if ($WhatIf) { Write-Host 'WHATIF — no uploads' -ForegroundColor Yellow }

$files = Get-ChildItem -LiteralPath $LocalRoot -Recurse -File -Force |
    Where-Object { -not (Test-ShouldSkip -FullPath $_.FullName -Root $LocalRoot) }

$uploaded = 0
foreach ($f in $files) {
    $rel = $f.FullName.Substring($LocalRoot.Length).Replace('\', '/')
    $remotePath = $remoteBase + $rel
    $parts = $remotePath.Trim('/').Split('/')
    $build = ''
    if ($WhatIf) {
        Write-Host "  would upload: $rel"
        continue
    }
    for ($i = 0; $i -lt $parts.Length - 1; $i++) {
        $build += '/' + $parts[$i]
        Ensure-FtpDirectory -FtpUri ($hostUri + $build) -Cred $ftpCred
    }
    $ftpFile = $hostUri + $remotePath
    Send-FtpFile -LocalFile $f.FullName -FtpUri $ftpFile -Cred $ftpCred
    $uploaded++
    if ($uploaded % 50 -eq 0) {
        Write-Host "  uploaded $uploaded files..."
    }
}

if ($WhatIf) {
    Write-Host "Would upload $($files.Count) files (no FTP connection used)."
} else {
    Write-Host "Done. Uploaded $uploaded files."
}
