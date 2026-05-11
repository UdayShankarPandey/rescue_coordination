# Rescue Coordination System - Fully Automated Setup

$ErrorActionPreference = 'Continue'

$baseDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$serverEnv = Join-Path $baseDir "server-env"
$phpDir = Join-Path $serverEnv "php"
$mysqlDir = Join-Path $serverEnv "mariadb"
$dataDir = Join-Path $mysqlDir "data"
$projectDir = Join-Path $baseDir "rescue_coordination\rescue_coordination"

Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "   RESCUE COORDINATION SYSTEM - AUTOMATED SETUP" -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "[1/5] Creating directories..." -ForegroundColor Yellow
if (!(Test-Path $serverEnv)) { New-Item -ItemType Directory -Path $serverEnv -Force | Out-Null }
if (!(Test-Path $phpDir)) { New-Item -ItemType Directory -Path $phpDir -Force | Out-Null }
if (!(Test-Path $mysqlDir)) { New-Item -ItemType Directory -Path $mysqlDir -Force | Out-Null }
if (!(Test-Path $dataDir)) { New-Item -ItemType Directory -Path $dataDir -Force | Out-Null }
Write-Host "        [OK] Directories created" -ForegroundColor Green
Write-Host ""

Write-Host "[2/5] Downloading PHP 8.2 (this may take 2-3 minutes)..." -ForegroundColor Yellow
$phpUrl = "https://windows.php.net/downloads/releases/php-8.2.12-Win32-vs16-x64.zip"
$phpZip = Join-Path $serverEnv "php.zip"

try {
    $webClient = New-Object System.Net.WebClient
    $webClient.DownloadFile($phpUrl, $phpZip)
    Write-Host "        [OK] PHP downloaded" -ForegroundColor Green
} catch {
    Write-Host "        [ERROR] Failed to download PHP" -ForegroundColor Red
    Write-Host "        Please visit: $phpUrl" -ForegroundColor Yellow
    exit 1
}

Write-Host ""
Write-Host "[3/5] Extracting PHP..." -ForegroundColor Yellow
try {
    Expand-Archive -Path $phpZip -DestinationPath $phpDir -Force
    Remove-Item $phpZip -Force
    Write-Host "        [OK] PHP extracted" -ForegroundColor Green
} catch {
    Write-Host "        [ERROR] Failed to extract PHP" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "[4/5] Downloading MariaDB 10.6 (this may take 3-5 minutes)..." -ForegroundColor Yellow
$mariadbUrl = "https://mirror.mariadb.org/mariadb-10.6.15/winx64-packages/mariadb-10.6.15-winx64.zip"
$mariadbZip = Join-Path $serverEnv "mariadb.zip"

try {
    $webClient = New-Object System.Net.WebClient
    $webClient.DownloadFile($mariadbUrl, $mariadbZip)
    Write-Host "        [OK] MariaDB downloaded" -ForegroundColor Green
} catch {
    Write-Host "        [ERROR] Failed to download MariaDB" -ForegroundColor Red
    Write-Host "        Please visit: $mariadbUrl" -ForegroundColor Yellow
    exit 1
}

Write-Host ""
Write-Host "[5/5] Extracting MariaDB..." -ForegroundColor Yellow
try {
    $tempMariadb = Join-Path $serverEnv "mariadb_temp"
    Expand-Archive -Path $mariadbZip -DestinationPath $tempMariadb -Force
    
    $extractedFolder = Get-ChildItem -Path $tempMariadb -Directory | Select-Object -First 1
    
    if ($extractedFolder) {
        Copy-Item -Path "$($extractedFolder.FullName)\*" -Destination $mysqlDir -Recurse -Force
        Remove-Item $tempMariadb -Recurse -Force
        Write-Host "        [OK] MariaDB extracted" -ForegroundColor Green
    } else {
        throw "Could not find extracted MariaDB folder"
    }
    
    Remove-Item $mariadbZip -Force
} catch {
    Write-Host "        [ERROR] Failed to extract MariaDB: $_" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "Verifying installations..." -ForegroundColor Yellow
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""

$phpExe = Join-Path $phpDir "php.exe"
$mysqlExe = Join-Path $mysqlDir "bin\mysqld.exe"

if (Test-Path $phpExe) {
    Write-Host "        ✓ PHP found" -ForegroundColor Green
} else {
    Write-Host "        ✗ PHP not found" -ForegroundColor Red
    exit 1
}

if (Test-Path $mysqlExe) {
    Write-Host "        ✓ MariaDB found" -ForegroundColor Green
} else {
    Write-Host "        ✗ MariaDB not found" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "Starting services..." -ForegroundColor Yellow
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "Starting MariaDB database..." -ForegroundColor Yellow
$mysqlBin = Join-Path $mysqlDir "bin"
$mysqlCmd = "cd /d `"$mysqlBin`" && mysqld --datadir=`"$dataDir`" --port=3306 --skip-grant-tables"
Start-Process -FilePath "cmd.exe" -ArgumentList "/c $mysqlCmd" -WindowStyle Normal | Out-Null

Write-Host "Waiting for MariaDB to initialize (3 seconds)..." -ForegroundColor Yellow
Start-Sleep -Seconds 3

Write-Host ""
Write-Host "Starting PHP development server..." -ForegroundColor Yellow
Write-Host ""
Write-Host "============================================================" -ForegroundColor Green
Write-Host "✓ APPLICATION READY!" -ForegroundColor Green
Write-Host "============================================================" -ForegroundColor Green
Write-Host ""
Write-Host "Open your browser and go to:" -ForegroundColor Cyan
Write-Host "    http://localhost:8000" -ForegroundColor Yellow
Write-Host ""
Write-Host "To stop the server: Press Ctrl+C" -ForegroundColor Cyan
Write-Host ""
Write-Host "============================================================" -ForegroundColor Green
Write-Host ""

Set-Location $projectDir
& $phpExe -S localhost:8000
