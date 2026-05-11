<?php
/**
 * Common utility functions
 */

/**
 * Sanitize user input
 * 
 * @param string $data The data to sanitize
 * @return string The sanitized data
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Generate a random string
 * 
 * @param int $length The length of the string
 * @return string The random string
 */
function generateRandomString($length = 32) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Calculate distance between two points using Haversine formula
 * 
 * @param float $lat1 Latitude of point 1
 * @param float $lon1 Longitude of point 1
 * @param float $lat2 Latitude of point 2
 * @param float $lon2 Longitude of point 2
 * @return float Distance in kilometers
 */
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Radius of the earth in km
    
    $latDelta = deg2rad($lat2 - $lat1);
    $lonDelta = deg2rad($lon2 - $lon1);
    
    $a = sin($latDelta / 2) * sin($latDelta / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
         sin($lonDelta / 2) * sin($lonDelta / 2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = $earthRadius * $c;
    
    return $distance;
}

/**
 * Format a timestamp to a readable date
 * 
 * @param string $timestamp The timestamp to format
 * @param string $format The format to use (default: 'Y-m-d H:i:s')
 * @return string The formatted date
 */
function formatDate($timestamp, $format = 'Y-m-d H:i:s') {
    $date = new DateTime($timestamp);
    return $date->format($format);
}

/**
 * Get time elapsed since a timestamp
 * 
 * @param string $timestamp The timestamp
 * @return string The time elapsed
 */
function timeElapsedString($timestamp) {
    $datetime = new DateTime($timestamp);
    $now = new DateTime();
    $interval = $now->diff($datetime);
    
    if ($interval->y >= 1) {
        return $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
    } elseif ($interval->m >= 1) {
        return $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
    } elseif ($interval->d >= 1) {
        return $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
    } elseif ($interval->h >= 1) {
        return $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
    } elseif ($interval->i >= 1) {
        return $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
    } else {
        return 'just now';
    }
}

/**
 * Redirect to a URL
 * 
 * @param string $url The URL to redirect to
 * @return void
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Check if the user is logged in
 * 
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get the current user's ID
 * 
 * @return int|null The user ID or null if not logged in
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get the current user's data
 * 
 * @param PDO $conn Database connection
 * @return array|null The user data or null if not logged in
 */
function getCurrentUser($conn) {
    if (!isLoggedIn()) {
        return null;
    }
    
    $userId = getCurrentUserId();
    $stmt = $conn->prepare("SELECT * FROM agencies WHERE id = :id");
    $stmt->execute(['id' => $userId]);
    
    return $stmt->fetch();
}

/**
 * Log an action to the system
 * 
 * @param PDO $conn Database connection
 * @param string $action The action performed
 * @param int $userId The user ID who performed the action
 * @param string $details Additional details
 * @return void
 */
function logAction($conn, $action, $userId = null, $details = '') {
    if ($userId === null && isLoggedIn()) {
        $userId = getCurrentUserId();
    }
    
    // This would typically write to a system_logs table
    // For now, we'll just log to the PHP error log
    error_log("Action: $action | User: $userId | Details: $details");
}

/**
 * Send a JSON response
 * 
 * @param array $data The data to send
 * @param int $statusCode HTTP status code
 * @return void
 */
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Get client IP address
 * 
 * @return string The IP address
 */
function getClientIp() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

/**
 * Generate a secure token
 * 
 * @return string The token
 */
function generateToken() {
    return bin2hex(random_bytes(32));
}

/**
 * Get the severity color for UI display
 * 
 * @param string $severity The severity level
 * @return string The color name
 */
function getSeverityColor($severity) {
    switch ($severity) {
        case 'low':
            return 'green';
        case 'medium':
            return 'yellow';
        case 'high':
            return 'orange';
        case 'critical':
            return 'red';
        default:
            return 'gray';
    }
}

/**
 * Get the status color for UI display
 * 
 * @param string $status The status
 * @return string The color name
 */
function getStatusColor($status) {
    switch ($status) {
        case 'reported':
            return 'yellow';
        case 'active':
            return 'red';
        case 'contained':
            return 'orange';
        case 'resolved':
            return 'green';
        default:
            return 'gray';
    }
}

/**
 * Get the agency type icon
 * 
 * @param string $type The agency type
 * @return string The icon class
 */
function getAgencyTypeIcon($type) {
    switch ($type) {
        case 'medical':
            return 'fas fa-ambulance';
        case 'fire':
            return 'fas fa-fire-extinguisher';
        case 'police':
            return 'fas fa-shield-alt';
        case 'military':
            return 'fas fa-fighter-jet';
        case 'ngo':
            return 'fas fa-hands-helping';
        default:
            return 'fas fa-building';
    }
}

/**
 * Get the disaster type icon
 * 
 * @param string $type The disaster type
 * @return string The icon class
 */
function getDisasterTypeIcon($type) {
    switch ($type) {
        case 'earthquake':
            return 'fas fa-house-damage';
        case 'flood':
            return 'fas fa-water';
        case 'fire':
            return 'fas fa-fire';
        case 'hurricane':
            return 'fas fa-wind';
        case 'tsunami':
            return 'fas fa-water';
        case 'landslide':
            return 'fas fa-mountain';
        case 'pandemic':
            return 'fas fa-virus';
        default:
            return 'fas fa-exclamation-triangle';
    }
}

/**
 * Get the severity class for UI display
 * 
 * @param string $severity The severity level
 * @return string The CSS class
 */
function getSeverityClass($severity) {
    switch ($severity) {
        case 'low':
            return 'bg-blue-100 text-blue-800';
        case 'medium':
            return 'bg-yellow-100 text-yellow-800';
        case 'high':
            return 'bg-orange-100 text-orange-800';
        case 'critical':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

/**
 * Get the status class for UI display
 * 
 * @param string $status The status
 * @return string The CSS class
 */
function getStatusClass($status) {
    switch ($status) {
        case 'reported':
            return 'bg-purple-100 text-purple-800';
        case 'active':
            return 'bg-red-100 text-red-800';
        case 'contained':
            return 'bg-yellow-100 text-yellow-800';
        case 'resolved':
            return 'bg-green-100 text-green-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}
