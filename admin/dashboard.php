<?php
require_once '../backend/controllers/auth_guard.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ParkSmart</title>
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/admin/dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-dashboard layout-with-sidebar">
        <!-- Sidebar Navigation -->
        <aside class="sidebar" aria-label="Sidebar Navigation">
            <div class="sidebar-header">
                <div class="logo">
                    <div class="logo-icon">
                        <div class="bar bar-1"></div>
                        <div class="bar bar-2"></div>
                        <div class="bar bar-3"></div>
                    </div>
                    <span class="logo-text">ParkSmart</span>
                </div>
            </div>
            <nav class="sidebar-nav" aria-label="Main menu">
                <a href="#" class="sidebar-link active" data-section="dashboard" aria-current="page">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="sidebar-link" data-section="slots">
                    <i class="fas fa-parking"></i>
                    <span>Manage Slots</span>
                </a>
                <a href="#" class="sidebar-link" data-section="drivers">
                    <i class="fas fa-users"></i>
                    <span>Manage Drivers</span>
                </a>
                <a href="#" class="sidebar-link" data-section="reports">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
                <a href="#" class="sidebar-link" data-section="payments">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Pending Payments</span>
                </a>
            </nav>
        </aside>
        <div class="main-area">
            <!-- Top Bar -->
            <header class="admin-topbar">
                <button class="sidebar-toggle" aria-label="Toggle menu"><i class="fas fa-bars"></i></button>
                <div class="topbar-right">
                    <div class="user-info">
                        <div class="user-avatar"><i class="fas fa-user-shield"></i></div>
                        <div class="user-details">
                            <span class="user-name" id="adminName">Administrator</span>
                        </div>
                    </div>
                    <button class="logout-btn" onclick="logout()" aria-label="Logout"><i class="fas fa-sign-out-alt"></i></button>
                </div>
            </header>
            <main class="admin-main">
                <!-- Dashboard -->
                <div class="main-section section-dashboard">
                    <!-- Welcome Section -->
                    <section class="welcome-section">
                        <div class="welcome-content">
                            <h1 class="welcome-title">Welcome back, <span id="adminFirstName">Admin</span>!</h1>
                            <p class="welcome-subtitle">Manage your parking system and monitor real-time activity</p>
                        </div>
                        <div class="welcome-time">
                            <i class="fas fa-clock"></i>
                            <span id="currentTime"></span>
                        </div>
                    </section>
                    <!-- Stats Overview -->
                    <section class="stats-section">
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon"><i class="fas fa-parking"></i></div>
                                <div class="stat-content"><h3 class="stat-number" id="totalSlots">0</h3><p class="stat-label">Total Slots</p></div>
                            </div>
                            <div class="stat-card"><div class="stat-icon available"><i class="fas fa-check-circle"></i></div>
                                <div class="stat-content"><h3 class="stat-number" id="availableSlots">0</h3><p class="stat-label">Available</p></div>
                            </div>
                            <div class="stat-card"><div class="stat-icon occupied"><i class="fas fa-times-circle"></i></div>
                                <div class="stat-content"><h3 class="stat-number" id="occupiedSlots">0</h3><p class="stat-label">Occupied</p></div>
                            </div>
                            <div class="stat-card"><div class="stat-icon maintenance"><i class="fas fa-tools"></i></div>
                                <div class="stat-content"><h3 class="stat-number" id="maintenanceSlots">0</h3><p class="stat-label">Maintenance</p></div>
                            </div>
                        </div>
                    </section>
                </div>
                <!-- Manage Slots -->
                <div class="main-section section-slots section-hidden" aria-hidden="true">
                    <section class="slots-management-section">
                        <div class="section-header">
                            <h2 class="section-title">Manage Parking Slots</h2>
                            <div class="slot-actions">
                                <button class="btn btn-outline" onclick="refreshSlots()">
                                    <i class="fas fa-sync-alt"></i> Refresh
                                </button>
                                <button class="btn btn-primary" onclick="openAddSlotModal()">
                                    <i class="fas fa-plus"></i> Add Slot
                                </button>
                            </div>
                        </div>
                        
                        <!-- Slot Statistics -->
                        <div class="slot-stats">
                            <div class="stat-item">
                                <span class="stat-number" id="totalSlotsCount">0</span>
                                <span class="stat-label">Total Slots</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number" id="availableSlotsCount">0</span>
                                <span class="stat-label">Available</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number" id="occupiedSlotsCount">0</span>
                                <span class="stat-label">Occupied</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number" id="maintenanceSlotsCount">0</span>
                                <span class="stat-label">Maintenance</span>
                            </div>
                        </div>

                        <!-- Slots Grid -->
                        <div class="slots-container">
                            <div class="slots-grid" id="slotsGrid">
                                <!-- Slots will be populated by JavaScript -->
                            </div>
                        </div>

                        <!-- Slot Details Panel -->
                        <div class="slot-details-panel" id="slotDetailsPanel" style="display: none;">
                            <div class="panel-header">
                                <h3 id="slotDetailsTitle">Slot Details</h3>
                                <button class="close-panel" onclick="closeSlotDetails()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="panel-content" id="slotDetailsContent">
                                <!-- Slot details will be populated here -->
                            </div>
                        </div>
                    </section>
                </div>
                <!-- Manage Drivers -->
                <div class="main-section section-drivers section-hidden" aria-hidden="true">
                    <section class="drivers-management-section">
                        <div class="section-header">
                            <h2 class="section-title">Manage Drivers</h2>
                            <div class="driver-actions">
                                <button class="btn btn-outline" onclick="refreshDrivers()">
                                    <i class="fas fa-sync-alt"></i> Refresh
                                </button>
                            </div>
                        </div>
                        
                        <!-- Driver Statistics -->
                        <div class="driver-stats">
                            <div class="stat-item">
                                <span class="stat-number" id="totalDriversCount">0</span>
                                <span class="stat-label">Total Drivers</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number" id="activeDriversCount">0</span>
                                <span class="stat-label">Active</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number" id="inactiveDriversCount">0</span>
                                <span class="stat-label">Inactive</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number" id="suspendedDriversCount">0</span>
                                <span class="stat-label">Suspended</span>
                            </div>
                        </div>

                        <!-- Drivers Table -->
                        <div class="drivers-container">
                            <div class="driver-filters">
                                <div class="filter-left">
                                    <div class="search-control" role="search">
                                        <span class="search-icon" aria-hidden="true"><i class="fas fa-search"></i></span>
                                        <input type="text" id="driverSearch" placeholder="Search drivers by name, email, plate..." aria-label="Search drivers">
                                        <button type="button" class="clear-search" onclick="clearDriverSearch()" aria-label="Clear search">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="filter-right">
                                    <div class="select-with-icon">
                                        <i class="fas fa-user-check" aria-hidden="true"></i>
                                        <select id="statusFilter" aria-label="Filter by status">
                                            <option value="">All Statuses</option>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                            <option value="suspended">Suspended</option>
                                        </select>
                                    </div>
                                    <div class="select-with-icon">
                                        <i class="fas fa-car-side" aria-hidden="true"></i>
                                        <select id="vehicleTypeFilter" aria-label="Filter by vehicle type">
                                            <option value="">All Vehicle Types</option>
                                            <option value="car">Car</option>
                                            <option value="suv">SUV</option>
                                            <option value="motorcycle">Motorcycle</option>
                                            <option value="truck">Truck</option>
                                            <option value="van">Van</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="table-wrapper">
                                <table class="drivers-table" id="driversTable">
                                    <thead>
                                        <tr>
                                            <th>Driver</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>Vehicle Type</th>
                                            <th>Plate Number</th>
                                            <th>Last Login</th>
                                            <th>Registered</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="driversTableBody">
                                        <tr>
                                            <td colspan="8" style="text-align: center; padding: 2rem; color: #666;">
                                                <i class="fas fa-spinner fa-spin"></i> Loading drivers...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </div>
                <!-- Reports -->
                <div class="main-section section-reports section-hidden" aria-hidden="true">
                    <!-- Daily Earnings Statistics -->
                    <section class="earnings-stats-section">
                        <div class="earnings-header">
                            <h2 class="earnings-title">Daily Earnings Statistics</h2>
                            <div class="earnings-controls">
                                <select class="earnings-period" id="earningsPeriod" onchange="updateEarningsChart()" title="Select time period for earnings data" aria-label="Select time period for earnings data">
                                    <option value="today">Today</option>
                                    <option value="week">This Week</option>
                                    <option value="month">This Month</option>
                                    <option value="year">This Year</option>
                                </select>
                                <button class="btn-export" onclick="exportEarningsData()">
                                    <i class="fas fa-download"></i>
                                    Export
                                </button>
                            </div>
                        </div>
                        
                        <!-- Earnings Stats Cards -->
                        <div class="earnings-cards">
                            <div class="earnings-card card-total">
                                <div class="card-label">Total Earnings</div>
                                <div class="card-value" id="totalEarnings">₱0</div>
                            </div>
                            <div class="earnings-card card-average">
                                <div class="card-label">Average Per Hour</div>
                                <div class="card-value" id="averagePerHour">₱0</div>
                            </div>
                            <div class="earnings-card card-peak">
                                <div class="card-label">Peak Hour</div>
                                <div class="card-value" id="peakHour">--:--</div>
                            </div>
                        </div>
                        
                        <!-- Earnings Chart -->
                        <div class="earnings-chart-container">
                            <canvas id="earningsChart"></canvas>
                        </div>
                    </section>
                    
                    <!-- Recent Activity Section -->
                    <section class="recent-activity-section">
                        <div class="section-header">
                            <h2 class="section-title">Recent Activity</h2>
                            <a href="#" class="view-all-link">View All</a>
                        </div>
                        <div class="activity-list" id="activityList">
                            <!-- Activities will be populated by JavaScript -->
                        </div>
                    </section>
                </div>
                <!-- Pending Payments -->
                <div class="main-section section-payments section-hidden" aria-hidden="true">
                    <section class="pending-payments-section">
                        <div class="section-header">
                            <h2 class="section-title">Pending Payments</h2>
                            <span class="badge" id="pendingCount">0</span>
                        </div>
                        <div class="payments-list" id="paymentsList">
                            <!-- Payments will be populated by JavaScript -->
                        </div>
                    </section>
                </div>
            </main>
        </div>
        <!-- Loading Overlay -->
        <div class="loading-overlay" id="loadingOverlay">
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading...</p>
            </div>
        </div>

    <!-- Payment Approval Modal -->
    <div class="modal" id="paymentModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Approve Payment</h3>
                <button class="modal-close" onclick="closePaymentModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="payment-details" id="paymentDetails">
                    <!-- Payment details will be populated -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closePaymentModal()">Cancel</button>
                <button class="btn btn-primary" onclick="approvePayment()">Approve</button>
            </div>
        </div>
    </div>

    <!-- Add Slot Modal -->
    <div class="modal" id="addSlotModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-plus-circle"></i> Add New Parking Slot</h3>
                <button class="modal-close" onclick="closeAddSlotModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="addSlotForm">
                    <div class="form-group">
                        <label for="slotNumber">Slot Number *</label>
                        <input type="text" id="slotNumber" name="slot_number" required placeholder="e.g., A1, B2, C3" maxlength="10">
                        <small class="form-help">Enter a unique slot identifier</small>
                    </div>
                    <div class="form-group">
                        <label for="slotStatus">Initial Status *</label>
                        <select id="slotStatus" name="status" required>
                            <option value="available">Available</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                        <small class="form-help">Choose the initial status for this slot</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAddSlotModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addSlot()">
                    <i class="fas fa-plus"></i> Add Slot
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Slot Modal -->
    <div class="modal" id="editSlotModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Edit Parking Slot</h3>
                <button class="modal-close" onclick="closeEditSlotModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="editSlotForm">
                    <input type="hidden" id="editSlotId" name="slot_id">
                    <div class="form-group">
                        <label for="editSlotNumber">Slot Number *</label>
                        <input type="text" id="editSlotNumber" name="slot_number" required maxlength="10">
                        <small class="form-help">Enter a unique slot identifier</small>
                    </div>
                    <div class="form-group">
                        <label for="editSlotStatus">Status *</label>
                        <select id="editSlotStatus" name="status" required>
                            <option value="available">Available</option>
                            <option value="occupied">Occupied</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                        <small class="form-help">Current status of this slot</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeEditSlotModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateSlot()">
                    <i class="fas fa-save"></i> Update Slot
                </button>
            </div>
        </div>
    </div>

    <!-- View Slot Details Modal -->
    <div class="modal" id="viewSlotModal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3><i class="fas fa-info-circle"></i> Slot Details</h3>
                <button class="modal-close" onclick="closeViewSlotModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="viewSlotContent">
                    <!-- Slot details will be populated here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeViewSlotModal()">Close</button>
                <button type="button" class="btn btn-primary" onclick="openEditSlotFromView()">
                    <i class="fas fa-edit"></i> Edit Slot
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Slot Confirmation Modal -->
    <div class="modal" id="deleteSlotModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Delete Slot</h3>
                <button class="modal-close" onclick="closeDeleteSlotModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="delete-warning">
                    <div class="warning-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="warning-content">
                        <h4>Are you sure you want to delete this slot?</h4>
                        <p>This action cannot be undone. The following slot will be permanently removed:</p>
                        <div class="slot-to-delete" id="slotToDelete">
                            <!-- Slot info will be populated here -->
                        </div>
                        <div class="warning-note">
                            <i class="fas fa-info-circle"></i>
                            <span>If this slot has active parking sessions, deletion will be prevented for safety.</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeDeleteSlotModal()">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteSlot()">
                    <i class="fas fa-trash"></i> Delete Slot
                </button>
            </div>
        </div>
    </div>

    <!-- View Driver Details Modal -->
    <div class="modal" id="viewDriverModal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3><i class="fas fa-info-circle"></i> Driver Details</h3>
                <button class="modal-close" onclick="closeViewDriverModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="viewDriverContent">
                    <!-- Driver details will be populated here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeViewDriverModal()">Close</button>
                <button type="button" class="btn btn-primary" onclick="openEditDriverFromView()">
                    <i class="fas fa-edit"></i> Edit Driver
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Driver Modal -->
    <div class="modal" id="editDriverModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Edit Driver</h3>
                <button class="modal-close" onclick="closeEditDriverModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="editDriverForm">
                    <input type="hidden" id="editDriverId" name="user_id">
                    <div class="form-group">
                        <label for="editDriverFirstName">First Name *</label>
                        <input type="text" id="editDriverFirstName" name="first_name" required maxlength="75">
                        <small class="form-help">Driver's first name</small>
                    </div>
                    <div class="form-group">
                        <label for="editDriverLastName">Last Name *</label>
                        <input type="text" id="editDriverLastName" name="last_name" required maxlength="75">
                        <small class="form-help">Driver's last name</small>
                    </div>
                    <div class="form-group">
                        <label for="editDriverEmail">Email *</label>
                        <input type="email" id="editDriverEmail" name="email" required maxlength="150">
                        <small class="form-help">Driver's email address</small>
                    </div>
                    <div class="form-group">
                        <label for="editDriverStatus">Status *</label>
                        <select id="editDriverStatus" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
                        <small class="form-help">Current status of this driver</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeEditDriverModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateDriver()">
                    <i class="fas fa-save"></i> Update Driver
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Driver Confirmation Modal -->
    <div class="modal" id="deleteDriverModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Delete Driver</h3>
                <button class="modal-close" onclick="closeDeleteDriverModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="delete-warning">
                    <div class="warning-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="warning-content">
                        <h4>Are you sure you want to delete this driver?</h4>
                        <p>This action cannot be undone. The following driver will be permanently removed:</p>
                        <div class="driver-to-delete" id="driverToDelete">
                            <!-- Driver info will be populated here -->
                        </div>
                        <div class="warning-note">
                            <i class="fas fa-info-circle"></i>
                            <span>If this driver has active parking sessions, deletion will be prevented for safety.</span>
                        </div>
                        <div class="warning-note">
                            <i class="fas fa-warning"></i>
                            <span>This will also delete all associated vehicles and parking history.</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeDeleteDriverModal()">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteDriver()">
                    <i class="fas fa-trash"></i> Delete Driver
                </button>
            </div>
        </div>
    </div>
    </div>
    
    <!-- Include Export Preview Modal -->
    <?php include '../modals/admin/export_preview_modal.php'; ?>
    
    <script src="../js/global.js"></script>
    <script src="../js/admin/dashboard.js"></script>
</body>
</html>
