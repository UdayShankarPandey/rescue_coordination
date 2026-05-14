<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if already logged in
if (isSessionValid()) {
    redirect('index.php');
}

$error = '';
$success = '';
$formData = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'agency_type' => '',
    'address' => '',
    'city' => '',
    'state' => '',
    'country' => ''
];

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get database connection
    $conn = getDbConnection();
    
    if (!$conn) {
        $error = 'Database connection error. Please try again later.';
    } else {
        // Get form data
        $formData = [
            'name' => sanitize($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'phone' => sanitize($_POST['phone'] ?? ''),
            'agency_type' => sanitize($_POST['agency_type'] ?? ''),
            'address' => sanitize($_POST['address'] ?? ''),
            'city' => sanitize($_POST['city'] ?? ''),
            'state' => sanitize($_POST['state'] ?? ''),
            'country' => sanitize($_POST['country'] ?? '')
        ];
        
        // Validate form data
        if (empty($formData['name']) || empty($formData['email']) || empty($formData['password']) || 
            empty($formData['confirm_password']) || empty($formData['phone']) || empty($formData['agency_type']) ||
            empty($formData['address']) || empty($formData['city']) || empty($formData['state']) || 
            empty($formData['country'])) {
            $error = 'Please fill in all required fields.';
        } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($formData['password']) < 8) {
            $error = 'Password must be at least 8 characters long.';
        } elseif ($formData['password'] !== $formData['confirm_password']) {
            $error = 'Passwords do not match.';
        } else {
            // Attempt registration
            $result = registerAgency($conn, $formData);
            
            if ($result['status'] === 'success') {
                $success = $result['message'];
                // Clear form data
                $formData = [
                    'name' => '',
                    'email' => '',
                    'phone' => '',
                    'agency_type' => '',
                    'address' => '',
                    'city' => '',
                    'state' => '',
                    'country' => ''
                ];
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
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-indigo-600 text-white py-4 px-6">
            <h2 class="text-2xl font-bold">Register Your Agency</h2>
            <p class="text-indigo-200">Join the network of rescue agencies</p>
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
            
            <?php if (!empty($success)): ?>
                <div class="bg-green-100 text-green-700 p-4 rounded-md mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p><?= $success ?></p>
                            <p class="mt-2">Please check your email to verify your account. Once verified, you can <a href="login.php" class="font-medium underline">login here</a>.</p>
                            <p class="mt-2 text-sm">(For demo purposes, you can login immediately without verification)</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Agency Information -->
                        <div class="md:col-span-2">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">Agency Information</h3>
                        </div>
                        
                        <div>
                            <label for="name" class="block text-gray-700 font-medium mb-2">Agency Name <span class="text-red-600">*</span></label>
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($formData['name']) ?>" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        
                        <div>
                            <label for="agency_type" class="block text-gray-700 font-medium mb-2">Agency Type <span class="text-red-600">*</span></label>
                            <select id="agency_type" name="agency_type" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="" disabled <?= empty($formData['agency_type']) ? 'selected' : '' ?>>Select agency type</option>
                                <option value="medical" <?= $formData['agency_type'] === 'medical' ? 'selected' : '' ?>>Medical</option>
                                <option value="fire" <?= $formData['agency_type'] === 'fire' ? 'selected' : '' ?>>Fire Department</option>
                                <option value="police" <?= $formData['agency_type'] === 'police' ? 'selected' : '' ?>>Police</option>
                                <option value="military" <?= $formData['agency_type'] === 'military' ? 'selected' : '' ?>>Military</option>
                                <option value="ngo" <?= $formData['agency_type'] === 'ngo' ? 'selected' : '' ?>>NGO</option>
                                <option value="other" <?= $formData['agency_type'] === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        
                        <!-- Contact Information -->
                        <div class="md:col-span-2">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3 mt-4">Contact Information</h3>
                        </div>
                        
                        <div>
                            <label for="email" class="block text-gray-700 font-medium mb-2">Email Address <span class="text-red-600">*</span></label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($formData['email']) ?>" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-gray-700 font-medium mb-2">Phone Number <span class="text-red-600">*</span></label>
                            <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($formData['phone']) ?>" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        
                        <!-- Address -->
                        <div class="md:col-span-2">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3 mt-4">Address</h3>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label for="address" class="block text-gray-700 font-medium mb-2">Street Address <span class="text-red-600">*</span></label>
                            <input type="text" id="address" name="address" value="<?= htmlspecialchars($formData['address']) ?>" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        
                        <div>
                            <label for="city" class="block text-gray-700 font-medium mb-2">City <span class="text-red-600">*</span></label>
                            <input type="text" id="city" name="city" value="<?= htmlspecialchars($formData['city']) ?>" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        
                        <div>
                            <label for="state" class="block text-gray-700 font-medium mb-2">State/Province <span class="text-red-600">*</span></label>
                            <input type="text" id="state" name="state" value="<?= htmlspecialchars($formData['state']) ?>" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        
                        <div>
                            <label for="country" class="block text-gray-700 font-medium mb-2">Country <span class="text-red-600">*</span></label>
                            <input type="text" id="country" name="country" value="<?= htmlspecialchars($formData['country']) ?>" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        
                        <!-- Password -->
                        <div class="md:col-span-2">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3 mt-4">Account Security</h3>
                        </div>
                        
                        <div>
                            <label for="password" class="block text-gray-700 font-medium mb-2">Password <span class="text-red-600">*</span></label>
                            <input type="password" id="password" name="password" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <p class="text-sm text-gray-500 mt-1">Minimum 8 characters</p>
                        </div>
                        
                        <div>
                            <label for="confirm_password" class="block text-gray-700 font-medium mb-2">Confirm Password <span class="text-red-600">*</span></label>
                            <input type="password" id="confirm_password" name="confirm_password" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        
                        <!-- Terms and Conditions -->
                        <div class="md:col-span-2 mt-4">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" id="terms" name="terms" required
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="terms" class="text-gray-700">
                                        I agree to the <a href="#" class="text-indigo-600 hover:text-indigo-800">Terms and Conditions</a> and <a href="#" class="text-indigo-600 hover:text-indigo-800">Privacy Policy</a>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="md:col-span-2 mt-6">
                            <button type="submit" class="w-full bg-indigo-600 text-white py-3 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <i class="fas fa-user-plus mr-2"></i> Register Agency
                            </button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
            
            <div class="mt-6 text-center">
                <p class="text-gray-600">Already have an account? <a href="login.php" class="text-indigo-600 hover:text-indigo-800 font-medium">Login here</a></p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
