<?php
/**
 * Connection Test Page
 * This page verifies that the database connection is working properly
 */

require_once 'config/config.php';

$conn = null;
$error = null;

try {
    // Test database connection
    $conn = getDbConnection();
    
    if ($conn === null) {
        throw new Exception("getDbConnection() returned null");
    }
    
    // Try a simple query
    $result = $conn->query("SELECT COUNT(*) as count FROM agencies");
    $row = $result->fetch();
    $agencyCount = $row['count'];
    
    // Get database info
    $dbInfo = $conn->query("SELECT VERSION() as version")->fetch();
    
    $status = 'connected';
} catch (Exception $e) {
    $error = $e->getMessage();
    $status = 'error';
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto max-w-2xl px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="<?= $status === 'connected' ? 'bg-green-600' : 'bg-red-600' ?> text-white py-6 px-8">
                <h1 class="text-3xl font-bold mb-2">🔧 Database Connection Test</h1>
                <p class="<?= $status === 'connected' ? 'text-green-200' : 'text-red-200' ?>">
                    <?= $status === 'connected' ? '✓ Connection Successful' : '✗ Connection Failed' ?>
                </p>
            </div>
            
            <div class="p-8">
                <?php if ($status === 'connected'): ?>
                    <div class="space-y-4">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <h2 class="font-bold text-green-900 mb-3">✅ Connection Status</h2>
                            <ul class="text-green-800 space-y-2">
                                <li><strong>Host:</strong> <?= htmlspecialchars(DB_HOST) ?></li>
                                <li><strong>Database:</strong> <?= htmlspecialchars(DB_NAME) ?></li>
                                <li><strong>User:</strong> <?= htmlspecialchars(DB_USER) ?></li>
                                <li><strong>Database Version:</strong> <?= htmlspecialchars($dbInfo['version']) ?></li>
                                <li><strong>Agencies in Database:</strong> <?= $agencyCount ?></li>
                            </ul>
                        </div>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h2 class="font-bold text-blue-900 mb-3">✅ You Can Now:</h2>
                            <ul class="list-disc list-inside text-blue-900 space-y-2">
                                <li><a href="register.php" class="underline font-semibold hover:text-blue-700">Register your agency →</a></li>
                                <li><a href="login.php" class="underline font-semibold hover:text-blue-700">Login to existing account →</a></li>
                                <li><a href="index.php" class="underline font-semibold hover:text-blue-700">Go to dashboard →</a></li>
                            </ul>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <h2 class="font-bold text-red-900 mb-3">❌ Connection Error</h2>
                            <p class="text-red-800 font-mono break-all bg-red-100 p-3 rounded mb-3">
                                <?= htmlspecialchars($error) ?>
                            </p>
                        </div>
                        
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <h2 class="font-bold text-yellow-900 mb-3">⚠️ Troubleshooting</h2>
                            <ol class="list-decimal list-inside text-yellow-900 space-y-2">
                                <li>Check if MariaDB service is running (Windows Services)</li>
                                <li>Verify database credentials in: <code class="bg-yellow-100 px-2 py-1 rounded">config/database.php</code></li>
                                <li>Ensure database 'rescue_coordination' exists</li>
                                <li>Try restarting the PHP development server</li>
                                <li>Check the error message above for specific details</li>
                            </ol>
                        </div>
                        
                        <button onclick="location.reload()" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 font-semibold">
                            🔄 Retry Connection
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
