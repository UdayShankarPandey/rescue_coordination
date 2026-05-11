@echo off
echo Removing the massive server-env folder (local database and PHP binaries) from Git tracking...
echo (Don't worry, the actual files will STAY on your computer, they just won't be uploaded to GitHub anymore)
git rm -r --cached "server-env"

echo Adding the updated .gitignore...
git add .gitignore

echo Committing the removal of large files...
git commit -m "Stop tracking local server binaries and database data"

echo Pushing the clean-up to GitHub...
git push origin main

echo.
echo ===================================================
echo Done! The massive database/PHP files are no longer tracked by Git.
echo This will make your repository much faster and cleaner.
echo ===================================================
pause
