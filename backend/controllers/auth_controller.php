<?php
// Authentication Controller - Handle login and logout

// Set content type to JSON for API responses
header('Content-Type: application/json');

// Include database configuration
require_once '../config/db.php';

// Handle login request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    handleLogin();
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method or missing data'
    ]);
    exit;
}

function handleLogin() {
    try {
        // Validate required fields
        if (empty($_POST['username']) || empty($_POST['password'])) {
            throw new Exception('Username/email and password are required');
        }
        
        // Sanitize input data
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        // Get database connection
        $pdo = getDBConnection();
        
        // Check if username is email or username
        $isEmail = filter_var($username, FILTER_VALIDATE_EMAIL);
        
        if ($isEmail) {
            // Login with email
            $stmt = $pdo->prepare("
                SELECT user_id, username, password, first_name, last_name, full_name, email, role, status 
                FROM users 
                WHERE email = ?
            ");
        } else {
            // Login with username (which is also email in our system)
            $stmt = $pdo->prepare("
                SELECT user_id, username, password, first_name, last_name, full_name, email, role, status 
                FROM users 
                WHERE username = ?
            ");
        }
        
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if (!$user) {
            throw new Exception('Invalid username/email or password');
        }
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            throw new Exception('Invalid username/email or password');
        }
        
        // Check account status after password verification
        if ($user['status'] === 'suspended') {
            http_response_code(403);
            throw new Exception('Your account has been suspended. Please contact the administrator.');
        }
        
        if ($user['status'] === 'inactive') {
            http_response_code(403);
            throw new Exception('Your account is inactive. Please contact the administrator.');
        }
        
        // Update last login
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        
        // Start session
        session_start();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        
        // Log login activity
        $stmt = $pdo->prepare("
            INSERT INTO logs (user_id, action, log_time) 
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$user['user_id'], 'User logged in']);
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name']
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// Get database connection
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception('Database connection failed: ' . $e->getMessage());
    }
}

// Handle logout (if needed)
function handleLogout() {
    session_start();
    session_destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully'
    ]);
}
?>
