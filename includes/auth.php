<?php
/**
 * Authentication functions
 */

/**
 * Register a new agency
 * 
 * @param PDO $conn Database connection
 * @param array $data Agency data
 * @return array Result with status and message
 */
function registerAgency($conn, $data) {
    try {
        // Decode HTML entities that sanitize() may have added
        $rawEmail = html_entity_decode($data['email'], ENT_QUOTES, 'UTF-8');
        $rawEmail = trim($rawEmail);
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM agencies WHERE email = :email OR email = :encoded_email");
        $stmt->execute(['email' => $rawEmail, 'encoded_email' => $data['email']]);
        
        if ($stmt->rowCount() > 0) {
            return [
                'status' => 'error',
                'message' => 'Email already exists'
            ];
        }
        
        // Hash the password
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => HASH_COST]);
        
        // Generate verification code
        $verificationCode = generateRandomString(64);
        
        // Auto-verify accounts (no email verification needed)
        $autoVerify = 1;
        
        // Insert the new agency
        $stmt = $conn->prepare("
            INSERT INTO agencies (
                name, email, password, phone, agency_type, 
                address, city, state, country, 
                verification_code, verified
            ) VALUES (
                :name, :email, :password, :phone, :agency_type,
                :address, :city, :state, :country,
                :verification_code, :verified
            )
        ");
        
        $stmt->execute([
            'name' => $data['name'],
            'email' => $rawEmail,
            'password' => $hashedPassword,
            'phone' => $data['phone'],
            'agency_type' => $data['agency_type'],
            'address' => $data['address'],
            'city' => $data['city'],
            'state' => $data['state'],
            'country' => $data['country'],
            'verification_code' => $verificationCode,
            'verified' => $autoVerify
        ]);
        
        $agencyId = $conn->lastInsertId();
        
        // Send verification email (in a real app)
        // sendVerificationEmail($data['email'], $verificationCode);
        
        if ($autoVerify) {
            return [
                'status' => 'success',
                'message' => 'Registration successful! You can now log in.',
                'agency_id' => $agencyId
            ];
        } else {
            return [
                'status' => 'success',
                'message' => 'Registration successful. Please verify your email.',
                'agency_id' => $agencyId
            ];
        }
    } catch (PDOException $e) {
        error_log("Registration Error: " . $e->getMessage());
        return [
            'status' => 'error',
            'message' => 'Registration failed. Please try again.'
        ];
    }
}

/**
 * Verify an agency's email
 * 
 * @param PDO $conn Database connection
 * @param string $code Verification code
 * @return array Result with status and message
 */
