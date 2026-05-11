@echo off
setlocal enabledelayedexpansion

cd /d "%~dp0"

set "INSTALL_DIR=%cd%\server-env"
set "PHP_DIR=%INSTALL_DIR%\php"
set "MYSQL_DIR=C:\Program Files\MariaDB 12.2"
set "DATA_DIR=%INSTALL_DIR%\mariadb_data"
set "PROJECT_DIR=%cd%\rescue_coordination\rescue_coordination"

echo.
echo ============================================================
echo    RESCUE COORDINATION SYSTEM - SETUP GUIDE
echo ============================================================
echo.

REM Clean up deprecated documentation files
if exist "%cd%\DATABASE_SETUP_COMPLETE.md" del /f /q "%cd%\DATABASE_SETUP_COMPLETE.md"
if exist "%cd%\DATABASE_TROUBLESHOOTING.md" del /f /q "%cd%\DATABASE_TROUBLESHOOTING.md"

REM Create directories first
if not exist "%INSTALL_DIR%" mkdir "%INSTALL_DIR%"
if not exist "%PHP_DIR%" mkdir "%PHP_DIR%"
if not exist "%DATA_DIR%" mkdir "%DATA_DIR%"

REM Check if PHP exists
if exist "%PHP_DIR%\php.exe" (
    goto start_services
)

echo Step 1: Download PHP
echo.
echo 1. Open this link in your browser:
echo    https://windows.php.net/downloads/releases/
echo.
echo 2. Find and download: php-8.2.11-Win32-vs16-x64.zip
echo    (or any recent PHP 8.2 version)
echo.
echo 3. Extract the ZIP file to:
echo    %PHP_DIR%
echo.
echo Press Enter to open the download page, or any other key to skip...
pause

start https://windows.php.net/downloads/releases/

echo.
echo After downloading and extracting PHP, run this script again.
pause
exit /b 0

:start_services
echo.
echo ============================================================
echo    STARTING SERVICES
echo ============================================================
echo.

echo Starting MariaDB on port 3306...
start /min "MariaDB" cmd /k "cd /d "%MYSQL_DIR%\bin" && mysqld --datadir="%DATA_DIR%" --port=3306"

timeout /t 3 /nobreak

echo.
echo Starting PHP development server on port 8000...
echo.
echo ============================================================
echo    APPLICATION READY!
echo ============================================================
echo.
echo Opening http://localhost:8000/index.php in your browser...
echo.
start http://localhost:8000/index.php

timeout /t 2 /nobreak

cd /d "%PROJECT_DIR%"
"%PHP_DIR%\php.exe" -S localhost:8000

pause
