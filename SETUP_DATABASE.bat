@echo off
REM Rescue Coordination System - Database Setup Batch Script
REM This script initializes the MariaDB database with the schema

echo ========================================
echo Rescue Coordination System - DB Setup
echo ========================================
echo.

REM Try to find mysql executable
set "MYSQL_PATH="
if exist "C:\Program Files\MariaDB 10.6\bin\mysql.exe" (
    set "MYSQL_PATH=C:\Program Files\MariaDB 10.6\bin\mysql.exe"
) else if exist "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe" (
    set "MYSQL_PATH=C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe"
) else if exist "C:\MySQL\bin\mysql.exe" (
    set "MYSQL_PATH=C:\MySQL\bin\mysql.exe"
)

if "%MYSQL_PATH%"=="" (
    echo ERROR: MySQL/MariaDB not found!
    echo Please ensure MariaDB/MySQL is installed and in PATH.
    echo.
    echo You can:
    echo 1. Run INSTALL_ALL.bat to install MariaDB
    echo 2. OR manually add MySQL/MariaDB bin folder to PATH
    echo.
    pause
    exit /b 1
)

echo Found MySQL/MariaDB at: %MYSQL_PATH%
echo.

REM Check if MariaDB service is running
echo Checking if MariaDB service is running...
sc query MariaDB >nul 2>&1
if %errorlevel% neq 0 (
    echo.
    echo WARNING: MariaDB service not found or not running!
    echo Please start MariaDB service first:
    echo   - Windows: Services ^(services.msc^) ^> Find MariaDB ^> Start
    echo   - OR run: net start MariaDB
    echo.
    pause
    exit /b 1
)

echo MariaDB service is running.
echo.

REM Create database and import schema
echo Creating database 'rescue_coordination'...
"%MYSQL_PATH%" -u root -e "CREATE DATABASE IF NOT EXISTS rescue_coordination;"

if %errorlevel% neq 0 (
    echo.
    echo ERROR: Failed to create database!
    echo Possible causes:
    echo   1. MariaDB root user requires a password
    echo   2. MariaDB service is not running
    echo.
    echo If root has a password, edit this script and change:
    echo   "%MYSQL_PATH%" -u root -p YOUR_PASSWORD -e "..."
    echo.
    pause
    exit /b 1
)

echo Database 'rescue_coordination' created successfully.
echo.

echo Importing database schema...
"%MYSQL_PATH%" -u root rescue_coordination < "%~dp0rescue_coordination\database\schema.sql"

if %errorlevel% neq 0 (
    echo ERROR: Failed to import schema!
    echo Please check the schema.sql file exists at: %~dp0rescue_coordination\database\schema.sql
    pause
    exit /b 1
)

echo.
echo ========================================
echo ✓ Database setup completed successfully!
echo ========================================
echo.
echo Next steps:
echo 1. Start the application with: START.bat
echo 2. Open browser: http://localhost:8000
echo 3. Register your agency account
echo 4. Login and start using the system
echo.
pause
