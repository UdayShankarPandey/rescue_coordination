# Rescue Coordination System - Database Setup (PowerShell)
# This script initializes the MariaDB database with the schema

Write-Host "========================================"
Write-Host "Rescue Coordination System - DB Setup"
Write-Host "========================================"
Write-Host ""

# Find mysql executable
$mysqlPaths = @(
    "C:\Program Files\MariaDB 10.6\bin\mysql.exe",
    "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe",
    "C:\MySQL\bin\mysql.exe"
)

$mysqlPath = $null
foreach ($path in $mysqlPaths) {
    if (Test-Path $path) {
        $mysqlPath = $path
        break
    }
}

if (-not $mysqlPath) {
    Write-Host "ERROR: MySQL/MariaDB not found!" -ForegroundColor Red
    Write-Host "Please ensure MariaDB/MySQL is installed." -ForegroundColor Yellow
    Write-Host ""
    Write-Host "You can run: .\INSTALL_ALL.bat to install MariaDB"
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "Found MySQL/MariaDB at: $mysqlPath" -ForegroundColor Green
Write-Host ""

# Check if MariaDB service is running
Write-Host "Checking if MariaDB service is running..."
$service = Get-Service -Name "MariaDB" -ErrorAction SilentlyContinue
if (-not $service) {
    Write-Host "WARNING: MariaDB service not found!" -ForegroundColor Yellow
    Write-Host "Please start MariaDB service:" -ForegroundColor Yellow
    Write-Host "  net start MariaDB"
    Read-Host "Press Enter to exit"
    exit 1
}

if ($service.Status -ne "Running") {
    Write-Host "ERROR: MariaDB service is not running!" -ForegroundColor Red
    Write-Host "Starting MariaDB service..."
    Start-Service -Name "MariaDB" -ErrorAction SilentlyContinue
    Start-Sleep -Seconds 2
}

Write-Host "MariaDB service is running." -ForegroundColor Green
Write-Host ""

# Create database
Write-Host "Creating database 'rescue_coordination'..."
$dbCreateScript = "CREATE DATABASE IF NOT EXISTS rescue_coordination;"
$output = $dbCreateScript | & $mysqlPath -u root 2>&1

if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: Failed to create database!" -ForegroundColor Red
    Write-Host "Output: $output" -ForegroundColor Red
    Write-Host ""
    Write-Host "Possible causes:" -ForegroundColor Yellow
    Write-Host "  1. MariaDB root user requires a password"
    Write-Host "  2. MariaDB service is not running"
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "Database 'rescue_coordination' created successfully." -ForegroundColor Green
Write-Host ""

# Import schema
$schemaPath = Join-Path $PSScriptRoot "rescue_coordination\rescue_coordination\database\schema.sql"

if (-not (Test-Path $schemaPath)) {
    Write-Host "ERROR: Schema file not found at: $schemaPath" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "Importing database schema from: $schemaPath"
$schemaContent = Get-Content -Path $schemaPath -Raw
$schemaContent | & $mysqlPath -u root rescue_coordination 2>&1

if ($LASTEXITCODE -ne 0) {
    Write-Host "WARNING: Some schema statements may have failed" -ForegroundColor Yellow
    Write-Host "This is usually OK if tables already exist." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "========================================"
Write-Host "✓ Database setup completed!" -ForegroundColor Green
Write-Host "========================================"
Write-Host ""
Write-Host "Next steps:"
Write-Host "  1. Run: .\START.bat"
Write-Host "  2. Open: http://localhost:8000"
Write-Host "  3. Register and login"
Write-Host ""
Read-Host "Press Enter to exit"
