<?php
// Driver Controller - Handle driver registration and authentication

// Set content type to JSON for API responses
header('Content-Type: application/json');

// Include database configuration
require_once '../config/db.php';

// Handle registration request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['first_name'])) {
    handleDriverRegistration();
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method or missing data'
    ]);
    exit;
}

function handleDriverRegistration() {
    try {
        // Validate required fields
        $requiredFields = ['first_name', 'last_name', 'email', 'password', 'plate_number', 'vehicle_type'];
        $missingFields = [];
        
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $missingFields[] = $field;
            }
        }
        
        if (!empty($missingFields)) {
            throw new Exception('Missing required fields: ' . implode(', ', $missingFields));
        }
        
        // Sanitize and validate input data
        $firstName = trim($_POST['first_name']);
        $lastName = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $plateNumber = trim($_POST['plate_number']);
        $vehicleType = $_POST['vehicle_type'];
        $vehicleColor = isset($_POST['vehicle_color']) ? trim($_POST['vehicle_color']) : null;
        
        // Combine first and last name for full_name field
        $fullName = $firstName . ' ' . $lastName;
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }
        
        // Validate password strength
        if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)\S{8,30}$/', $password)) {
            throw new Exception('Password must be 8-30 characters with at least one letter and one number. Symbols are allowed.');
        }

        // Validate plate number (letters and numbers only)
        if (!preg_match('/^[A-Za-z0-9]+$/', $plateNumber)) {
            throw new Exception('Vehicle plate number must contain only letters and numbers');
        }

        // Get database connection
        $pdo = getDBConnection();
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception('Email already exists. Please use a different email address.');
        }
        
        // Check if plate number already exists
        $stmt = $pdo->prepare("SELECT vehicle_id FROM vehicles WHERE plate_number = ?");
        $stmt->execute([$plateNumber]);
        if ($stmt->fetch()) {
            throw new Exception('Vehicle plate number already registered. Please contact support if this is an error.');
        }
        
        // Start transaction
        $pdo->beginTransaction();
        
        try {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user record (using email as username)
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password, first_name, last_name, full_name, email, role, status, date_registered) 
                VALUES (?, ?, ?, ?, ?, ?, 'driver', 'active', NOW())
            ");
            $stmt->execute([$email, $hashedPassword, $firstName, $lastName, $fullName, $email]);
            $userId = $pdo->lastInsertId();
            
            // Insert vehicle record
            $stmt = $pdo->prepare("
                INSERT INTO vehicles (user_id, plate_number, vehicle_type, color, is_active, date_registered) 
                VALUES (?, ?, ?, ?, TRUE, NOW())
            ");
            $stmt->execute([$userId, $plateNumber, $vehicleType, $vehicleColor]);
            $vehicleId = $pdo->lastInsertId();
            
            // Log registration activity
            $stmt = $pdo->prepare("
                INSERT INTO logs (user_id, action, log_time) 
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$userId, 'Driver account created']);
            
            // Commit transaction
            $pdo->commit();
            
            // Return success response
            echo json_encode([
                'success' => true,
                'message' => 'Driver account created successfully',
                'data' => [
                    'user_id' => $userId,
                    'vehicle_id' => $vehicleId,
                    'username' => $email,
                    'full_name' => $fullName,
                    'email' => $email
                ]
            ]);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            throw $e;
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
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
