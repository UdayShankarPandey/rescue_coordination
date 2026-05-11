<?php
/**
 * API Endpoint - Get Disasters
 * Returns active disasters in JSON format
 */

require_once '../config/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    $conn = getDbConnection();
    
    if (!$conn) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Database connection failed'
        ], 500);
    }
    
    // Get active disasters
    $stmt = $conn->prepare("
        SELECT id, title, description, disaster_type, severity, latitude, longitude, 
               radius_km, status, started_at, created_at
        FROM disasters
        WHERE status IN ('reported', 'active')
        ORDER BY created_at DESC
        LIMIT 100
    ");
    
    $stmt->execute();
    $disasters = $stmt->fetchAll();
    
    sendJsonResponse([
        'success' => true,
        'disasters' => $disasters
    ]);
    
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'message' => 'An error occurred'
    ], 500);
}
