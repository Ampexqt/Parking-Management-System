<?php
// Admin Slot Details API

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

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Slot ID is required'
    ]);
    exit;
}

try {
    $pdo = getDBConnection();
    $slotId = $_GET['id'];
    
    // Get slot details with current session info
    $stmt = $pdo->prepare("
        SELECT 
            s.slot_id,
            s.slot_number,
            s.status,
            s.last_updated,
            ps.session_id,
            ps.start_time,
            ps.end_time,
            ps.status as session_status,
            TIMESTAMPDIFF(SECOND, ps.start_time, NOW()) AS duration_seconds,
            u.full_name as driver_name,
            v.plate_number as vehicle_plate,
            v.vehicle_type
        FROM parking_slots s
        LEFT JOIN parking_sessions ps ON s.slot_id = ps.slot_id AND ps.status = 'active'
        LEFT JOIN users u ON ps.user_id = u.user_id
        LEFT JOIN vehicles v ON ps.vehicle_id = v.vehicle_id
        WHERE s.slot_id = ?
    ");
    $stmt->execute([$slotId]);
    $slot = $stmt->fetch();
    
    if (!$slot) {
        throw new Exception('Slot not found');
    }
    
    // Format the response
    $response = [
        'slot_id' => $slot['slot_id'],
        'slot_number' => $slot['slot_number'],
        'status' => $slot['status'],
        'last_updated' => date('M j, Y g:i A', strtotime($slot['last_updated']))
    ];
    
    // Add current session info if exists
    if ($slot['session_id']) {
        $durationSeconds = isset($slot['duration_seconds']) ? max(0, (int)$slot['duration_seconds']) : null;
        $response['current_session'] = [
            'session_id' => $slot['session_id'],
            'driver_name' => $slot['driver_name'],
            'vehicle_plate' => $slot['vehicle_plate'],
            'vehicle_type' => $slot['vehicle_type'],
            'start_time' => date('M j, Y g:i A', strtotime($slot['start_time'])),
            'start_time_iso' => date(DATE_ATOM, strtotime($slot['start_time'])),
            'session_status' => $slot['session_status'],
            'duration_seconds' => $durationSeconds,
            'server_time_iso' => date(DATE_ATOM)
        ];
    }
    
    echo json_encode([
        'success' => true,
        'slot' => $response
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
