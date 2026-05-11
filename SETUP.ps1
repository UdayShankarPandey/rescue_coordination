$ErrorActionPreference = 'Stop'

$baseDir = (Get-Location).Path
$serverEnv = Join-Path $baseDir "server-env"
$phpDir = Join-Path $serverEnv "php"
$mysqlDir = Join-Path $serverEnv "mariadb"
$dataDir = Join-Path $mysqlDir "data"
$projectDir = Join-Path $baseDir "rescue_coordination\rescue_coordination"

# Create directories
Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "   RESCUE COORDINATION SYSTEM - AUTOMATED SETUP" -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "[1/5] Creating directories..." -ForegroundColor Yellow
@($serverEnv, $phpDir, $mysqlDir, $dataDir) | ForEach-Object { if (!(Test-Path $_)) { New-Item -ItemType Directory -Path $_ -Force | Out-Null } }
Write-Host "        [OK] Directories created" -ForegroundColor Green
Write-Host ""

# Download PHP
$phpZip = Join-Path $serverEnv "php.zip"
$phpUrl = "https://windows.php.net/downloads/releases/php-8.2.11-Win32-vs16-x64.zip"

Write-Host "[2/5] Downloading PHP 8.2 (this may take 2-3 minutes)..." -ForegroundColor Yellow
try {
    $ProgressPreference = 'SilentlyContinue'
    Invoke-WebRequest -Uri $phpUrl -OutFile $phpZip -UseBasicParsing
    Write-Host "        [OK] PHP downloaded" -ForegroundColor Green
} catch {
    Write-Host "        [ERROR] Failed to download PHP" -ForegroundColor Red
    Write-Host "Error: $_" -ForegroundColor Red
    exit 1
}
Write-Host ""

# Download MariaDB
$mariadbZip = Join-Path $serverEnv "mariadb.zip"
$mariadbUrl = "https://mirror.example.com/mariadb/mariadb-10.6.14/winx64-packages/mariadb-10.6.14-winx64.zip"

Write-Host "[3/5] Downloading MariaDB 10.6 (this may take 3-5 minutes)..." -ForegroundColor Yellow
try {
    $ProgressPreference = 'SilentlyContinue'
    Invoke-WebRequest -Uri $mariadbUrl -OutFile $mariadbZip -UseBasicParsing
    Write-Host "        [OK] MariaDB downloaded" -ForegroundColor Green
} catch {
    Write-Host "        [ERROR] Failed to download MariaDB" -ForegroundColor Red
    Write-Host "Error: $_" -ForegroundColor Red
    exit 1
}
Write-Host ""

# Extract PHP
Write-Host "[4/5] Extracting PHP..." -ForegroundColor Yellow
try {
    Expand-Archive -Path $phpZip -DestinationPath $phpDir -Force -ErrorAction Stop
    Remove-Item $phpZip -Force
    Write-Host "        [OK] PHP extracted" -ForegroundColor Green
} catch {
    Write-Host "        [ERROR] Failed to extract PHP: $_" -ForegroundColor Red
    exit 1
}
Write-Host ""

# Extract MariaDB
Write-Host "[5/5] Extracting MariaDB..." -ForegroundColor Yellow
try {
    $tempDir = Join-Path $serverEnv "mariadb_temp"
    Expand-Archive -Path $mariadbZip -DestinationPath $tempDir -Force -ErrorAction Stop
    
    $extracted = Get-ChildItem -Path $tempDir -Directory | Select-Object -First 1
    if ($extracted) {
        Copy-Item -Path "$($extracted.FullName)\*" -Destination $mysqlDir -Recurse -Force
        Remove-Item $tempDir -Recurse -Force
    }
    
    Remove-Item $mariadbZip -Force
    Write-Host "        [OK] MariaDB extracted" -ForegroundColor Green
} catch {
    Write-Host "        [ERROR] Failed to extract MariaDB: $_" -ForegroundColor Red
    exit 1
}
Write-Host ""

# Verify
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "Verifying installations..." -ForegroundColor Yellow
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""

if (!(Test-Path (Join-Path $phpDir "php.exe"))) {
    Write-Host "        [ERROR] PHP not found" -ForegroundColor Red
    exit 1
}
Write-Host "        OK - PHP found" -ForegroundColor Green

if (!(Test-Path (Join-Path $mysqlDir "bin\mysqld.exe"))) {
    Write-Host "        [ERROR] MariaDB not found" -ForegroundColor Red
    exit 1
}
Write-Host "        OK - MariaDB found" -ForegroundColor Green

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
Write-Host "RESCUE COORDINATION SYSTEM - APPLICATION READY" -ForegroundColor Green
Write-Host "============================================================" -ForegroundColor Green
Write-Host ""
Write-Host "Open your browser to: http://localhost:8000" -ForegroundColor Cyan
Write-Host ""
Write-Host "To stop the server: Press Ctrl+C" -ForegroundColor Yellow
Write-Host ""
Write-Host "============================================================" -ForegroundColor Green
Write-Host ""

Set-Location $projectDir
$phpExe = Join-Path $phpDir "php.exe"
& $phpExe -S localhost:8000
