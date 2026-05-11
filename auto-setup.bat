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
set "PHP_URL=https://windows.php.net/downloads/releases/php-8.2.12-Win32-vs16-x64.zip"
set "MARIADB_URL=https://mirror.mariadb.org/mariadb-10.6.15/winx64-packages/mariadb-10.6.15-winx64.zip"

echo [2/5] Downloading PHP 8.2 (this may take 2-3 minutes)...
echo Trying with curl...
curl -L -o "%PHP_ZIP%" "%PHP_URL%" >nul 2>&1
if exist "%PHP_ZIP%" (
    echo        [OK] PHP downloaded
    goto php_done
)

echo Trying with PowerShell...
powershell -Command "Invoke-WebRequest -Uri '%PHP_URL%' -OutFile '%PHP_ZIP%' -UseBasicParsing" >nul 2>&1
if exist "%PHP_ZIP%" (
    echo        [OK] PHP downloaded
    goto php_done
)

echo Trying with VBScript...
cscript.exe "%~dp0download.vbs" "%PHP_URL%" "%PHP_ZIP%" "%MARIADB_URL%" "%MARIADB_ZIP%" >nul 2>&1
if exist "%PHP_ZIP%" (
    echo        [OK] PHP downloaded
    if exist "%MARIADB_ZIP%" (
        echo        [OK] MariaDB downloaded
        goto both_done
    )
    goto php_done
)

echo        [ERROR] Failed to download PHP
echo.
echo Please download manually:
echo   PHP: %PHP_URL%
echo   Save to: %PHP_ZIP%
echo.
pause
exit /b 1

:php_done
echo.

echo [3/5] Downloading MariaDB 10.6 (this may take 3-5 minutes)...
echo Trying with curl...
curl -L -o "%MARIADB_ZIP%" "%MARIADB_URL%" >nul 2>&1
if exist "%MARIADB_ZIP%" (
    echo        [OK] MariaDB downloaded
    goto both_done
)

echo Trying with PowerShell...
powershell -Command "Invoke-WebRequest -Uri '%MARIADB_URL%' -OutFile '%MARIADB_ZIP%' -UseBasicParsing" >nul 2>&1
if exist "%MARIADB_ZIP%" (
    echo        [OK] MariaDB downloaded
    goto both_done
)

echo        [ERROR] Failed to download MariaDB
echo.
echo Please download manually:
echo   MariaDB: %MARIADB_URL%
echo   Save to: %MARIADB_ZIP%
echo.
pause
exit /b 1

:both_done
echo.

echo [4/5] Extracting PHP...
powershell -Command "Expand-Archive -Path '%PHP_ZIP%' -DestinationPath '%PHP_DIR%' -Force; Remove-Item '%PHP_ZIP%' -Force"
echo        [OK] PHP extracted
echo.

echo [5/5] Extracting MariaDB...
powershell -Command "$f = Get-ChildItem -Path '%INSTALL_DIR%' -Filter 'mariadb*.zip' | Select-Object -First 1; Expand-Archive -Path $f.FullName -DestinationPath '%INSTALL_DIR%\mariadb_temp' -Force; $d = Get-ChildItem -Path '%INSTALL_DIR%\mariadb_temp' -Directory | Select-Object -First 1; Copy-Item -Path $d.FullName\* -Destination '%MYSQL_DIR%' -Recurse -Force; Remove-Item '%INSTALL_DIR%\mariadb_temp' -Recurse -Force; Remove-Item $f.FullName -Force"
echo        [OK] MariaDB extracted
echo.

echo ============================================================
echo Verifying installations...
echo ============================================================
echo.

if not exist "%PHP_DIR%\php.exe" (
    echo [ERROR] PHP not found
    pause
    exit /b 1
) else (
    echo        ✓ PHP found
)

if not exist "%MYSQL_DIR%\bin\mysqld.exe" (
    echo [ERROR] MariaDB not found
    pause
    exit /b 1
) else (
    echo        ✓ MariaDB found
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
