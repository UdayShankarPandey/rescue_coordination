@echo off
setlocal

echo ===================================================
echo   GitHub Repository Push Script
echo ===================================================
echo.

REM Check if git is installed
git --version >nul 2>&1
if %errorlevel% neq 0 (
    echo Error: Git is not installed or not in your PATH.
    echo Please install Git from https://git-scm.com/
    pause
    exit /b 1
)

REM Initialize git if not already initialized
if not exist ".git" (
    echo Initializing new Git repository...
    git init
    if %errorlevel% neq 0 goto error
)

REM Add all files
echo Adding files to Git...
git add .
if %errorlevel% neq 0 goto error

REM Check if there are changes to commit
git status | findstr "nothing to commit" >nul 2>&1
if %errorlevel% equ 0 (
    echo No new changes to commit.
) else (
    echo Committing changes...
    git commit -m "Initial commit for Rescue Coordination System"
    if %errorlevel% neq 0 goto error
)

echo.
echo ===================================================
echo Please create a new empty repository on GitHub.
echo Go to: https://github.com/new
echo ===================================================
echo.
set /p repo_url="Enter the GitHub repository URL (e.g., https://github.com/username/repo.name.git): "

if "%repo_url%"=="" (
    echo No URL provided. Exiting...
    pause
    exit /b 1
)

REM Check if remote origin already exists and remove it if necessary
git remote get-url origin >nul 2>&1
if %errorlevel% equ 0 (
    echo Updating existing remote 'origin'...
    git remote set-url origin %repo_url%
) else (
    echo Adding remote 'origin'...
    git remote add origin %repo_url%
)

echo.
echo Pushing code to GitHub...
git branch -M main
git push -u origin main
if %errorlevel% neq 0 goto error

echo.
echo ===================================================
echo Success! Your project has been pushed to GitHub.
echo ===================================================
pause
exit /b 0

:error
echo.
echo An error occurred during the Git process.
pause
exit /b 1
