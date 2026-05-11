<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Log out the user
logoutAgency();

// Start a new session for the flash message
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set flash message
$_SESSION['flash_message'] = 'You have been logged out successfully.';
$_SESSION['flash_type'] = 'success';

// Redirect to home page
redirect('index.php');
?>
