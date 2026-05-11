# Rescue Coordination System

A comprehensive platform for coordinating rescue agencies during natural and man-made disasters. Complete application with automated setup.

---

## ⚡ QUICK START (3 Steps - 30 Minutes)

### Step 1: Run Setup (First Time Only)
```batch
Double-click: INSTALL_ALL.bat
Wait: 15-20 minutes for automatic downloads and installation
```

### Step 2: Start Application
```batch
Double-click: RUN.bat (or START.bat)
Wait: 2-3 seconds for servers to start
```

### Step 3: Open Application
```
Open browser: http://localhost:8000
Register: Create your agency account
Start: Using the system!
```

---

## 📋 COMPLETE INSTALLATION GUIDE

### Prerequisites
- **Windows, Linux, or Mac** with internet connection
- **~250 MB free disk space**
- **Browser** (Chrome, Firefox, Edge, Safari)
- **No admin rights needed** (uses portable versions)

### Method 1: Automatic Setup (Recommended) ⭐

#### Windows Batch
```batch
cd C:\Users\Uday Shankar Pandey\Downloads\rescue_coordination
INSTALL_ALL.bat
```

What it does:
- Downloads PHP 8.2 (~30 MB)
- Downloads MariaDB 10.6 (~200 MB)
- Configures everything automatically
- Creates and initializes database
- Ready in 15-20 minutes

#### PowerShell
```powershell
cd "C:\Users\Uday Shankar Pandey\Downloads\rescue_coordination"
Set-ExecutionPolicy -ExecutionPolicy Bypass -Scope CurrentUser
.\SETUP.ps1 -AutoStart
```

### Method 2: Manual Setup (If Automatic Fails)

#### Step 1: Download PHP
1. Visit: https://windows.php.net/downloads/releases/
2. Download: **php-8.2.12-Win32-vs16-x64.zip**
3. Create folder: `server-env\php\`
4. Extract ZIP into that folder

#### Step 2: Download MariaDB
1. Visit: https://mariadb.org/download/
2. Download: **mariadb-10.6.15-winx64.zip**
3. Create folder: `server-env\mariadb\`
4. Extract ZIP into that folder
5. Create folder: `server-env\mariadb\data\`

#### Step 3: Initialize Database
```cmd
cd server-env\mariadb\bin
mysqld --initialize-insecure --datadir=..\data --skip-innodb
```

#### Step 4: Import Schema
```cmd
cd rescue_coordination\rescue_coordination\database
mysql -u root < schema.sql
```

#### Step 5: Start Services

**Terminal 1 - MariaDB:**
```cmd
cd server-env\mariadb\bin
mysqld --datadir=..\data --port=3306
```

**Terminal 2 - PHP:**
```cmd
cd rescue_coordination\rescue_coordination
php -S localhost:8000
```

---

## 🎯 FIRST-TIME USAGE

### Create Your First Account
1. Go to: `http://localhost:8000`
2. Click: **"Register Your Agency"**
3. Fill in:
   - **Agency Name:** Your organization name
   - **Agency Type:** Choose from list (Fire, Medical, Police, Military, NGO, Other)
   - **Email:** any@example.com
   - **Password:** Minimum 8 characters
   - **Phone:** Any format
   - **Address, City, State, Country:** Any values
4. Click: **"Register"**
5. Result: Auto-verified in development mode
6. Go to: **"Login"** with your credentials

### Explore Features
- **Dashboard:** View active disasters, agencies, resources
- **Disasters:** Report new disasters, view active ones, search
- **Agencies:** Browse all registered rescue organizations
- **Resources:** Manage equipment and supplies
- **Map:** See real-time agency locations
- **Reports:** Generate disaster response analytics

---

## 🔧 SYSTEM REQUIREMENTS

### Runtime Environment
- **PHP:** 8.2 (included in setup)
- **Database:** MariaDB 10.6 (included in setup)
- **RAM:** 512 MB minimum
- **CPU:** Any processor
- **Internet:** Required for first setup only

