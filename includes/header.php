<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if session is valid
$isLoggedIn = isSessionValid();

// Get current user if logged in
$currentUser = null;
if ($isLoggedIn) {
    $conn = getDbConnection();
    if ($conn) {
        $currentUser = getCurrentUser($conn);
    }
}

// Get the current page
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Custom CSS -->
    <style>
        .map-container {
            height: 500px;
        }
        
        @media (min-width: 768px) {
            .map-container {
                height: 700px;
            }
        }
        
        .agency-marker {
            border-radius: 50%;
            width: 12px;
            height: 12px;
            border: 2px solid white;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
        }
        
        .agency-marker.medical { background-color: #ef4444; }
        .agency-marker.fire { background-color: #f97316; }
        .agency-marker.police { background-color: #3b82f6; }
        .agency-marker.military { background-color: #65a30d; }
        .agency-marker.ngo { background-color: #8b5cf6; }
        .agency-marker.other { background-color: #6b7280; }
        
        .disaster-marker {
            border-radius: 50%;
            width: 20px;
            height: 20px;
            border: 3px solid white;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .disaster-marker.earthquake { background-color: #b91c1c; }
        .disaster-marker.flood { background-color: #1d4ed8; }
        .disaster-marker.fire { background-color: #c2410c; }
        .disaster-marker.hurricane { background-color: #4338ca; }
        .disaster-marker.tsunami { background-color: #0369a1; }
        .disaster-marker.landslide { background-color: #92400e; }
        .disaster-marker.pandemic { background-color: #4d7c0f; }
        .disaster-marker.other { background-color: #4b5563; }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Top Navigation -->
    <nav class="bg-indigo-600 text-white shadow-md">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-3">
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="flex items-center space-x-2">
                        <i class="fas fa-hands-helping text-2xl"></i>
                        <span class="font-bold text-xl"><?= APP_NAME ?></span>
                    </a>
                </div>
                
                <div class="hidden md:flex items-center space-x-6">
                    <a href="index.php" class="hover:text-indigo-200 <?= $currentPage === 'index.php' ? 'border-b-2 border-white' : '' ?>">
                        <i class="fas fa-home mr-1"></i> Home
                    </a>
                    <a href="disasters.php" class="hover:text-indigo-200 <?= $currentPage === 'disasters.php' ? 'border-b-2 border-white' : '' ?>">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Disasters
                    </a>
                    <a href="agencies.php" class="hover:text-indigo-200 <?= $currentPage === 'agencies.php' ? 'border-b-2 border-white' : '' ?>">
                        <i class="fas fa-building mr-1"></i> Agencies
                    </a>
                    <a href="resources.php" class="hover:text-indigo-200 <?= $currentPage === 'resources.php' ? 'border-b-2 border-white' : '' ?>">
                        <i class="fas fa-boxes mr-1"></i> Resources
                    </a>
                    <a href="communications.php" class="hover:text-indigo-200 <?= $currentPage === 'communications.php' ? 'border-b-2 border-white' : '' ?>">
                        <i class="fas fa-comments mr-1"></i> Communications
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <?php if ($isLoggedIn): ?>
                        <div class="relative group">
                            <button class="flex items-center space-x-1 hover:text-indigo-200">
                                <i class="fas fa-user-circle text-xl"></i>
                                <span class="hidden md:inline"><?= htmlspecialchars($currentUser['name']) ?></span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 hidden group-hover:block">
                                <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-100">
                                    <i class="fas fa-id-card mr-2"></i> Profile
                                </a>
                                <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-indigo-100">
                                    <i class="fas fa-cog mr-2"></i> Settings
                                </a>
                                <div class="border-t border-gray-200 my-1"></div>
                                <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="hover:text-indigo-200">
                            <i class="fas fa-sign-in-alt mr-1"></i>
                            <span class="hidden md:inline">Login</span>
                        </a>
                        <a href="register.php" class="bg-white text-indigo-600 px-4 py-2 rounded-md hover:bg-indigo-100">
                            <i class="fas fa-user-plus mr-1"></i>
                            <span class="hidden md:inline">Register</span>
                        </a>
                    <?php endif; ?>
                    
                    <!-- Mobile menu button -->
                    <button id="mobile-menu-button" class="md:hidden text-white focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mobile menu -->
            <div id="mobile-menu" class="md:hidden hidden pb-4">
                <a href="index.php" class="block py-2 hover:text-indigo-200 <?= $currentPage === 'index.php' ? 'font-bold' : '' ?>">
                    <i class="fas fa-home mr-2"></i> Home
                </a>
                <a href="disasters.php" class="block py-2 hover:text-indigo-200 <?= $currentPage === 'disasters.php' ? 'font-bold' : '' ?>">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Disasters
                </a>
                <a href="agencies.php" class="block py-2 hover:text-indigo-200 <?= $currentPage === 'agencies.php' ? 'font-bold' : '' ?>">
                    <i class="fas fa-building mr-2"></i> Agencies
                </a>
                <a href="resources.php" class="block py-2 hover:text-indigo-200 <?= $currentPage === 'resources.php' ? 'font-bold' : '' ?>">
                    <i class="fas fa-boxes mr-2"></i> Resources
                </a>
                <a href="communications.php" class="block py-2 hover:text-indigo-200 <?= $currentPage === 'communications.php' ? 'font-bold' : '' ?>">
                    <i class="fas fa-comments mr-2"></i> Communications
                </a>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="flex-grow">
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="flash-message container mx-auto mt-4 px-4">
                <div class="<?= $_SESSION['flash_type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?> p-4 rounded-md flex items-start">
                    <div class="flex-shrink-0 mr-3">
                        <i class="fas <?= $_SESSION['flash_type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> text-xl"></i>
                    </div>
                    <div>
                        <p><?= $_SESSION['flash_message'] ?></p>
                    </div>
                    <button class="ml-auto flash-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
        <?php endif; ?>
