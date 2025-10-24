<?php
// Recent Activity API - Get driver's recent activities

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
    
    // Get recent parking sessions
    $stmt = $pdo->prepare("
        SELECT 
            ps.session_id,
            ps.start_time,
            ps.end_time,
            ps.status,
            sl.slot_number,
            v.plate_number,
            p.payment_status
        FROM parking_sessions ps
        JOIN parking_slots sl ON ps.slot_id = sl.slot_id
        JOIN vehicles v ON ps.vehicle_id = v.vehicle_id
        LEFT JOIN payments p ON ps.session_id = p.session_id
        WHERE ps.user_id = ?
        ORDER BY ps.start_time DESC
        LIMIT 5
    ");
    
    $stmt->execute([$userId]);
    $sessions = $stmt->fetchAll();
    
    $activities = [];
    
    foreach ($sessions as $session) {
        // Add parking activity
        $activities[] = [
            'type' => 'park',
            'title' => "Parked at Slot {$session['slot_number']}",
            'time_ago' => getTimeAgo($session['start_time']),
            'status' => 'completed'
        ];
        
        // Add exit activity if session is completed
        if ($session['status'] === 'completed' && $session['end_time']) {
            $activities[] = [
                'type' => 'exit',
                'title' => "Exited from Slot {$session['slot_number']}",
                'time_ago' => getTimeAgo($session['end_time']),
                'status' => 'completed'
            ];
        }
        
        // Add payment activity if payment exists
        if ($session['payment_status']) {
            $paymentStatus = $session['payment_status'] === 'approved' ? 'completed' : 'pending';
            $activities[] = [
                'type' => 'payment',
                'title' => $session['payment_status'] === 'approved' ? 'Payment Approved' : 'Payment Pending',
                'time_ago' => getTimeAgo($session['end_time'] ?: $session['start_time']),
                'status' => $paymentStatus
            ];
        }
    }
    
    // Sort activities by time (most recent first)
    usort($activities, function($a, $b) {
        return strtotime($b['time_ago']) - strtotime($a['time_ago']);
    });
    
    // Limit to 5 most recent activities
    $activities = array_slice($activities, 0, 5);
    
    echo json_encode([
        'success' => true,
        'activities' => $activities
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

// Get time ago string
function getTimeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) {
        return 'Just now';
    } elseif ($time < 3600) {
        $minutes = floor($time / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($time < 86400) {
        $hours = floor($time / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($time < 2592000) {
        $days = floor($time / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', strtotime($datetime));
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
?>
