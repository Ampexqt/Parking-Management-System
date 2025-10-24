<?php
// Admin Drivers API

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
    
    // Get all drivers (users with role 'driver') with primary vehicle info
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
            COUNT(v.vehicle_id) as vehicle_count,
            COUNT(ps.session_id) as total_sessions,
            COUNT(CASE WHEN ps.status = 'active' THEN 1 END) as active_sessions,
            (SELECT vv.vehicle_type FROM vehicles vv WHERE vv.user_id = u.user_id AND vv.is_active = 1 ORDER BY vv.vehicle_id DESC LIMIT 1) AS vehicle_type,
            (SELECT vv.plate_number FROM vehicles vv WHERE vv.user_id = u.user_id AND vv.is_active = 1 ORDER BY vv.vehicle_id DESC LIMIT 1) AS plate_number
        FROM users u
        LEFT JOIN vehicles v ON u.user_id = v.user_id AND v.is_active = 1
        LEFT JOIN parking_sessions ps ON u.user_id = ps.user_id
        WHERE u.role = 'driver'
        GROUP BY u.user_id
        ORDER BY u.date_registered DESC
    ");
    $stmt->execute();
    $drivers = $stmt->fetchAll();
    
    // Format the response
    $formattedDrivers = array_map(function($driver) {
        return [
            'user_id' => $driver['user_id'],
            'username' => $driver['username'],
            'first_name' => $driver['first_name'],
            'last_name' => $driver['last_name'],
            'full_name' => $driver['full_name'],
            'email' => $driver['email'],
            'status' => $driver['status'],
            'last_login' => $driver['last_login'] ? date('M j, Y g:i A', strtotime($driver['last_login'])) : 'Never',
            'date_registered' => date('M j, Y', strtotime($driver['date_registered'])),
            'vehicle_count' => (int)$driver['vehicle_count'],
            'total_sessions' => (int)$driver['total_sessions'],
            'active_sessions' => (int)$driver['active_sessions'],
            'vehicle_type' => $driver['vehicle_type'] ?? null,
            'plate_number' => $driver['plate_number'] ?? null
        ];
    }, $drivers);
    
    echo json_encode([
        'success' => true,
        'drivers' => $formattedDrivers
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
