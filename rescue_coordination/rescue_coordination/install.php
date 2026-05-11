#!/usr/bin/env php
<?php
/**
 * Rescue Coordination System - Installation Script
 * Run this script once to set up the application
 */

define('BASE_PATH', __DIR__);

// Colors for console output
class ConsoleColor {
    const RESET = "\033[0m";
    const RED = "\033[31m";
    const GREEN = "\033[32m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const CYAN = "\033[36m";
}

function print_header($text) {
    echo "\n" . ConsoleColor::CYAN . "============================================" . ConsoleColor::RESET . "\n";
    echo ConsoleColor::CYAN . $text . ConsoleColor::RESET . "\n";
    echo ConsoleColor::CYAN . "============================================" . ConsoleColor::RESET . "\n\n";
}

function print_success($text) {
    echo ConsoleColor::GREEN . "✓ " . $text . ConsoleColor::RESET . "\n";
}

function print_error($text) {
    echo ConsoleColor::RED . "✗ " . $text . ConsoleColor::RESET . "\n";
}

function print_info($text) {
    echo ConsoleColor::BLUE . "ℹ " . $text . ConsoleColor::RESET . "\n";
}

function print_warning($text) {
    echo ConsoleColor::YELLOW . "⚠ " . $text . ConsoleColor::RESET . "\n";
}

// Start installation
print_header("Rescue Coordination System - Installation");

// Step 1: Check PHP version
echo "Step 1: Checking PHP version...\n";
$php_version = phpversion();
if (version_compare($php_version, '7.4.0', '>=')) {
    print_success("PHP version $php_version is compatible");
} else {
    print_error("PHP version $php_version is not compatible (requires 7.4+)");
    exit(1);
}

// Step 2: Check required extensions
echo "\nStep 2: Checking required PHP extensions...\n";
$required_extensions = ['pdo', 'pdo_mysql', 'curl', 'json', 'gd'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        print_success("Extension: $ext");
    } else {
        print_warning("Missing extension: $ext");
        $missing_extensions[] = $ext;
    }
}

if (!empty($missing_extensions)) {
    print_warning("Install missing extensions for full functionality");
}

// Step 3: Check directory structure
echo "\nStep 3: Checking directory structure...\n";
$directories = [
    'config',
    'database',
    'includes',
    'assets',
    'assets/css',
    'assets/js',
    'assets/images',
    'api',
    'services'
];

foreach ($directories as $dir) {
    $path = BASE_PATH . '/' . $dir;
    if (is_dir($path)) {
        print_success("Directory: $dir");
    } else {
        print_info("Creating directory: $dir");
        @mkdir($path, 0755, true);
        if (is_dir($path)) {
            print_success("Created: $dir");
        } else {
            print_error("Failed to create: $dir");
        }
    }
}

// Step 4: Check configuration files
echo "\nStep 4: Checking configuration files...\n";
$config_files = [
    'config/config.php',
    'config/database.php',
    'database/schema.sql',
    'includes/auth.php',
    'includes/functions.php'
];

foreach ($config_files as $file) {
    $path = BASE_PATH . '/' . $file;
    if (file_exists($path)) {
        print_success("Found: $file");
    } else {
        print_error("Missing: $file");
    }
}

// Step 5: Check file permissions
echo "\nStep 5: Checking file permissions...\n";
$writable_dirs = [
    'assets',
    'assets/css',
    'assets/js',
    'api'
];

foreach ($writable_dirs as $dir) {
    $path = BASE_PATH . '/' . $dir;
    if (is_writable($path)) {
        print_success("Writable: $dir");
    } else {
        print_warning("Not writable: $dir (may cause issues)");
        @chmod($path, 0755);
    }
}

// Step 6: Test database connection
echo "\nStep 6: Testing database connection...\n";
try {
    require_once BASE_PATH . '/config/database.php';
    $conn = getDbConnection();
    
    if ($conn) {
        print_success("Database connection successful");
        
        // Check if tables exist
        $tables = [
            'agencies',
            'disasters',
            'agency_locations',
            'resources',
            'resource_requests',
            'communications'
        ];
        
        echo "\nChecking database tables:\n";
        $existing_tables = [];
        $stmt = $conn->query("SHOW TABLES FROM " . DB_NAME);
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $existing_tables[] = $row[0];
        }
        
        foreach ($tables as $table) {
            if (in_array($table, $existing_tables)) {
                print_success("Table: $table");
            } else {
                print_warning("Missing table: $table");
            }
        }
        
        if (count($existing_tables) < count($tables)) {
            print_info("Import database/schema.sql to create missing tables");
        }
    } else {
        print_error("Database connection failed");
        print_info("Check database credentials in config/database.php");
    }
} catch (Exception $e) {
    print_error("Database error: " . $e->getMessage());
}

// Installation summary
print_header("Installation Summary");

print_success("Environment check complete!");
echo "\n";
echo "Next steps:\n";
echo "1. Configure database in config/database.php\n";
echo "2. Import database schema: mysql -u root -p < database/schema.sql\n";
echo "3. Start the development server:\n";
echo "   - Windows: start.bat\n";
echo "   - Linux/Mac: ./start.sh\n";
echo "4. Access the application at http://localhost:8000\n";
echo "\n";

print_info("For detailed setup instructions, see SETUP.md");
print_success("Installation script completed!");

echo "\n";
