<?php
/**
 * Application configuration
 */

// Load .env file
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

// Helper function to get env variable with fallback
function get_env($key, $default = null) {
    $value = getenv($key);
    if ($value === false) {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
    return $value;
}

// Application settings
define('APP_NAME', get_env('APP_NAME', 'Rescue Coordination System'));
define('APP_VERSION', '1.0.0');
define('APP_URL', get_env('APP_URL', 'http://localhost:8000'));
define('APP_ROOT', dirname(__DIR__));

// Session settings
define('SESSION_LIFETIME', (int)get_env('SESSION_LIFETIME', 86400)); // 24 hours in seconds
define('SESSION_NAME', get_env('SESSION_NAME', 'rescue_coordination_session'));

// Security settings
define('HASH_COST', (int)get_env('HASH_COST', 10)); // For password hashing

// Map settings
define('DEFAULT_LAT', 20.5937); // Default map center latitude
define('DEFAULT_LNG', 78.9629); // Default map center longitude (India center)
define('DEFAULT_ZOOM', 5); // Default map zoom level

// API keys
define('OPENWEATHER_API_KEY', get_env('OPENWEATHER_API_KEY', 'YOUR_OPENWEATHER_API_KEY'));
define('OPENAI_API_KEY', get_env('OPENAI_API_KEY', 'YOUR_OPENAI_API_KEY'));

// Time settings
define('DEFAULT_TIMEZONE', get_env('DEFAULT_TIMEZONE', 'Asia/Kolkata'));
date_default_timezone_set(DEFAULT_TIMEZONE);

// Error reporting
$env = get_env('ENVIRONMENT', 'production');
if ($env === 'development' || $_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
    // Development environment
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    define('ENVIRONMENT', 'development');
} else {
    // Production environment
    error_reporting(0);
    ini_set('display_errors', 0);
    define('ENVIRONMENT', 'production');
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Include database configuration
require_once __DIR__ . '/database.php';
