<?php
// Admin Driver Details API

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
        'message' => 'Driver ID is required'
    ]);
    exit;
}

try {
    $pdo = getDBConnection();
    $driverId = $_GET['id'];
    
    // Get driver details
    $stmt = $pdo->prepare("
        SELECT 
            u.user_id,
            u.username,
            u.first_name,
            u.last_name,
            u.full_name,
            u.email,
            u.status,
            u.last_login,
            u.date_registered,
            u.updated_at
        FROM users u
        WHERE u.user_id = ? AND u.role = 'driver'
    ");
    $stmt->execute([$driverId]);
    $driver = $stmt->fetch();
    
    if (!$driver) {
        throw new Exception('Driver not found');
    }
    
    // Get driver's vehicles
    $stmt = $pdo->prepare("
        SELECT 
            vehicle_id,
            plate_number,
            vehicle_type,
            color,
            is_active,
            date_registered
        FROM vehicles
        WHERE user_id = ?
        ORDER BY date_registered DESC
    ");
    $stmt->execute([$driverId]);
    $vehicles = $stmt->fetchAll();
    
    // Get recent parking sessions
    $stmt = $pdo->prepare("
        SELECT 
            ps.session_id,
            ps.start_time,
            ps.end_time,
            ps.status,
            s.slot_number,
            v.plate_number,
            v.vehicle_type
        FROM parking_sessions ps
        JOIN parking_slots s ON ps.slot_id = s.slot_id
        JOIN vehicles v ON ps.vehicle_id = v.vehicle_id
        WHERE ps.user_id = ?
        ORDER BY ps.start_time DESC
        LIMIT 10
    ");
    $stmt->execute([$driverId]);
    $sessions = $stmt->fetchAll();
    
    // Format the response
    $response = [
        'user_id' => $driver['user_id'],
        'username' => $driver['username'],
        'first_name' => $driver['first_name'],
        'last_name' => $driver['last_name'],
        'full_name' => $driver['full_name'],
        'email' => $driver['email'],
        'status' => $driver['status'],
        'last_login' => $driver['last_login'] ? date('M j, Y g:i A', strtotime($driver['last_login'])) : 'Never',
        'date_registered' => date('M j, Y g:i A', strtotime($driver['date_registered'])),
        'updated_at' => date('M j, Y g:i A', strtotime($driver['updated_at'])),
        'vehicles' => array_map(function($vehicle) {
            return [
                'vehicle_id' => $vehicle['vehicle_id'],
                'plate_number' => $vehicle['plate_number'],
                'vehicle_type' => $vehicle['vehicle_type'],
                'color' => $vehicle['color'],
                'is_active' => (bool)$vehicle['is_active'],
                'date_registered' => date('M j, Y', strtotime($vehicle['date_registered']))
            ];
        }, $vehicles),
        'recent_sessions' => array_map(function($session) {
            return [
                'session_id' => $session['session_id'],
                'slot_number' => $session['slot_number'],
                'plate_number' => $session['plate_number'],
                'vehicle_type' => $session['vehicle_type'],
                'start_time' => date('M j, Y g:i A', strtotime($session['start_time'])),
                'end_time' => $session['end_time'] ? date('M j, Y g:i A', strtotime($session['end_time'])) : 'Active',
                'status' => $session['status']
            ];
        }, $sessions)
    ];
    
    echo json_encode([
        'success' => true,
        'driver' => $response
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
