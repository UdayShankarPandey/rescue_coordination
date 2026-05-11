@echo off
REM Rescue Coordination System - Quick Start Script for Windows

echo.
echo ============================================
echo Rescue Coordination System - Setup
echo ============================================
echo.

REM Check if PHP is installed
php -v >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: PHP is not installed or not in PATH
    echo Please install PHP and add it to your system PATH
    pause
    exit /b 1
)

REM Check if MySQL is installed
mysql -u root --version >nul 2>&1
if %errorlevel% neq 0 (
    echo WARNING: MySQL not found in PATH (optional - you may need to import database manually)
)

echo [1/4] Checking directories...
if not exist assets (
    mkdir assets
    mkdir assets\css
    mkdir assets\js
    mkdir assets\images
)
echo Directory structure verified!

echo.
echo [2/4] Checking configuration...
if not exist config\database.php (
    echo ERROR: config\database.php not found!
    pause
    exit /b 1
)
echo Configuration verified!

echo.
echo [3/4] Setting directory permissions...
REM Windows doesn't require explicit chmod, but ensure IUSR has access
echo Permissions set (Windows auto-configures)

echo.
echo [4/4] Import database schema (Optional)
echo.
echo To import the database:
echo 1. Open MySQL Command Line or phpMyAdmin
echo 2. Run: mysql -u root -p rescue_coordination ^< database\schema.sql
echo 3. Or import database\schema.sql through phpMyAdmin
echo.

echo.
echo ============================================
echo Starting PHP Development Server...
echo ============================================
echo.
echo Access the application at: http://localhost:8000
echo.
echo Press Ctrl+C to stop the server
echo.

php -S localhost:8000

pause
