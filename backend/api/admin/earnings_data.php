<?php
// Admin Earnings Data API
// Returns earnings statistics based on approved payments

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output
ini_set('log_errors', 1);

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

// Get period parameter (default: today)
$period = isset($_GET['period']) ? $_GET['period'] : 'today';

try {
    $pdo = getDBConnection();
    
    $labels = [];
    $data = [];
    $stats = [
        'total' => 0,
        'average' => 0,
        'peakHour' => '00:00'
    ];
    
    // Build query based on period
    switch ($period) {
        case 'today':
            // Hourly earnings for today
            $stmt = $pdo->prepare("
                SELECT 
                    HOUR(p.payment_date) as hour,
                    SUM(p.amount) as total_earnings,
                    COUNT(p.payment_id) as payment_count
                FROM payments p
                WHERE p.payment_status = 'approved'
                AND DATE(p.payment_date) = CURDATE()
                GROUP BY HOUR(p.payment_date)
                ORDER BY hour
            ");
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            // Initialize all 24 hours with 0
            $hourlyData = array_fill(0, 24, 0);
            
            // Fill in actual data
            foreach ($results as $row) {
                $hourlyData[$row['hour']] = (float)$row['total_earnings'];
            }
            
            // Create labels and data arrays
            for ($i = 0; $i < 24; $i++) {
                $labels[] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
                $data[] = $hourlyData[$i];
            }
            
            // Calculate stats
            $stats['total'] = array_sum($data);
            $stats['average'] = $stats['total'] > 0 ? round($stats['total'] / 24, 2) : 0;
            
            // Find peak hour
            $maxValue = max($data);
            if ($maxValue > 0) {
                $peakIndex = array_search($maxValue, $data);
                $stats['peakHour'] = str_pad($peakIndex, 2, '0', STR_PAD_LEFT) . ':00';
            }
            break;
            
        case 'week':
            // Daily earnings for this week
            $stmt = $pdo->prepare("
                SELECT 
                    DAYOFWEEK(p.payment_date) as day_num,
                    DATE(p.payment_date) as payment_day,
                    SUM(p.amount) as total_earnings
                FROM payments p
                WHERE p.payment_status = 'approved'
                AND YEARWEEK(p.payment_date, 1) = YEARWEEK(CURDATE(), 1)
                GROUP BY DATE(p.payment_date)
                ORDER BY payment_day
            ");
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            // Days of week
            $daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            $weeklyData = array_fill(0, 7, 0);
            
            foreach ($results as $row) {
                $dayIndex = $row['day_num'] - 1; // MySQL DAYOFWEEK returns 1-7
                $weeklyData[$dayIndex] = (float)$row['total_earnings'];
            }
            
            // Reorder to start from Monday
            $reordered = array_merge(array_slice($weeklyData, 1), [$weeklyData[0]]);
            $reorderedLabels = array_merge(array_slice($daysOfWeek, 1), [$daysOfWeek[0]]);
            
            $labels = $reorderedLabels;
            $data = $reordered;
            
            $stats['total'] = array_sum($data);
            $stats['average'] = $stats['total'] > 0 ? round($stats['total'] / 7 / 24, 2) : 0;
            $stats['peakHour'] = '14:00'; // Default for week view
            break;
            
        case 'month':
            // Daily earnings for this month
            $stmt = $pdo->prepare("
                SELECT 
                    DAY(p.payment_date) as day_num,
                    SUM(p.amount) as total_earnings
                FROM payments p
                WHERE p.payment_status = 'approved'
                AND YEAR(p.payment_date) = YEAR(CURDATE())
                AND MONTH(p.payment_date) = MONTH(CURDATE())
                GROUP BY DAY(p.payment_date)
                ORDER BY day_num
            ");
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            // Get number of days in current month
            $daysInMonth = (int)date('t');
            $monthlyData = [];
            
            // Initialize all days with 0
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $monthlyData[$i] = 0;
            }
            
            // Fill in actual data
            foreach ($results as $row) {
                $monthlyData[(int)$row['day_num']] = (float)$row['total_earnings'];
            }
            
            // Convert to proper arrays for JSON
            $labels = [];
            $data = [];
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $labels[] = (string)$i;
                $data[] = $monthlyData[$i];
            }
            
            $stats['total'] = array_sum($data);
            $stats['average'] = $stats['total'] > 0 ? round($stats['total'] / $daysInMonth / 24, 2) : 0;
            $stats['peakHour'] = '14:00'; // Default for month view
            break;
            
        case 'year':
            // Monthly earnings for this year
            $stmt = $pdo->prepare("
                SELECT 
                    MONTH(p.payment_date) as month_num,
                    SUM(p.amount) as total_earnings
                FROM payments p
                WHERE p.payment_status = 'approved'
                AND YEAR(p.payment_date) = YEAR(CURDATE())
                GROUP BY MONTH(p.payment_date)
                ORDER BY month_num
            ");
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $yearlyData = [];
            
            // Initialize all months with 0
            for ($i = 1; $i <= 12; $i++) {
                $yearlyData[$i] = 0;
            }
            
            // Fill in actual data
            foreach ($results as $row) {
                $yearlyData[(int)$row['month_num']] = (float)$row['total_earnings'];
            }
            
            $labels = $months;
            $data = array_values($yearlyData);
            
            $stats['total'] = array_sum($data);
            $stats['average'] = $stats['total'] > 0 ? round($stats['total'] / 365 / 24, 2) : 0;
            $stats['peakHour'] = '14:00'; // Default for year view
            break;
            
        default:
            throw new Exception('Invalid period parameter');
    }
    
    echo json_encode([
        'success' => true,
        'labels' => $labels,
        'data' => $data,
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'period' => $period,
        'trace' => $e->getTraceAsString()
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
