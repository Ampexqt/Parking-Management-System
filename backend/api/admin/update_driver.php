<?php
// Admin Update Driver API

// Set content type to JSON
header('Content-Type: application/json');

// Start session
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Include database configuration
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

// Validate required fields
if (empty($_POST['user_id']) || empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['email']) || empty($_POST['status'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'All fields are required'
    ]);
    exit;
}

$userId = $_POST['user_id'];
$firstName = trim($_POST['first_name']);
$lastName = trim($_POST['last_name']);
$email = trim($_POST['email']);
$status = $_POST['status'];

// Validate status
if (!in_array($status, ['active', 'inactive', 'suspended'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid status value'
    ]);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format'
    ]);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Check if driver exists
    $stmt = $pdo->prepare("SELECT user_id, full_name FROM users WHERE user_id = ? AND role = 'driver'");
    $stmt->execute([$userId]);
    $driver = $stmt->fetch();
    
    if (!$driver) {
        throw new Exception('Driver not found');
    }
    
    // Check if email already exists (excluding current driver)
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $stmt->execute([$email, $userId]);
    if ($stmt->fetch()) {
        throw new Exception('Email already exists');
    }
    
    // Update driver
    $fullName = $firstName . ' ' . $lastName;
    $stmt = $pdo->prepare("
        UPDATE users 
        SET first_name = ?, last_name = ?, full_name = ?, email = ?, status = ?, updated_at = NOW()
        WHERE user_id = ?
    ");
    $stmt->execute([$firstName, $lastName, $fullName, $email, $status, $userId]);
    
    // Log the action
    $stmt = $pdo->prepare("
        INSERT INTO logs (user_id, action, log_time)
        VALUES (?, ?, NOW())
    ");
    $stmt->execute([$_SESSION['user_id'], "Updated driver: {$driver['full_name']} to {$status}"]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Driver updated successfully',
        'data' => [
            'user_id' => $userId,
            'full_name' => $fullName,
            'email' => $email,
            'status' => $status
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
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
?>
