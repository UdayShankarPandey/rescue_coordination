@echo off
echo Removing .env from Git tracking (keeping the file on your PC)...
git rm --cached "rescue_coordination\rescue_coordination\.env"

echo Adding .gitignore to track the ignore rules...
git add .gitignore

echo Committing the changes...
git commit -m "Stop tracking .env file for security and add .gitignore"

echo Pushing to GitHub...
git push origin main

echo.
echo ===================================================
echo Done! Your .env file is now hidden and removed from GitHub.
echo (It is still safe on your local computer!)
echo ===================================================
pause
