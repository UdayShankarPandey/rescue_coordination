<?php
/**
 * API Endpoint - Get Agencies
 * Returns active agencies with locations in JSON format
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
    
    // Get agencies with valid coordinates
    $stmt = $conn->prepare("
        SELECT id, name, email, phone, agency_type, city, state, country,
               latitude, longitude, last_active
        FROM agencies
        WHERE verified = TRUE AND latitude IS NOT NULL AND longitude IS NOT NULL
        ORDER BY last_active DESC
        LIMIT 500
    ");
    
    $stmt->execute();
    $agencies = $stmt->fetchAll();
    
    sendJsonResponse([
        'success' => true,
        'agencies' => $agencies
    ]);
    
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'message' => 'An error occurred'
    ], 500);
}
