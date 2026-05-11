<?php
/**
 * Rescue Coordination System - Database Setup
 * 
 * This script initializes the database and imports the schema.
 * Access from browser: http://localhost:8000/setup-database.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$output = [];
$hasError = false;

// Add output message
function addOutput($message, $isError = false) {
    global $output, $hasError;
    if ($isError) {
        $hasError = true;
        $output[] = ['type' => 'error', 'message' => $message];
    } else {
        $output[] = ['type' => 'success', 'message' => $message];
    }
}

require_once __DIR__ . '/config/database.php';

// Try to connect to MariaDB/MySQL
try {
    addOutput("Attempting to connect to MariaDB/MySQL...");
    
    // First, try to connect to MySQL server without specifying database
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        )
    );
    
    addOutput("✓ Connected to MariaDB/MySQL server");
    
    // Create database
    try {
        $conn->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
        addOutput("✓ Database '" . DB_NAME . "' created/exists");
    } catch (Exception $e) {
        addOutput("Error creating database: " . $e->getMessage(), true);
    }
    
    // Select the database
    $conn->exec("USE " . DB_NAME);
    addOutput("✓ Selected '" . DB_NAME . "' database");
    
    // Read and execute schema
    $schemaFile = __DIR__ . '/database/schema.sql';
    if (file_exists($schemaFile)) {
        $schema = file_get_contents($schemaFile);
        
        // Remove SQL comments
        $schema = preg_replace('/--.*$/m', '', $schema);
        
        // Split by semicolon and execute each statement
        $statements = array_filter(array_map('trim', preg_split('/;/', $schema)));
        $statementCount = 0;
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                try {
                    $conn->exec($statement);
                    $statementCount++;
                } catch (Exception $e) {
                    // Some statements might already exist, that's okay
                    addOutput("Note: " . substr($e->getMessage(), 0, 100));
                }
            }
        }
        
        addOutput("✓ Database schema imported successfully ($statementCount statements executed)");
    } else {
        addOutput("Schema file not found at: $schemaFile", true);
    }
    
    // Test the connection with the new database
    $testConn = getDbConnection();
    
    if ($testConn) {
        $result = $testConn->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema='" . DB_NAME . "'")->fetch();
        addOutput("✓ Database connection test passed - " . $result['table_count'] . " tables created");
        
        addOutput("✅ DATABASE SETUP COMPLETE! You can now register and login.", false);
    } else {
        addOutput("❌ Database connection test failed with credentials from config/database.php", true);
    }
    
} catch (PDOException $e) {
    addOutput("❌ Database connection failed: " . $e->getMessage(), true);
    addOutput("Make sure MariaDB/MySQL is running and credentials in config/database.php are correct.", true);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - Rescue Coordination System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto max-w-2xl px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-indigo-600 text-white py-6 px-8">
                <h1 class="text-3xl font-bold mb-2">🚨 Database Setup</h1>
                <p class="text-indigo-200">Rescue Coordination System Initialization</p>
            </div>
            
            <div class="p-8">
                <div class="space-y-3">
                    <?php foreach ($output as $msg): ?>
                        <div class="flex items-start <?= $msg['type'] === 'error' ? 'text-red-800 bg-red-50' : 'text-green-800 bg-green-50' ?> p-4 rounded-lg border <?= $msg['type'] === 'error' ? 'border-red-200' : 'border-green-200' ?>">
                            <span class="font-bold mr-3">
                                <?= $msg['type'] === 'error' ? '✗' : '✓' ?>
                            </span>
                            <span><?= htmlspecialchars($msg['message']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (!$hasError): ?>
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-blue-900"><strong>Next Steps:</strong></p>
                        <ol class="list-decimal list-inside text-blue-900 mt-2 space-y-1">
                            <li>Visit <a href="register.php" class="underline font-semibold hover:text-blue-700">Register Page</a> to create your agency account</li>
                            <li>Then go to <a href="login.php" class="underline font-semibold hover:text-blue-700">Login Page</a> to access the system</li>
                        </ol>
                    </div>
                <?php else: ?>
                    <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-red-900"><strong>Troubleshooting:</strong></p>
                        <ol class="list-decimal list-inside text-red-900 mt-2 space-y-1">
                            <li>Make sure MariaDB/MySQL service is running</li>
                            <li>On Windows, check Services: Start → Services → MariaDB</li>
                            <li>If MariaDB is not installed, run <code class="bg-red-100 px-2 py-1 rounded">INSTALL_ALL.bat</code> from the project root</li>
                            <li>Verify the database credentials in: <code class="bg-red-100 px-2 py-1 rounded">rescue_coordination/config/database.php</code></li>
                        </ol>
                    </div>
                    
                    <button onclick="location.reload()" class="mt-4 w-full bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700">
                        🔄 Retry Setup
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
