<?php
// Admin Chart Data API

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

$period = $_GET['period'] ?? 'today';

try {
    $pdo = getDBConnection();
    
    $labels = [];
    $data = [];
    
    switch ($period) {
        case 'today':
            // Get hourly data for today
            for ($hour = 0; $hour < 24; $hour++) {
                $labels[] = sprintf('%02d:00', $hour);
                
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as count
                    FROM parking_sessions
                    WHERE DATE(start_time) = CURDATE() 
                    AND HOUR(start_time) = ?
                ");
                $stmt->execute([$hour]);
                $result = $stmt->fetch();
                $data[] = (int)$result['count'];
            }
            break;
            
        case 'week':
            // Get daily data for this week
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            foreach ($days as $day) {
                $labels[] = $day;
                
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as count
                    FROM parking_sessions
                    WHERE YEARWEEK(start_time, 1) = YEARWEEK(CURDATE(), 1)
                    AND DAYNAME(start_time) = ?
                ");
                $stmt->execute([$day]);
                $result = $stmt->fetch();
                $data[] = (int)$result['count'];
            }
            break;
            
        case 'month':
            // Get weekly data for this month
            $weeks = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
            foreach ($weeks as $week) {
                $labels[] = $week;
                
                $weekNum = array_search($week, $weeks) + 1;
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as count
                    FROM parking_sessions
                    WHERE YEAR(start_time) = YEAR(CURDATE())
                    AND MONTH(start_time) = MONTH(CURDATE())
                    AND WEEK(start_time, 1) - WEEK(DATE_SUB(start_time, INTERVAL DAYOFMONTH(start_time)-1 DAY), 1) + 1 = ?
                ");
                $stmt->execute([$weekNum]);
                $result = $stmt->fetch();
                $data[] = (int)$result['count'];
            }
            break;
            
        default:
            throw new Exception('Invalid period specified');
    }
    
    echo json_encode([
        'success' => true,
        'labels' => $labels,
        'data' => $data
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
