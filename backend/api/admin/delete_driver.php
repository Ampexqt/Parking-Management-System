<?php
// Admin Delete Driver API

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

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['user_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Driver ID is required'
    ]);
    exit;
}

try {
    $pdo = getDBConnection();
    $driverId = $input['user_id'];
    
    // Get driver info for logging
    $stmt = $pdo->prepare("SELECT full_name FROM users WHERE user_id = ? AND role = 'driver'");
    $stmt->execute([$driverId]);
    $driver = $stmt->fetch();
    
    if (!$driver) {
        throw new Exception('Driver not found');
    }
    
    // Check if driver has active parking sessions
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as active_sessions
        FROM parking_sessions
        WHERE user_id = ? AND status = 'active'
    ");
    $stmt->execute([$driverId]);
    $result = $stmt->fetch();
    
    if ($result['active_sessions'] > 0) {
        throw new Exception('Cannot delete driver with active parking sessions');
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Delete related data in order (due to foreign key constraints)
        // Delete payments (if any)
        $stmt = $pdo->prepare("
            DELETE p FROM payments p
            JOIN parking_sessions ps ON p.session_id = ps.session_id
            WHERE ps.user_id = ?
        ");
        $stmt->execute([$driverId]);
        
        // Delete parking sessions
        $stmt = $pdo->prepare("DELETE FROM parking_sessions WHERE user_id = ?");
        $stmt->execute([$driverId]);
        
        // Delete vehicles
        $stmt = $pdo->prepare("DELETE FROM vehicles WHERE user_id = ?");
        $stmt->execute([$driverId]);
        
        // Delete logs (optional - keep for audit trail)
        // $stmt = $pdo->prepare("DELETE FROM logs WHERE user_id = ?");
        // $stmt->execute([$driverId]);
        
        // Delete the driver
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND role = 'driver'");
        $stmt->execute([$driverId]);
        
        // Log the action
        $stmt = $pdo->prepare("
            INSERT INTO logs (user_id, action, log_time)
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$_SESSION['user_id'], "Deleted driver: {$driver['full_name']}"]);
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Driver deleted successfully',
            'data' => [
                'user_id' => $driverId,
                'full_name' => $driver['full_name']
            ]
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
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
