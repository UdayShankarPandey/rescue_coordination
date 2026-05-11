@echo off
REM Rescue Coordination System - Quick Start
REM This script starts MariaDB and PHP development server

setlocal enabledelayedexpansion

cd /d "%~dp0"

set "INSTALL_DIR=%cd%\server-env"
set "PHP_DIR=%INSTALL_DIR%\php"
set "MYSQL_DIR=%INSTALL_DIR%\mariadb"
set "DATA_DIR=%MYSQL_DIR%\data"
set "PROJECT_DIR=%cd%\rescue_coordination\rescue_coordination"

echo.
echo ====================================
echo  Rescue Coordination System
echo ====================================
echo.

REM Check if PHP exists
if not exist "%PHP_DIR%\php.exe" (
    echo ERROR: PHP not found!
    echo.
    echo Please follow these steps:
    echo.
    echo 1. Download PHP 8.2:
    echo    https://windows.php.net/downloads/releases/
    echo    File: php-8.2.12-Win32-vs16-x64.zip
    echo.
    echo 2. Create folder: server-env\php
    echo.
    echo 3. Extract PHP ZIP into: %PHP_DIR%
    echo.
    echo 4. Then run this script again.
    echo.
    pause
    exit /b 1
)

REM Check if MariaDB exists
if not exist "%MYSQL_DIR%\bin\mysqld.exe" (
    echo ERROR: MariaDB not found!
    echo.
    echo Please follow these steps:
    echo.
    echo 1. Download MariaDB:
    echo    https://mariadb.org/download/
    echo    File: mariadb-10.6.15-winx64.zip
    echo.
    echo 2. Create folder: server-env
    echo.
    echo 3. Extract MariaDB ZIP into: %INSTALL_DIR%
    echo.
    echo 4. Rename extracted folder to: mariadb
    echo.
    echo 5. Create folder: server-env\mariadb\data
    echo.
    echo 6. Then run this script again.
    echo.
    pause
    exit /b 1
)

echo Starting services...
echo.
echo Starting MariaDB database...
start "MariaDB" cmd /k "cd /d "%MYSQL_DIR%\bin" && mysqld --datadir="%DATA_DIR%" --port=3306"

REM Wait for MariaDB to start
timeout /t 3 /nobreak

echo Starting PHP web server...
echo.
echo Open browser to: http://localhost:8000
echo.
echo Press Ctrl+C to stop both services
echo.

cd /d "%PROJECT_DIR%"
"%PHP_DIR%\php.exe" -S localhost:8000

pause
