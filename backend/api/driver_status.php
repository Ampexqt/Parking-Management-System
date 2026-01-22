<?php
// Driver Status API - Get current parking status

// Set content type to JSON
header('Content-Type: application/json');

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Include database configuration
require_once '../config/db.php';

try {
    $pdo = getDBConnection();
    $userId = $_SESSION['user_id'];
    
    // Get current active parking session
    $stmt = $pdo->prepare("
        SELECT 
            ps.session_id,
            ps.start_time,
            UNIX_TIMESTAMP(ps.start_time) AS start_ts,
            UNIX_TIMESTAMP(CURRENT_TIMESTAMP) AS server_now_ts,
            ps.status,
            sl.slot_number,
            v.plate_number,
            v.vehicle_type
        FROM parking_sessions ps
        JOIN parking_slots sl ON ps.slot_id = sl.slot_id
        JOIN vehicles v ON ps.vehicle_id = v.vehicle_id
        WHERE ps.user_id = ? AND ps.status = 'active'
        ORDER BY ps.start_time DESC
        LIMIT 1
    ");
    
    $stmt->execute([$userId]);
    $session = $stmt->fetch();
    
    if ($session) {
        // Calculate duration using server timestamps to avoid client/server timezone drift
        $startTs = (int)$session['start_ts'];
        $serverNowTs = (int)$session['server_now_ts'];
        $diffSec = max(0, $serverNowTs - $startTs);
        $hours = intdiv($diffSec, 3600);
        $minutes = intdiv($diffSec % 3600, 60);
        $durationString = $hours . ' hours ' . $minutes . ' minutes';
        
        echo json_encode([
            'success' => true,
            'parking_status' => [
                'is_parked' => true,
                'session_id' => $session['session_id'],
                'slot_number' => $session['slot_number'],
                'start_time' => date('g:i A', strtotime($session['start_time'])),
                'start_time_iso' => $session['start_time'],
                'start_ts' => $startTs,
                'server_now_ts' => $serverNowTs,
                'duration' => $durationString,
                'plate_number' => $session['plate_number'],
                'vehicle_type' => $session['vehicle_type']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'parking_status' => [
                'is_parked' => false
            ]
        ]);
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
        $pdo->exec("SET time_zone = '+08:00'");
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception('Database connection failed: ' . $e->getMessage());
    }
}
?>