### Browser Requirements
- **Chrome** 90+
- **Firefox** 88+
- **Safari** 14+
- **Edge** 90+
- JavaScript enabled

### Operating Systems
- Windows 10/11
- Linux (any distribution)
- macOS 10.13+

---

## 📁 PROJECT STRUCTURE

```
rescue_coordination/
├── server-env/                      [Created by setup]
│   ├── php/                         PHP 8.2 portable
│   ├── mariadb/                     MariaDB 10.6 portable
│   └── data/                        Database files
│
├── rescue_coordination/
│   └── rescue_coordination/         [Main application]
│       ├── config/
│       │   ├── config.php           Application configuration
│       │   └── database.php         Database connection
│       ├── database/
│       │   └── schema.sql           Database schema
│       ├── includes/
│       │   ├── auth.php             Authentication functions
│       │   ├── functions.php        Utility functions
│       │   ├── header.php           HTML header template
│       │   └── footer.php           HTML footer template
│       ├── assets/
│       │   ├── css/                 Stylesheets
│       │   ├── js/                  JavaScript files
│       │   └── images/              Image assets
│       ├── services/                Service classes
│       ├── index.php                Homepage
│       ├── login.php                User login
│       ├── register.php             Agency registration
│       ├── logout.php               Session termination
│       ├── agencies.php             Agency directory
│       ├── resources.php            Resource management
│       ├── disasters.php            Disaster listing
│       ├── disaster-details.php     Disaster details
│       ├── report-disaster.php      Report disaster
│       ├── setup-database.php       Database setup utility
│       └── .env                     Environment configuration
│
├── START.bat                        Quick start (daily use)
├── RUN.bat                          Alternative start script
├── INSTALL_ALL.bat                  Setup script (first time)
├── SETUP_DATABASE.bat               Database initialization
├── README.md                        This file
└── start.sh                         Linux startup
```

---

## 💾 DATABASE INFORMATION

### Overview
Your `rescue_coordination` database is automatically initialized with 10 tables and all relationships imported from the schema. The application securely loads your database credentials and API keys via a local `.env` file instead of hardcoding them in the PHP files.

### Database Connection Mechanism
1. **Configuration:** Stored securely in `rescue_coordination/rescue_coordination/.env`.
2. **Parser:** `config.php` loads the `.env` variables and acts as a secure fallback.
3. **Database Connection:** `database.php` (`getDbConnection()`) creates a secure PDO connection to MariaDB using these loaded variables.

### Database Structure
The system includes these tables:
- **agencies** - Registered rescue agencies (Fire, Police, Medical, etc.)
- **disasters** - Reported natural and man-made disasters
- **agency_locations** - GPS tracking of agencies
- **resources** - Resources available at each agency (vehicles, equipment, personnel)
- **resource_requests** - Requests for resources during disasters
- **communications** - Messages between agencies
- **alerts** - System alerts and notifications
- **ai_predictions** - AI-generated disaster predictions
- **alert_recipients** - Recipients for alert broadcasts
- **communication_recipients** - Recipients for messages

### Connection Details
```
Host:     localhost
Port:     3306
Username: root
Password: (Configured in your .env file)
Database: rescue_coordination
```

---

## ✨ FEATURES

### Authentication & Management
- ✅ Agency registration and verification
- ✅ Secure login with session management
- ✅ Auto-verification in development mode
- ✅ 24-hour session timeout
- ✅ Bcrypt password hashing

### Disaster Management
- ✅ Report new disasters with GPS coordinates
- ✅ Classify by type and severity
- ✅ Real-time status tracking
- ✅ Search and filter capabilities
- ✅ Disaster detail views

### Agency System
- ✅ Complete agency directory
- ✅ Filter by agency type
- ✅ Verification status tracking
- ✅ Last activity monitoring
- ✅ Location sharing

### Resource Management
- ✅ Track available resources
- ✅ Resource request system
- ✅ Request fulfillment tracking
- ✅ Inventory management
- ✅ Resource allocation

