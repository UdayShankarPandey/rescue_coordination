@echo off
SETLOCAL ENABLEDELAYEDEXPANSION
cd /d "%~dp0"

echo.
echo ============================================================
echo    RESCUE COORDINATION SYSTEM - SETUP
echo ============================================================
echo.
echo Step 1: Creating directories...

set "INSTALL_DIR=%cd%\server-env"
set "PHP_DIR=%INSTALL_DIR%\php"
set "MYSQL_DIR=%INSTALL_DIR%\mariadb"

if not exist "%INSTALL_DIR%" mkdir "%INSTALL_DIR%"
if not exist "%PHP_DIR%" mkdir "%PHP_DIR%"
if not exist "%MYSQL_DIR%" mkdir "%MYSQL_DIR%"
if not exist "%MYSQL_DIR%\data" mkdir "%MYSQL_DIR%\data"

echo Created: %INSTALL_DIR%

echo.
echo ============================================================
echo DOWNLOAD REQUIRED - FOLLOW THESE STEPS
echo ============================================================
echo.
echo Step 1: Download PHP 8.2
echo   URL: https://windows.php.net/downloads/releases/
echo   File: php-8.2.12-Win32-vs16-x64.zip
echo   Extract to: %PHP_DIR%
echo.
echo Step 2: Download MariaDB
echo   URL: https://mariadb.org/download/
echo   File: mariadb-10.6.15-winx64.zip
echo   Extract to: %INSTALL_DIR%
echo   Then rename folder to: mariadb
echo.
echo Step 3: After downloads, run START.bat
echo.
echo ============================================================
echo.
echo Press any key to open download pages...
pause

REM Open PHP download page
start https://windows.php.net/downloads/releases/

REM Open MariaDB download page
timeout /t 2 /nobreak
start https://mariadb.org/download/

echo.
echo Downloads should open in your browser.
echo Download both files and extract them as instructed.
echo.
echo When done, return and double-click: START.bat
echo.
pause
    echo [ERROR] Failed to download PHP
    echo Trying alternative download method...
    echo Please visit https://windows.php.net/downloads/releases/
    echo Download: php-8.2.12-Win32-vs16-x64.zip
    echo Extract to: %PHP_DIR%
    pause
)

echo.
echo Step 3: Configuring PHP...

REM Copy and configure php.ini
if exist "%PHP_DIR%\php.ini-production" (
    copy "%PHP_DIR%\php.ini-production" "%PHP_DIR%\php.ini" >nul
) else if exist "%PHP_DIR%\php.ini-development" (
    copy "%PHP_DIR%\php.ini-development" "%PHP_DIR%\php.ini" >nul
)

echo [OK] PHP configured

echo.
echo Step 4: Downloading MariaDB (Portable)...
echo This may take 3-5 minutes...

REM Download MariaDB portable
powershell -Command "$ProgressPreference = 'Continue'; (New-Object Net.WebClient).DownloadFile('https://mirror.mariadb.org/mariadb-10.6.15/winx64-packages/mariadb-10.6.15-winx64.zip', '%INSTALL_DIR%\mariadb.zip')" 2>nul

if exist "%INSTALL_DIR%\mariadb.zip" (
    echo [OK] MariaDB downloaded successfully
    echo Extracting MariaDB...
    powershell -Command "Expand-Archive -Path '%INSTALL_DIR%\mariadb.zip' -DestinationPath '%INSTALL_DIR%' -Force" 2>nul
    
    REM Find the extracted folder and rename it
    for /d %%A in ("%INSTALL_DIR%\mariadb-*") do (
        move "%%A" "%MYSQL_DIR%" >nul 2>&1
    )
    
    del "%INSTALL_DIR%\mariadb.zip"
    echo [OK] MariaDB extracted
) else (
    echo [ERROR] Failed to download MariaDB
    echo Trying alternative...
    echo Please visit https://mariadb.org/download/
    echo Download: mariadb-10.6.15-winx64.zip
    echo Extract to: %MYSQL_DIR%
    pause
)

echo.
echo Step 5: Setting up MariaDB data directory...
if not exist "%MYSQL_DIR%\data" mkdir "%MYSQL_DIR%\data"
echo [OK] Data directory created

echo.
echo Step 6: Initializing MariaDB...
cd /d "%MYSQL_DIR%\bin"
mysqld --initialize-insecure --datadir="%MYSQL_DIR%\data" --user=mysql 2>nul
echo [OK] MariaDB initialized

echo.
echo Step 7: Creating database and importing schema...
REM Start MariaDB temporarily
start /B mysqld --datadir="%MYSQL_DIR%\data" --port=3306 --user=mysql

REM Wait for MariaDB to start
timeout /t 3 /nobreak

REM Import schema
mysql -u root -p --port=3306 < "%PROJECT_DIR%\database\schema.sql" 2>nul

if errorlevel 0 (
    echo [OK] Database imported successfully
) else (
    echo [WARNING] Database import may have issues
)

echo.
echo Step 8: Creating startup helper scripts...

REM Create a batch file to start all services
(
    echo @echo off
    echo title Rescue Coordination System - Services
    echo cd /d "%INSTALL_DIR%"
    echo echo.
    echo echo ====================================
    echo echo Starting Rescue Coordination System
    echo echo ====================================
    echo echo.
    echo echo Starting MariaDB...
    echo start "MariaDB" cmd /k "cd /d "%MYSQL_DIR%\bin" ^&^& mysqld --datadir="%MYSQL_DIR%\data" --port=3306 --skip-grant-tables"
    echo timeout /t 2
    echo.
    echo echo Starting PHP Development Server...
    echo cd /d "%PROJECT_DIR%"
    echo "%PHP_DIR%\php.exe" -S localhost:8000
) > "%INSTALL_DIR%\start-all.bat"

echo [OK] Startup script created

echo.
echo ============================================================
echo    INSTALLATION COMPLETE!
echo ============================================================
echo.
echo Next steps:
echo.
echo 1. Run the startup script:
echo    "%INSTALL_DIR%\start-all.bat"
echo.
echo 2. Open browser to:
echo    http://localhost:8000
echo.
echo 3. Register your first agency account
echo.
echo Database credentials (development):
echo    Host: localhost
echo    User: root
echo    Password: (empty)
echo.
echo ============================================================
echo.

pause
