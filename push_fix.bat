@echo off
echo Adding files to git...
git add .

echo Committing files...
git commit -m "Initial commit for Rescue Coordination System"

echo Ensuring branch is main...
git branch -M main

echo Pushing to GitHub...
git push -u origin main

echo.
echo ===================================================
echo If you saw success messages above, your code is pushed!
echo If you got an error, please share it.
echo ===================================================
pause
