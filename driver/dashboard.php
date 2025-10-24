<?php
require_once '../backend/controllers/auth_guard.php';
requireDriver();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard - ParkSmart</title>
    <link rel="icon" type="image/svg+xml" href="../assets/Icons/favicon.svg">
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/driver/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <header class="dashboard-header">
            <div class="header-content">
                <div class="header-left">
                    <div class="logo">
                        <div class="logo-icon">
                            <div class="bar bar-1"></div>
                            <div class="bar bar-2"></div>
                            <div class="bar bar-3"></div>
                        </div>
                        <span class="logo-text">ParkSmart</span>
                    </div>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="user-details">
                            <span class="user-name" id="userName">John Doe</span>
                            <span class="user-role">Driver</span>
                        </div>
                    </div>
                    <button class="logout-btn" onclick="logout()">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="dashboard-main">
            <!-- Welcome Section -->
            <section class="welcome-section">
                <div class="welcome-content">
                    <h1 class="welcome-title">Welcome back, <span id="userFirstName">John</span>!</h1>
                    <p class="welcome-subtitle">Find parking instantly, pay with cash</p>
                </div>
                <div class="welcome-time">
                    <i class="fas fa-clock"></i>
                    <span id="currentTime"></span>
                </div>
            </section>

            <!-- Current Parking Status -->
            <section class="parking-status-section">
                <div class="parking-card" id="parkingCard">
                    <div class="parking-info" id="parkingInfo">
                        <div class="parking-icon">
                            <i class="fas fa-parking" id="parkingIcon"></i>
                        </div>
                        <div class="parking-details">
                            <h3 class="parking-title" id="parkingTitle">No Active Parking</h3>
                            <p class="parking-description" id="parkingDescription">You are not currently parked</p>
                        </div>
                    </div>
                    <div class="parking-actions" id="parkingActions">
                        <button class="btn btn-primary btn-large" onclick="findParking()">
                            <i class="fas fa-search"></i>
                            Find Parking
                        </button>
                    </div>
                </div>
            </section>

            <!-- Quick Actions -->
            <section class="quick-actions-section">
                <div class="actions-grid">
                    <div class="action-card" onclick="findParking()">
                        <div class="action-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3 class="action-title">Find Parking</h3>
                        <p class="action-description">Browse available slots and park instantly</p>
                    </div>
                    <div class="action-card" onclick="viewMyParking()">
                        <div class="action-icon">
                            <i class="fas fa-car"></i>
                        </div>
                        <h3 class="action-title">My Parking</h3>
                        <p class="action-description">View current parking status and details</p>
                    </div>
                    <div class="action-card" onclick="viewHistory()">
                        <div class="action-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <h3 class="action-title">Parking History</h3>
                        <p class="action-description">View past parking sessions and payments</p>
                    </div>
                </div>
            </section>
        </main>

        <!-- Bottom Navigation -->
        <nav class="bottom-nav">
            <a href="dashboard.php" class="nav-item active">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="find_parking.php" class="nav-item">
                <i class="fas fa-search"></i>
                <span>Find Parking</span>
            </a>
            <a href="my_parking.php" class="nav-item">
                <i class="fas fa-car"></i>
                <span>My Parking</span>
            </a>
            <a href="history.php" class="nav-item">
                <i class="fas fa-history"></i>
                <span>History</span>
            </a>
        </nav>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading...</p>
        </div>
    </div>

    <script src="../js/global.js"></script>
    <script src="../js/driver/dashboard.js?v=20251023-1"></script>
</body>
</html>