function verifyAgency($conn, $code) {
    try {
        $stmt = $conn->prepare("
            UPDATE agencies 
            SET verified = TRUE, verification_code = NULL 
            WHERE verification_code = :code
        ");
        
        $stmt->execute(['code' => $code]);
        
        if ($stmt->rowCount() > 0) {
            return [
                'status' => 'success',
                'message' => 'Email verified successfully. You can now log in.'
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Invalid verification code.'
            ];
        }
    } catch (PDOException $e) {
        error_log("Verification Error: " . $e->getMessage());
        return [
            'status' => 'error',
            'message' => 'Verification failed. Please try again.'
        ];
    }
}

/**
 * Log in an agency
 * 
 * @param PDO $conn Database connection
 * @param string $email Email
 * @param string $password Password
 * @return array Result with status and message
 */
function loginAgency($conn, $email, $password) {
    try {
        // Strip any HTML encoding that sanitize() may have added — store/query raw email
        $rawEmail = html_entity_decode($email, ENT_QUOTES, 'UTF-8');
        $rawEmail = trim($rawEmail);
        
        $stmt = $conn->prepare("
            SELECT id, name, email, password, verified 
            FROM agencies 
            WHERE email = :email OR email = :encoded_email
        ");
        
        $stmt->execute([
            'email' => $rawEmail,
            'encoded_email' => $email
        ]);
        $agency = $stmt->fetch();
        
        if (!$agency) {
            return [
                'status' => 'error',
                'message' => 'Invalid email or password.'
            ];
        }
        
        if (!password_verify($password, $agency['password'])) {
            return [
                'status' => 'error',
                'message' => 'Invalid email or password.'
            ];
        }
        
        // Auto-verify the account if not already verified
        if (!$agency['verified']) {
            $updateStmt = $conn->prepare("UPDATE agencies SET verified = TRUE WHERE id = :id");
            $updateStmt->execute(['id' => $agency['id']]);
        }
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Set session variables
        $_SESSION['user_id'] = $agency['id'];
        $_SESSION['user_name'] = $agency['name'];
        $_SESSION['last_activity'] = time();
        
        // Update last active timestamp
        $stmt = $conn->prepare("
            UPDATE agencies 
            SET last_active = NOW() 
            WHERE id = :id
        ");
        
        $stmt->execute(['id' => $agency['id']]);
        
        return [
            'status' => 'success',
            'message' => 'Login successful.',
            'agency_id' => $agency['id'],
            'agency_name' => $agency['name']
        ];
    } catch (PDOException $e) {
        error_log("Login Error: " . $e->getMessage());
        return [
            'status' => 'error',
            'message' => 'Login failed. Please try again.'
        ];
    }
}

/**
 * Log out the current agency
 * 
 * @return void
 */
function logoutAgency() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy the session
    session_destroy();
}

/**
 * Reset an agency's password
 * 
 * @param PDO $conn Database connection
 * @param string $email Email
 * @return array Result with status and message
 */
function resetPassword($conn, $email) {
    try {
        $stmt = $conn->prepare("
            SELECT id, name 
            FROM agencies 
            WHERE email = :email AND verified = TRUE
        ");
        
        $stmt->execute(['email' => $email]);
        $agency = $stmt->fetch();
        
        if (!$agency) {
            return [
                'status' => 'error',
                'message' => 'Email not found or account not verified.'
            ];
        }
        
        // Generate reset token
        $resetToken = generateRandomString(64);
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store reset token in database
        $stmt = $conn->prepare("
            UPDATE agencies 
            SET reset_token = :token, reset_expiry = :expiry 
            WHERE id = :id
        ");
        
        $stmt->execute([
            'token' => $resetToken,
            'expiry' => $expiry,
            'id' => $agency['id']
        ]);
        
        // Send reset email (in a real app)
        // sendResetEmail($email, $resetToken);
        
        return [
            'status' => 'success',
            'message' => 'Password reset instructions sent to your email.'
        ];
    } catch (PDOException $e) {
        error_log("Password Reset Error: " . $e->getMessage());
        return [
            'status' => 'error',
            'message' => 'Password reset failed. Please try again.'
        ];
    }
}

/**
 * Update an agency's password with a reset token
 * 
 * @param PDO $conn Database connection
 * @param string $token Reset token
 * @param string $password New password
 * @return array Result with status and message
 */
function updatePasswordWithToken($conn, $token, $password) {
    try {
        $stmt = $conn->prepare("
            SELECT id 
            FROM agencies 
            WHERE reset_token = :token AND reset_expiry > NOW()
        ");
        
        $stmt->execute(['token' => $token]);
        $agency = $stmt->fetch();
        
        if (!$agency) {
            return [
                'status' => 'error',
                'message' => 'Invalid or expired reset token.'
            ];
        }
        
        // Hash the new password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
        
        // Update the password
        $stmt = $conn->prepare("
            UPDATE agencies 
            SET password = :password, reset_token = NULL, reset_expiry = NULL 
            WHERE id = :id
        ");
        
        $stmt->execute([
            'password' => $hashedPassword,
            'id' => $agency['id']
        ]);
        
        return [
            'status' => 'success',
            'message' => 'Password updated successfully. You can now log in with your new password.'
        ];
    } catch (PDOException $e) {
        error_log("Password Update Error: " . $e->getMessage());
        return [
            'status' => 'error',
            'message' => 'Password update failed. Please try again.'
        ];
    }
}

/**
 * Check if a session is valid and not expired
 * 
 * @return bool True if valid, false otherwise
 */
function isSessionValid() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Check if session has expired
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
        // Session expired, destroy it
        logoutAgency();
        return false;
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
    
    return true;
}

/**
 * Require authentication to access a page
 * 
 * @param string $redirectUrl URL to redirect to if not authenticated
 * @return void
 */
function requireAuth($redirectUrl = '/login.php') {
    if (!isSessionValid()) {
        redirect($redirectUrl);
    }
}
