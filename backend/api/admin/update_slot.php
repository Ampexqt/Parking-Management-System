<?php
// Admin Update Slot API

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
if (empty($_POST['slot_id']) || empty($_POST['slot_number']) || empty($_POST['status'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'All fields are required'
    ]);
    exit;
}

$slotId = $_POST['slot_id'];
$slotNumber = trim($_POST['slot_number']);
$status = $_POST['status'];

// Validate status
if (!in_array($status, ['available', 'occupied', 'maintenance'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid status value'
    ]);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Check if slot exists
    $stmt = $pdo->prepare("SELECT slot_id FROM parking_slots WHERE slot_id = ?");
    $stmt->execute([$slotId]);
    if (!$stmt->fetch()) {
        throw new Exception('Slot not found');
    }
    
    // Check if slot number already exists (excluding current slot)
    $stmt = $pdo->prepare("SELECT slot_id FROM parking_slots WHERE slot_number = ? AND slot_id != ?");
    $stmt->execute([$slotNumber, $slotId]);
    if ($stmt->fetch()) {
        throw new Exception('Slot number already exists');
    }
    
    // Update slot
    $stmt = $pdo->prepare("
        UPDATE parking_slots 
        SET slot_number = ?, status = ?, last_updated = NOW()
        WHERE slot_id = ?
    ");
    $stmt->execute([$slotNumber, $status, $slotId]);
    
    // Log the action
    $stmt = $pdo->prepare("
        INSERT INTO logs (user_id, action, log_time)
        VALUES (?, ?, NOW())
    ");
    $stmt->execute([$_SESSION['user_id'], "Updated parking slot: {$slotNumber} to {$status}"]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Slot updated successfully',
        'data' => [
            'slot_id' => $slotId,
            'slot_number' => $slotNumber,
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
