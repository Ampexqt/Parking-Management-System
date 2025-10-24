<?php
// PDF Report Generation - Opens in new window for printing
// This approach uses browser's print to PDF functionality

// Start session
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo '<h1>Unauthorized Access</h1>';
    exit;
}

// Include database configuration
require_once '../../config/db.php';

// Get parameters
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
    $periodLabel = '';
    
    // Build query based on period (same logic as export_earnings_pdf.php)
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

    $customPeriodLabel = isset($_GET['periodLabel']) ? trim($_GET['periodLabel']) : '';
    if ($customPeriodLabel !== '') {
        $periodLabel = $customPeriodLabel;
    }

    $clientGeneratedAt = $_GET['generatedAt'] ?? null;
    $clientTimeZoneParam = $_GET['timeZone'] ?? null;

    try {
        $reportGeneratedAt = $clientGeneratedAt ? new DateTime($clientGeneratedAt) : new DateTime('now');
    } catch (Exception $e) {
        $reportGeneratedAt = new DateTime('now');
    }

    $timezoneValid = false;
    if (!empty($clientTimeZoneParam)) {
        try {
            $clientTimeZoneObj = new DateTimeZone($clientTimeZoneParam);
            $reportGeneratedAt->setTimezone($clientTimeZoneObj);
            $timezoneValid = true;
        } catch (Exception $e) {
            // Ignore invalid timezone and keep server default
        }
    }

    $generatedAtFormatted = $reportGeneratedAt->format('F j, Y \a\t g:i A');
    $generatedAtDisplay = $generatedAtFormatted;
    if ($timezoneValid) {
        $generatedAtDisplay .= ' (' . $reportGeneratedAt->format('T') . ')';
    }

} catch (Exception $e) {
    echo '<h1>Error generating report</h1><p>' . $e->getMessage() . '</p>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parking Management System Earnings Report - <?php echo htmlspecialchars($periodLabel, ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.8;
            color: #333;
            background: white;
            padding: 40px;
            margin: 0;
        }
        
        .report-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .report-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #3498db;
        }
        
        .report-title {
            color: #2c3e50;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .report-period {
            color: #3498db;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .report-date {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin: 50px 0;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            border: 2px solid #dee2e6;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 16px;
            font-weight: 500;
        }
        
        .data-section {
            margin: 60px 0;
        }
        
        .section-title {
            color: #2c3e50;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .data-table th {
            background: #3498db;
            color: white;
            font-weight: 600;
            padding: 15px;
            text-align: left;
            font-size: 16px;
        }
        
        .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
            color: #495057;
        }
        
        .data-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .data-table tr:hover {
            background: #e3f2fd;
        }
        
        .additional-stats {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            border: 2px solid #e9ecef;
            margin: 30px 0;
        }
        
        .stats-table {
            display: grid;
            gap: 15px;
        }
        
        .stat-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .stat-row:last-child {
            border-bottom: none;
        }
        
        .stat-name {
            color: #495057;
            font-weight: 600;
            font-size: 16px;
        }
        
        .stat-value-small {
            color: #2c3e50;
            font-weight: bold;
            font-size: 16px;
        }
        
        .report-footer {
            margin-top: 50px;
            text-align: center;
            padding-top: 20px;
            border-top: 2px solid #e9ecef;
            color: #7f8c8d;
        }
        
        .footer-info {
            margin: 5px 0;
        }
        
        /* Print styles */
        @media print {
            body {
                padding: 0;
            }
            
            .report-container {
                max-width: none;
                padding: 25px 30px;
            }
            
            .report-header {
                margin-bottom: 25px;
                padding-bottom: 12px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
                margin: 20px 0;
                gap: 20px;
            }
            
            .stat-card {
                break-inside: avoid;
                padding: 18px;
            }
            
            .data-table {
                break-inside: auto;
                page-break-inside: auto;
                margin: 15px 0;
            }

            .data-section {
                margin: 20px 0 15px;
            }
            
            .section-title {
                margin-bottom: 12px;
                padding-bottom: 6px;
            }
            
            .data-table th,
            .data-table td {
                padding: 10px 12px;
            }
            
            .additional-stats {
                margin: 15px 0;
            }
            
            .report-footer {
                margin-top: 30px;
                padding-top: 15px;
            }
        }
        
        @page {
            margin: 0.5in;
            size: A4;
        }
    </style>
</head>
<body>
    <div class="report-container">
        <!-- Report Header -->
        <div class="report-header">
            <h1 class="report-title">Parking Management System Earnings Report</h1>
            <p class="report-date">Generated on <?php echo htmlspecialchars($generatedAtDisplay, ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">₱<?php echo number_format((float) ($stats['total'] ?? 0), 2); ?></div>
                <div class="stat-label">Total Earnings</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">₱<?php echo number_format((float) ($stats['average'] ?? 0), 2); ?></div>
                <div class="stat-label">Average Per <?php 
                    echo $period === 'today' ? 'Hour' : 
                         ($period === 'week' ? 'Day' : 
                         ($period === 'month' ? 'Day' : 'Month')); 
                ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['peakHour']; ?></div>
                <div class="stat-label">Peak <?php 
                    echo $period === 'today' ? 'Hour' : 
                         ($period === 'week' ? 'Day' : 
                         ($period === 'month' ? 'Day' : 'Month')); 
                ?></div>
            </div>
        </div>
        
        <!-- Earnings Data Table -->
        <div class="data-section">
            <h2 class="section-title">Earnings Data</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?php echo $period === 'today' ? 'Time' : ($period === 'week' ? 'Day' : ($period === 'month' ? 'Day' : 'Month')); ?></th>
                        <th>Earnings</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $rowCount = min(count($labels), count($data)); ?>
                    <?php for ($i = 0; $i < $rowCount; $i++): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($labels[$i] ?? ('Row ' . ($i + 1))); ?></td>
                        <td>₱<?php echo number_format((float) ($data[$i] ?? 0), 2); ?></td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Additional Statistics -->
        <div class="additional-stats">
            <h3 class="section-title">Additional Statistics</h3>
            <div class="stats-table">
                <div class="stat-row">
                    <span class="stat-name">Total Sessions:</span>
                    <span class="stat-value-small"><?php echo $additionalStats['total_sessions'] ?? 0; ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-name">Unique Drivers:</span>
                    <span class="stat-value-small"><?php echo $additionalStats['unique_drivers'] ?? 0; ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-name">Average Payment:</span>
                    <span class="stat-value-small">₱<?php echo number_format((float) ($additionalStats['avg_payment'] ?? 0), 2); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Report Footer -->
        <div class="report-footer">
            <div class="footer-info">Report generated by <?php echo htmlspecialchars($_SESSION['username'] ?? 'Administrator', ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="footer-info">Parking Management System</div>
            <div class="footer-info">Generated on <?php echo htmlspecialchars($generatedAtDisplay, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
    </div>
    
    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };
        
        // Close window after printing
        window.onafterprint = function() {
            window.close();
        };
    </script>
</body>
</html>

<?php
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
