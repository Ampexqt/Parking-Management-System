<?php
// Admin Earnings PDF Export API
// Generates PDF report for earnings statistics

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1); // Show errors for debugging
ini_set('log_errors', 1);

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

// Get parameters
$period = isset($_GET['period']) ? $_GET['period'] : 'today';
$action = isset($_GET['action']) ? $_GET['action'] : 'preview'; // 'preview' or 'download'

try {
    // Debug: Log the request
    error_log("Export earnings PDF request - Period: " . $period . ", Action: " . $action);
    
    $pdo = getDBConnection();
    
    // Test database connection
    if (!$pdo) {
        throw new Exception('Failed to establish database connection');
    }
    
    $labels = [];
    $data = [];
    $stats = [
        'total' => 0,
        'average' => 0,
        'peakHour' => '00:00'
    ];
    $periodLabel = '';
    
    // Build query based on period
    switch ($period) {
        case 'today':
            $periodLabel = 'Today (' . date('M d, Y') . ')';
            $stmt = $pdo->prepare("
                SELECT 
                    HOUR(p.payment_date) as hour,
                    SUM(p.amount) as total_earnings
                FROM payments p
                WHERE p.payment_status = 'approved'
                AND DATE(p.payment_date) = CURDATE()
                GROUP BY HOUR(p.payment_date)
                ORDER BY hour
            ");
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            // Initialize 24 hours with 0 earnings
            $hourlyData = [];
            for ($i = 0; $i < 24; $i++) {
                $hourlyData[$i] = 0;
            }
            
            // Fill in actual data
            foreach ($results as $row) {
                $hourlyData[(int)$row['hour']] = (float)$row['total_earnings'];
            }
            
            // Create labels for every 2 hours (12-hour format)
            for ($i = 0; $i < 24; $i += 2) {
                $labels[] = date('g:i A', strtotime(sprintf('%02d:00', $i)));
            }
            
            // Create data for every 2 hours
            for ($i = 0; $i < 24; $i += 2) {
                $data[] = $hourlyData[$i] + ($i + 1 < 24 ? $hourlyData[$i + 1] : 0);
            }
            
            $stats['total'] = array_sum($hourlyData);
            $stats['average'] = $stats['total'] > 0 ? round($stats['total'] / 24, 2) : 0;
            
            // Find peak hour
            $maxEarnings = max($hourlyData);
            $peakHourIndex = array_search($maxEarnings, $hourlyData);
            $stats['peakHour'] = date('g:i A', strtotime(sprintf('%02d:00', $peakHourIndex)));
            break;
            
        case 'week':
            $periodLabel = 'This Week (' . date('M d', strtotime('monday this week')) . ' - ' . date('M d, Y', strtotime('sunday this week')) . ')';
            $stmt = $pdo->prepare("
                SELECT 
                    DAYNAME(p.payment_date) as day_name,
                    DAYOFWEEK(p.payment_date) as day_num,
                    SUM(p.amount) as total_earnings
                FROM payments p
                WHERE p.payment_status = 'approved'
                AND YEARWEEK(p.payment_date) = YEARWEEK(CURDATE())
                GROUP BY DAYOFWEEK(p.payment_date), DAYNAME(p.payment_date)
                ORDER BY day_num
            ");
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            $weeklyData = [];
            
            // Initialize all days with 0
            for ($i = 1; $i <= 7; $i++) {
                $weeklyData[$i] = 0;
            }
            
            // Fill in actual data
            foreach ($results as $row) {
                $weeklyData[(int)$row['day_num']] = (float)$row['total_earnings'];
            }
            
            $labels = $days;
            $orderedIndices = [2, 3, 4, 5, 6, 7, 1]; // Start from Monday
            $data = [];
            foreach ($orderedIndices as $index) {
                $data[] = $weeklyData[$index] ?? 0;
            }
            
            $stats['total'] = array_sum($data);
            $stats['average'] = $stats['total'] > 0 ? round($stats['total'] / 7, 2) : 0;
            $peakIndex = array_search(max($data), $data, true);
            $stats['peakHour'] = $peakIndex !== false ? $days[$peakIndex] : $days[0];
            break;
            
        case 'month':
            $periodLabel = 'This Month (' . date('F Y') . ')';
            $stmt = $pdo->prepare("
                SELECT 
                    DAY(p.payment_date) as day,
                    SUM(p.amount) as total_earnings
                FROM payments p
                WHERE p.payment_status = 'approved'
                AND YEAR(p.payment_date) = YEAR(CURDATE())
                AND MONTH(p.payment_date) = MONTH(CURDATE())
                GROUP BY DAY(p.payment_date)
                ORDER BY day
            ");
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            $daysInMonth = date('t');
            $monthlyData = [];
            
            // Initialize all days with 0
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $monthlyData[$i] = 0;
            }
            
            // Fill in actual data
            foreach ($results as $row) {
                $monthlyData[(int)$row['day']] = (float)$row['total_earnings'];
            }
            
            // Create labels for every 5 days
            $labels = [];
            $data = [];
            for ($i = 1; $i <= $daysInMonth; $i += 5) {
                $labels[] = 'Day ' . $i;
                $dayData = 0;
                for ($j = $i; $j < min($i + 5, $daysInMonth + 1); $j++) {
                    $dayData += $monthlyData[$j];
                }
                $data[] = $dayData;
            }
            
            $stats['total'] = array_sum($monthlyData);
            $stats['average'] = $stats['total'] > 0 ? round($stats['total'] / $daysInMonth, 2) : 0;
            $stats['peakHour'] = 'Day ' . array_search(max($monthlyData), $monthlyData);
            break;
            
        case 'year':
            $periodLabel = 'This Year (' . date('Y') . ')';
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
            $stats['average'] = $stats['total'] > 0 ? round($stats['total'] / 365, 2) : 0;
            $stats['peakHour'] = $months[array_search(max($data), $data)];
            break;
            
        default:
            throw new Exception('Invalid period parameter');
    }
    
    // Get additional statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_sessions,
            COUNT(DISTINCT ps.user_id) as unique_drivers,
            AVG(p.amount) as avg_payment
        FROM payments p
        JOIN parking_sessions ps ON p.session_id = ps.session_id
        WHERE p.payment_status = 'approved'
        AND (
            CASE 
                WHEN ? = 'today' THEN DATE(p.payment_date) = CURDATE()
                WHEN ? = 'week' THEN YEARWEEK(p.payment_date) = YEARWEEK(CURDATE())
                WHEN ? = 'month' THEN YEAR(p.payment_date) = YEAR(CURDATE()) AND MONTH(p.payment_date) = MONTH(CURDATE())
                WHEN ? = 'year' THEN YEAR(p.payment_date) = YEAR(CURDATE())
            END
        )
    ");
    $stmt->execute([$period, $period, $period, $period]);
    $additionalStats = $stmt->fetch();
    if (!$additionalStats) {
        $additionalStats = [
            'total_sessions' => 0,
            'unique_drivers' => 0,
            'avg_payment' => 0
        ];
    } else {
        $additionalStats['total_sessions'] = (int) ($additionalStats['total_sessions'] ?? 0);
        $additionalStats['unique_drivers'] = (int) ($additionalStats['unique_drivers'] ?? 0);
        $additionalStats['avg_payment'] = (float) ($additionalStats['avg_payment'] ?? 0);
    }
    
    // Debug: Log additional stats
    error_log("Additional stats: " . json_encode($additionalStats));
    
    // Prepare report data
    $generatedAt = new DateTime('now');
    $reportData = [
        'period' => $period,
        'periodLabel' => $periodLabel,
        'labels' => $labels,
        'data' => $data,
        'stats' => $stats,
        'additionalStats' => $additionalStats,
        'generatedAt' => $generatedAt->format(DateTime::ATOM),
        'timezone' => $generatedAt->getTimezone()->getName(),
        'generatedBy' => $_SESSION['username'] ?? 'Administrator'
    ];
    
    if ($action === 'preview') {
        // Return preview data as JSON
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $reportData
        ]);
    } else {
        // Generate and return PDF
        generatePDF($reportData);
    }
    
} catch (Exception $e) {
    // Debug: Log the error
    error_log("Export earnings PDF error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}

function generatePDF($reportData) {
    // Generate HTML report
    $html = generateHTMLReport($reportData);
    
    // Set headers for PDF download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="earnings_report_' . $reportData['period'] . '_' . date('Y-m-d') . '.pdf"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    // For a more robust PDF generation, you could use:
    // 1. TCPDF library
    // 2. FPDF library  
    // 3. mPDF library
    // 4. wkhtmltopdf command line tool
    // 5. Browser's print to PDF functionality
    
    // For now, we'll return HTML that can be printed to PDF
    // The user can use their browser's "Print to PDF" functionality
    echo $html;
}

function generateHTMLReport($data) {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Earnings Report - ' . $data['periodLabel'] . '</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .header h1 { color: #2c3e50; margin: 0; }
            .header p { color: #7f8c8d; margin: 5px 0; }
            .stats-grid { display: flex; justify-content: space-around; margin: 20px 0; }
            .stat-card { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; min-width: 150px; }
            .stat-value { font-size: 24px; font-weight: bold; color: #2c3e50; }
            .stat-label { color: #7f8c8d; font-size: 14px; }
            .chart-section { margin: 30px 0; }
            .chart-title { font-size: 18px; font-weight: bold; margin-bottom: 15px; }
            .data-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            .data-table th, .data-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            .data-table th { background-color: #f2f2f2; }
            .footer { margin-top: 40px; text-align: center; color: #7f8c8d; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Parking System Earnings Report</h1>
            <p>' . $data['periodLabel'] . '</p>
            <p>Generated on ' . date('F j, Y \a\t g:i A', strtotime($data['generatedAt'])) . '</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">₱' . number_format($data['stats']['total'], 2) . '</div>
                <div class="stat-label">Total Earnings</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">₱' . number_format($data['stats']['average'], 2) . '</div>
                <div class="stat-label">Average Per ' . ($data['period'] === 'today' ? 'Hour' : ($data['period'] === 'week' ? 'Day' : ($data['period'] === 'month' ? 'Day' : 'Month'))) . '</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">' . $data['stats']['peakHour'] . '</div>
                <div class="stat-label">Peak ' . ($data['period'] === 'today' ? 'Hour' : ($data['period'] === 'week' ? 'Day' : ($data['period'] === 'month' ? 'Day' : 'Month'))) . '</div>
            </div>
        </div>
        
        <div class="chart-section">
            <div class="chart-title">Earnings Data</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>' . ($data['period'] === 'today' ? 'Time' : ($data['period'] === 'week' ? 'Day' : ($data['period'] === 'month' ? 'Day' : 'Month'))) . '</th>
                        <th>Earnings</th>
                    </tr>
                </thead>
                <tbody>';
    
    for ($i = 0; $i < count($data['labels']); $i++) {
        $html .= '<tr><td>' . $data['labels'][$i] . '</td><td>₱' . number_format($data['data'][$i], 2) . '</td></tr>';
    }
    
    $html .= '
                </tbody>
            </table>
        </div>
        
        <div class="chart-section">
            <div class="chart-title">Additional Statistics</div>
            <table class="data-table">
                <tr><td>Total Sessions</td><td>' . $data['additionalStats']['total_sessions'] . '</td></tr>
                <tr><td>Unique Drivers</td><td>' . $data['additionalStats']['unique_drivers'] . '</td></tr>
                <tr><td>Average Payment</td><td>₱' . number_format($data['additionalStats']['avg_payment'], 2) . '</td></tr>
            </table>
        </div>
        
        <div class="footer">
            <p>Report generated by ' . $data['generatedBy'] . '</p>
            <p>Parking Management System</p>
        </div>
    </body>
    </html>';
    
    return $html;
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
