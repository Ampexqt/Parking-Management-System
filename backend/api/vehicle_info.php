<?php
// Vehicle Info API - Get driver's vehicle information

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
    
    // Get user's vehicle information
    $stmt = $pdo->prepare("
        SELECT 
            vehicle_id,
            plate_number,
            vehicle_type,
            color,
            is_active,
            date_registered
        FROM vehicles
        WHERE user_id = ? AND is_active = 1
        ORDER BY date_registered DESC
        LIMIT 1
    ");
    
    $stmt->execute([$userId]);
    $vehicle = $stmt->fetch();
    
    if ($vehicle) {
        echo json_encode([
            'success' => true,
            'vehicle' => [
                'vehicle_id' => $vehicle['vehicle_id'],
                'plate_number' => $vehicle['plate_number'],
                'vehicle_type' => $vehicle['vehicle_type'],
                'color' => $vehicle['color'],
                'is_active' => $vehicle['is_active'],
                'date_registered' => $vehicle['date_registered']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No vehicle found for this user'
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
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception('Database connection failed: ' . $e->getMessage());
    }
}
?>
