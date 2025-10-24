<?php
// Admin Recent Activity API

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

try {
    $pdo = getDBConnection();
    
    // Get recent activities from logs
    $stmt = $pdo->prepare("
        SELECT 
            l.action,
            l.log_time,
            u.full_name as user_name,
            u.role
        FROM logs l
        LEFT JOIN users u ON l.user_id = u.user_id
        ORDER BY l.log_time DESC
        LIMIT 10
    ");
    $stmt->execute();
    $logs = $stmt->fetchAll();
    
    // Get recent parking sessions
    $stmt = $pdo->prepare("
        SELECT 
            ps.session_id,
            ps.start_time,
            ps.end_time,
            ps.status,
            sl.slot_number,
            u.full_name as driver_name,
            v.plate_number
        FROM parking_sessions ps
        JOIN parking_slots sl ON ps.slot_id = sl.slot_id
        JOIN users u ON ps.user_id = u.user_id
        JOIN vehicles v ON ps.vehicle_id = v.vehicle_id
        ORDER BY ps.start_time DESC
        LIMIT 5
    ");
    $stmt->execute();
    $sessions = $stmt->fetchAll();
    
    $activities = [];
    
    // Process logs
    foreach ($logs as $log) {
        $activities[] = [
            'type' => 'system',
            'title' => $log['action'],
            'time_ago' => getTimeAgo($log['log_time']),
            'user' => $log['user_name'] ?: 'System',
            'role' => $log['role'] ?: 'system'
        ];
    }
    
    // Process parking sessions
    foreach ($sessions as $session) {
        if ($session['status'] === 'active') {
            $activities[] = [
                'type' => 'park',
                'title' => "Driver {$session['driver_name']} parked at Slot {$session['slot_number']}",
                'time_ago' => getTimeAgo($session['start_time']),
                'user' => $session['driver_name'],
                'role' => 'driver'
            ];
        } elseif ($session['status'] === 'completed') {
            $activities[] = [
                'type' => 'exit',
                'title' => "Driver {$session['driver_name']} exited from Slot {$session['slot_number']}",
                'time_ago' => getTimeAgo($session['end_time']),
                'user' => $session['driver_name'],
                'role' => 'driver'
            ];
        }
    }
    
    // Sort activities by time (most recent first)
    usort($activities, function($a, $b) {
        return strtotime($b['time_ago']) - strtotime($a['time_ago']);
    });
    
    // Limit to 10 most recent activities
    $activities = array_slice($activities, 0, 10);
    
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
