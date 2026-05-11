#!/bin/bash

# Rescue Coordination System - Quick Start Script for Linux/Mac

echo ""
echo "============================================"
echo "Rescue Coordination System - Setup"
echo "============================================"
echo ""

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "ERROR: PHP is not installed"
    echo "Install PHP with: sudo apt install php php-mysql php-cli"
    exit 1
fi

# Check if MySQL/MariaDB is installed
if ! command -v mysql &> /dev/null; then
    echo "WARNING: MySQL not found (optional - you may need to import database manually)"
fi

echo "[1/4] Checking directories..."
mkdir -p assets/css
mkdir -p assets/js
mkdir -p assets/images
echo "Directory structure verified!"

echo ""
echo "[2/4] Checking configuration..."
if [ ! -f config/database.php ]; then
    echo "ERROR: config/database.php not found!"
    exit 1
fi
echo "Configuration verified!"

echo ""
echo "[3/4] Setting directory permissions..."
chmod 755 assets
chmod 755 assets/css
chmod 755 assets/js
chmod 755 assets/images
chmod 755 api
chmod 755 config
chmod 755 includes
echo "Permissions set!"

echo ""
echo "[4/4] Database setup instructions"
echo ""
echo "To import the database:"
echo "1. Ensure MySQL is running: sudo service mysql start"
echo "2. Create database: mysql -u root -p -e 'CREATE DATABASE IF NOT EXISTS rescue_coordination;'"
echo "3. Import schema: mysql -u root -p rescue_coordination < database/schema.sql"
echo ""

echo ""
echo "============================================"
echo "Starting PHP Development Server..."
echo "============================================"
echo ""
echo "Access the application at: http://localhost:8000"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""

php -S localhost:8000
