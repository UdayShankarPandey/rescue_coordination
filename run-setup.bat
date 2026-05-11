@echo off
setlocal enabledelayedexpansion

cd /d "%~dp0"

set "INSTALL_DIR=%cd%\server-env"
set "PHP_DIR=%INSTALL_DIR%\php"
set "MYSQL_DIR=%INSTALL_DIR%\mariadb"
set "DATA_DIR=%MYSQL_DIR%\data"
set "PROJECT_DIR=%cd%\rescue_coordination\rescue_coordination"

echo.
echo ============================================================
echo    RESCUE COORDINATION SYSTEM - AUTOMATED SETUP
echo ============================================================
echo.

echo [1/5] Creating directories...
if not exist "%INSTALL_DIR%" mkdir "%INSTALL_DIR%"
if not exist "%PHP_DIR%" mkdir "%PHP_DIR%"
if not exist "%MYSQL_DIR%" mkdir "%MYSQL_DIR%"
if not exist "%DATA_DIR%" mkdir "%DATA_DIR%"
echo        [OK] Directories created
echo.

set "PHP_ZIP=%INSTALL_DIR%\php.zip"
set "MARIADB_ZIP=%INSTALL_DIR%\mariadb.zip"

echo [2/5] Downloading PHP 8.2 (this may take 2-3 minutes)...
curl.exe --location --url "https://windows.php.net/downloads/releases/php-8.2.12-Win32-vs16-x64.zip" --output "%PHP_ZIP%" --compressed --silent
if exist "%PHP_ZIP%" (
    echo        [OK] PHP downloaded
) else (
    echo        [ERROR] Failed to download PHP
    echo.
    echo Please download manually from:
    echo https://windows.php.net/downloads/releases/
    echo File: php-8.2.12-Win32-vs16-x64.zip
    echo Extract to: %PHP_DIR%
    echo.
    pause
    exit /b 1
)
echo.

echo [3/5] Downloading MariaDB 10.6 (this may take 3-5 minutes)...
curl.exe --location --url "https://mirror.mariadb.org/mariadb-10.6.15/winx64-packages/mariadb-10.6.15-winx64.zip" --output "%MARIADB_ZIP%" --compressed --silent
if exist "%MARIADB_ZIP%" (
    echo        [OK] MariaDB downloaded
) else (
    echo        [ERROR] Failed to download MariaDB
    echo.
    echo Please download manually from:
    echo https://mariadb.org/download/
    echo File: mariadb-10.6.15-winx64.zip
    echo Extract to: %INSTALL_DIR%
    echo.
    pause
    exit /b 1
)
echo.

echo [4/5] Extracting PHP...
powershell -NoProfile -Command "Expand-Archive -Path '%PHP_ZIP%' -DestinationPath '%PHP_DIR%' -Force" >nul 2>&1
if exist "%PHP_DIR%\php.exe" (
    echo        [OK] PHP extracted
    del "%PHP_ZIP%"
) else (
    echo        [ERROR] PHP extraction failed
    pause
    exit /b 1
)
echo.

echo [5/5] Extracting MariaDB...
powershell -NoProfile -Command "Expand-Archive -Path '%MARIADB_ZIP%' -DestinationPath '%INSTALL_DIR%\mariadb_temp' -Force" >nul 2>&1

for /d %%A in ("%INSTALL_DIR%\mariadb_temp\*") do (
    move "%%A" "%MYSQL_DIR%" >nul 2>&1
)
rmdir "%INSTALL_DIR%\mariadb_temp" /s /q >nul 2>&1
del "%MARIADB_ZIP%"

if exist "%MYSQL_DIR%\bin\mysqld.exe" (
    echo        [OK] MariaDB extracted
) else (
    echo        [ERROR] MariaDB extraction failed
    pause
    exit /b 1
)
echo.

echo ============================================================
echo Verifying installations...
echo ============================================================
echo.

if exist "%PHP_DIR%\php.exe" (
    echo        ✓ PHP found
) else (
    echo        ✗ PHP not found
    pause
    exit /b 1
)

if exist "%MYSQL_DIR%\bin\mysqld.exe" (
    echo        ✓ MariaDB found
) else (
    echo        ✗ MariaDB not found
    pause
    exit /b 1
)

echo.
echo ============================================================
echo Starting services...
echo ============================================================
echo.

echo Starting MariaDB database...
start "MariaDB" cmd /k "cd /d "%MYSQL_DIR%\bin" && mysqld --datadir="%DATA_DIR%" --port=3306 --skip-grant-tables"

timeout /t 3 /nobreak

echo.
echo Starting PHP development server...
echo.
echo ============================================================
echo RESCUE COORDINATION SYSTEM - APPLICATION READY
echo ============================================================
echo.
echo Open your browser to: http://localhost:8000
echo.
echo To stop the server: Press Ctrl+C
echo.
echo ============================================================
echo.

cd /d "%PROJECT_DIR%"
"%PHP_DIR%\php.exe" -S localhost:8000
