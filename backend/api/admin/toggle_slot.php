<?php
// Admin Toggle Slot Status API

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

if (!isset($input['slot_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Slot ID is required'
    ]);
    exit;
}

try {
    $pdo = getDBConnection();
    $slotId = $input['slot_id'];
    
    // Get current slot status
    $stmt = $pdo->prepare("
        SELECT slot_id, slot_number, status
        FROM parking_slots
        WHERE slot_id = ?
    ");
    $stmt->execute([$slotId]);
    $slot = $stmt->fetch();
    
    if (!$slot) {
        throw new Exception('Slot not found');
    }
    
    // Toggle status
    $newStatus = $slot['status'] === 'available' ? 'maintenance' : 'available';
    
    // Update slot status
    $stmt = $pdo->prepare("
        UPDATE parking_slots 
        SET status = ?, last_updated = NOW()
        WHERE slot_id = ?
    ");
    $stmt->execute([$newStatus, $slotId]);
    
    // Log the action
    $stmt = $pdo->prepare("
        INSERT INTO logs (user_id, action, log_time)
        VALUES (?, ?, NOW())
    ");
    $stmt->execute([$_SESSION['user_id'], "Changed slot {$slot['slot_number']} status to {$newStatus}"]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Slot status updated successfully',
        'data' => [
            'slot_id' => $slotId,
            'slot_number' => $slot['slot_number'],
            'old_status' => $slot['status'],
            'new_status' => $newStatus
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
