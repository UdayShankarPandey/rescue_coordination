<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if already logged in
if (isSessionValid()) {
    redirect('index.php');
}

$error = '';
$email = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get database connection
    $conn = getDbConnection();
    
    if (!$conn) {
        $error = 'Database connection error. Please try again later.';
    } else {
        // Get form data
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validate form data
        if (empty($email) || empty($password)) {
            $error = 'Please enter both email and password.';
        } else {
            // Attempt login
            $result = loginAgency($conn, $email, $password);
            
            if ($result['status'] === 'success') {
                // Set flash message
                $_SESSION['flash_message'] = 'Login successful. Welcome back!';
                $_SESSION['flash_type'] = 'success';
                
                // Redirect to dashboard
                redirect('index.php');
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Include header
include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-indigo-600 text-white py-4 px-6">
            <h2 class="text-2xl font-bold">Login to Your Account</h2>
            <p class="text-indigo-200">Access your agency dashboard</p>
        </div>
        
        <div class="p-6">
            <?php if (!empty($error)): ?>
                <div class="bg-red-100 text-red-700 p-4 rounded-md mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p><?= $error ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>">
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-medium mb-2">Email Address</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <label for="password" class="block text-gray-700 font-medium">Password</label>
                        <a href="forgot-password.php" class="text-sm text-indigo-600 hover:text-indigo-800">Forgot password?</a>
                    </div>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                
                <div class="flex items-center mb-6">
                    <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="remember" class="ml-2 block text-gray-700">Remember me</label>
                </div>
                
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-gray-600">Don't have an account? <a href="register.php" class="text-indigo-600 hover:text-indigo-800 font-medium">Register your agency</a></p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
