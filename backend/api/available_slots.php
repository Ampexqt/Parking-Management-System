<?php
// Available Slots API - List parking slots and statuses
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

require_once '../config/db.php';

try {
    $pdo = getDBConnection();
    $status = isset($_GET['status']) ? strtolower(trim($_GET['status'])) : '';
    $valid = ['available','occupied','maintenance'];
    if ($status && in_array($status, $valid, true)) {
        $stmt = $pdo->prepare("SELECT slot_id, slot_number, status FROM parking_slots WHERE status = ? ORDER BY slot_number ASC");
        $stmt->execute([$status]);
        $slots = $stmt->fetchAll();
    } else {
        $stmt = $pdo->query("SELECT slot_id, slot_number, status FROM parking_slots ORDER BY slot_number ASC");
        $slots = $stmt->fetchAll();
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'slots' => $slots
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

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