### Advanced Features
- ✅ Real-time interactive mapping (Leaflet.js)
- ✅ Search and filtering
- ✅ Pagination (10-15 items per page)
- ✅ Responsive design (mobile-friendly)
- ✅ Dashboard with statistics

### Security
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS protection (HTML escaping)
- ✅ CSRF token ready
- ✅ Password validation
- ✅ Session management
- ✅ Input sanitization

---

## 🚀 STARTUP PROCEDURES

### Daily Startup (After Setup Complete)
```batch
1. Navigate to: C:\Users\Uday Shankar Pandey\Downloads\rescue_coordination
2. Double-click: RUN.bat (or START.bat)
3. Wait 3 seconds
4. App automatically opens: http://localhost:8000/index.php
```

### What RUN.bat Does
1. Starts MariaDB securely on port 3306
2. Waits for database initialization
3. Starts PHP on port 8000
4. Automatically opens the application in your browser
5. Ctrl+C to stop both services

### Custom Port Usage
```bash
# Use different PHP port if 8000 busy
php -S localhost:8001

# Use different MariaDB port
mysqld --port=3307
```

---

## ⚠️ TROUBLESHOOTING

### "Database connection error" on Login/Register
**Cause:** The application cannot connect to the MariaDB server.
**Solutions:**
1. **Verify MariaDB is Running:**
   - Check Windows Services (`services.msc`) and ensure "MariaDB" is running, or run `Get-Service -Name MariaDB` in PowerShell.
2. **Check Database Credentials:**
   - Open `rescue_coordination/rescue_coordination/.env` and ensure `DB_PASS` matches your actual MariaDB root password.
3. **Run the Database Setup Script:**
   - Go to `http://localhost:8000/setup-database.php` or double-click `SETUP_DATABASE.bat` to ensure all tables are created.

### "Could not find driver" Error
**Cause:** The PHP PDO extension is disabled in your PHP configuration.
**Solution:**
Ensure you have `php.ini` configured properly in `server-env\php\` with `extension_dir` set to the absolute path of the `ext` folder and `extension=pdo_mysql` enabled. *(This is automatically applied if you used the latest setup).* Restart `RUN.bat` for changes to take effect.

### "Access denied for user 'root'@'localhost'"
**Cause:** The password provided in `.env` or `config/database.php` is incorrect.
**Solution:** Update the password in your `.env` file to match your MariaDB setup. If you forgot your MariaDB password, you can reset it or reinstall MariaDB.

### "Port already in use" Error
**Cause:** Service already running from previous session.
**Fix:** 
```batch
taskkill /IM mysqld.exe /F
taskkill /IM php.exe /F
```
Wait 5 seconds and try `RUN.bat` again.

### "Blank white page" or PHP errors
**Cause:** PHP error occurred.
**Fix:** 
1. Press F12 to open browser console.
2. Check error messages.
3. Ensure `error_reporting` is enabled locally.

### Setup Downloads Slow/Fail
**Cause:** Internet connection issues.
**Fix:** Try `SETUP.ps1` instead of the batch file, or use manual download methods as listed in the Complete Installation Guide.

---

## 📊 TECHNOLOGY STACK

| Layer | Technology |
|-------|-----------|
| **Frontend** | HTML5, Tailwind CSS, JavaScript |
| **Backend** | PHP 8.2 |
| **Database** | MariaDB 10.6 (MySQL 5.7+ compatible) |
| **Mapping** | Leaflet.js with OpenStreetMap |
| **Icons** | Font Awesome 6.4.0 |
| **Security** | PDO, Bcrypt, HTML escaping |

---

## 🆘 GETTING HELP

### Documentation
- This README covers 90% of use cases.
- Check the Troubleshooting section above.
- Review inline code comments.

### External Resources
- **PHP Documentation:** https://www.php.net/docs.php
- **MariaDB Help:** https://mariadb.com/kb/en/
- **Tailwind CSS:** https://tailwindcss.com/docs
- **Leaflet Maps:** https://leafletjs.com/reference.html

---

## 📄 LICENSE

MIT - Open Source

---

**Enjoy your Rescue Coordination System! 🚀**
