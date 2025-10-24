// Admin Dashboard JavaScript

let statsChart = null;
let currentPaymentId = null;
let loadedDrivers = [];
let driverFiltersInitialized = false;
let slotDurationInterval = null;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard
    initDashboard();
    initRealTimeUpdates();
    initUserData();
    initChart();
    setupSidebarNavigation();
});

// Initialize dashboard
function initDashboard() {
    updateCurrentTime();
    setInterval(updateCurrentTime, 1000);
    loadDashboardData();
    loadParkingSlots();
    loadRecentActivity();
    loadPendingPayments();
}

// Update current time
function updateCurrentTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
    const dateString = now.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    document.getElementById('currentTime').textContent = `${timeString} • ${dateString}`;
}

// Load dashboard data
async function loadDashboardData() {
    try {
        const response = await fetch('../backend/api/admin/dashboard_stats.php');
        const data = await response.json();
        
        if (data.success) {
            updateStats(data.stats);
        }
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
}

// Update statistics display
function updateStats(stats) {
    document.getElementById('totalSlots').textContent = stats.total_slots || 0;
    document.getElementById('availableSlots').textContent = stats.available_slots || 0;
    document.getElementById('occupiedSlots').textContent = stats.occupied_slots || 0;
    document.getElementById('maintenanceSlots').textContent = stats.maintenance_slots || 0;
}

// Load parking slots
async function loadParkingSlots() {
    try {
        const response = await fetch('../backend/api/admin/parking_slots.php');
        const data = await response.json();
        
        if (data.success) {
            updateParkingSlots(data.slots);
            updateSlotStats(data.slots);
        }
    } catch (error) {
        console.error('Error loading parking slots:', error);
    }
}

// Update parking slots display
function updateParkingSlots(slots) {
    const slotsGrid = document.getElementById('slotsGrid');
    
    slotsGrid.innerHTML = slots.map(slot => `
        <div class="slot-item ${slot.status}" title="Slot ${slot.slot_number} - ${slot.status}">
            <div class="slot-number">${slot.slot_number}</div>
            <div class="slot-status">${slot.status}</div>
            <div class="slot-info"></div>
            <div class="slot-actions">
                <button class="slot-action-btn view" onclick="event.stopPropagation(); viewSlotDetails(${slot.slot_id})" title="View Details">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="slot-action-btn edit" onclick="event.stopPropagation(); openEditSlotModal(${slot.slot_id})" title="Edit Slot">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="slot-action-btn delete" onclick="event.stopPropagation(); openDeleteSlotModal(${slot.slot_id})" title="Delete Slot">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `).join('');
}

// Update slot statistics
function updateSlotStats(slots) {
    const stats = {
        total: slots.length,
        available: slots.filter(s => s.status === 'available').length,
        occupied: slots.filter(s => s.status === 'occupied').length,
        maintenance: slots.filter(s => s.status === 'maintenance').length
    };
    
    document.getElementById('totalSlotsCount').textContent = stats.total;
    document.getElementById('availableSlotsCount').textContent = stats.available;
    document.getElementById('occupiedSlotsCount').textContent = stats.occupied;
    document.getElementById('maintenanceSlotsCount').textContent = stats.maintenance;
}

// Toggle slot status
async function toggleSlotStatus(slotId) {
    try {
        showLoading(true);
        
        const response = await fetch('../backend/api/admin/toggle_slot.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ slot_id: slotId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Slot status updated successfully', 'success');
            loadParkingSlots();
            loadDashboardData();
        } else {
            showNotification(data.message || 'Failed to update slot status', 'error');
        }
    } catch (error) {
        console.error('Error toggling slot status:', error);
        showNotification('Network error. Please try again.', 'error');
    } finally {
        showLoading(false);
    }
}

// Load recent activity
async function loadRecentActivity() {
    try {
        const response = await fetch('../backend/api/admin/recent_activity.php');
        const data = await response.json();
        
        if (data.success) {
            updateRecentActivity(data.activities);
        }
    } catch (error) {
        console.error('Error loading recent activity:', error);
    }
}

// Update recent activity display
function updateRecentActivity(activities) {
    const activityList = document.getElementById('activityList');
    
    if (activities.length === 0) {
        activityList.innerHTML = `
            <div class="activity-item">
                <div class="activity-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="activity-details">
                    <h4 class="activity-title">No recent activity</h4>
                    <p class="activity-time">System activity will appear here</p>
                </div>
            </div>
        `;
        return;
    }
    
    activityList.innerHTML = activities.map(activity => `
        <div class="activity-item">
            <div class="activity-icon">
                <i class="fas fa-${getActivityIcon(activity.type)}"></i>
            </div>
            <div class="activity-details">
                <h4 class="activity-title">${activity.title}</h4>
                <p class="activity-time">${activity.time_ago}</p>
            </div>
        </div>
    `).join('');
}

// Get activity icon based on type
function getActivityIcon(type) {
    const icons = {
        'park': 'parking',
        'exit': 'sign-out-alt',
        'payment': 'money-bill-wave',
        'login': 'sign-in-alt',
        'slot_change': 'parking',
        'driver_register': 'user-plus'
    };
    return icons[type] || 'info-circle';
}

// Load pending payments
async function loadPendingPayments() {
    try {
        const response = await fetch('../backend/api/admin/pending_payments.php');
        const data = await response.json();
        
        if (data.success) {
            updatePendingPayments(data.payments);
        }
    } catch (error) {
        console.error('Error loading pending payments:', error);
    }
}

// Update pending payments display
function updatePendingPayments(payments) {
    const paymentsList = document.getElementById('paymentsList');
    const pendingCount = document.getElementById('pendingCount');
    
    pendingCount.textContent = payments.length;
    
    if (payments.length === 0) {
        paymentsList.innerHTML = `
            <div class="payment-item">
                <div class="payment-details">
                    <h4 class="payment-amount">No pending payments</h4>
                    <p class="payment-info">All payments have been processed</p>
                </div>
            </div>
        `;
        return;
    }
    
    paymentsList.innerHTML = payments.map(payment => `
        <div class="payment-item" onclick="openPaymentModal(${payment.payment_id})">
            <div class="payment-details">
                <h4 class="payment-amount">₱${Number(payment.amount).toFixed(2)}</h4>
                <p class="payment-info">Slot ${payment.slot_number} • ${payment.driver_name} • ${payment.time_ago} • ${Number(payment.amount).toFixed(2)} pesos</p>
            </div>
            <div class="payment-actions">
                <i class="fas fa-chevron-right"></i>
            </div>
        </div>
    `).join('');
}

// Open payment approval modal
async function openPaymentModal(paymentId) {
    try {
        const response = await fetch(`../backend/api/admin/payment_details.php?id=${paymentId}`);
        const data = await response.json();
        
        if (data.success) {
            currentPaymentId = paymentId;
            updatePaymentModal(data.payment);
            document.getElementById('paymentModal').classList.add('show');
        } else {
            showNotification(data.message || 'Failed to load payment details', 'error');
        }
    } catch (error) {
        console.error('Error loading payment details:', error);
        showNotification('Network error. Please try again.', 'error');
    }
}

// Update payment modal content
function updatePaymentModal(payment) {
    const paymentDetails = document.getElementById('paymentDetails');
    const formattedAmount = `₱${Number(payment.amount).toFixed(2)}`;
    const vehiclePlate = payment.vehicle_plate || '—';
    const vehicleType = payment.vehicle_type || '—';
    const vehicleColor = payment.vehicle_color ? payment.vehicle_color : null;

    paymentDetails.innerHTML = `
        <div class="payment-summary">
            <h4>Payment Details</h4>
            <div class="payment-info-grid">
                <div class="info-item">
                    <label>Amount:</label>
                    <span>${formattedAmount}</span>
                </div>
                <div class="info-item">
                    <label>Driver:</label>
                    <span>${payment.driver_name}</span>
                </div>
                <div class="info-item">
                    <label>Vehicle Plate:</label>
                    <span>${vehiclePlate}</span>
                </div>
                <div class="info-item">
                    <label>Vehicle Type:</label>
                    <span>${vehicleType}</span>
                </div>
                ${vehicleColor ? `
                <div class="info-item">
                    <label>Vehicle Color:</label>
                    <span>${vehicleColor}</span>
                </div>` : ''}
                <div class="info-item">
                    <label>Slot:</label>
                    <span>${payment.slot_number}</span>
                </div>
                <div class="info-item">
                    <label>Duration:</label>
                    <span>${payment.duration}</span>
                </div>
                <div class="info-item">
                    <label>Requested:</label>
                    <span>${payment.requested_time}</span>
                </div>
            </div>
        </div>
    `;
}

// Close payment modal
function closePaymentModal() {
    document.getElementById('paymentModal').classList.remove('show');
    currentPaymentId = null;
}

// Approve payment
async function approvePayment() {
    if (!currentPaymentId) return;
    
    try {
        showLoading(true);
        
        const response = await fetch('../backend/api/admin/approve_payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ payment_id: currentPaymentId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Payment approved successfully', 'success');
            closePaymentModal();
            loadPendingPayments();
            loadRecentActivity();
        } else {
            showNotification(data.message || 'Failed to approve payment', 'error');
        }
    } catch (error) {
        console.error('Error approving payment:', error);
        showNotification('Network error. Please try again.', 'error');
    } finally {
        showLoading(false);
    }
}

// Decline payment
async function declinePayment() {
    if (!currentPaymentId) return;
    
    try {
        showLoading(true);
        
        const response = await fetch('../backend/api/admin/decline_payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ payment_id: currentPaymentId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Payment declined', 'success');
            closePaymentModal();
            loadPendingPayments();
            loadRecentActivity();
        } else {
            showNotification(data.message || 'Failed to decline payment', 'error');
        }
    } catch (error) {
        console.error('Error declining payment:', error);
        showNotification('Network error. Please try again.', 'error');
    } finally {
        showLoading(false);
    }
}

// Initialize chart
function initChart() {
    const chartElement = document.getElementById('statsChart');
    
    // Check if the old statsChart element exists (it's been replaced with earningsChart)
    if (!chartElement) {
        console.log('statsChart element not found - using new earnings chart instead');
        return;
    }
    
    const ctx = chartElement.getContext('2d');
    
    statsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Parking Sessions',
                data: [],
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    updateChart();
}

// Update chart
async function updateChart() {
    const periodElement = document.getElementById('chartPeriod');
    
    // Check if element exists and statsChart is initialized
    if (!periodElement || !statsChart) {
        console.log('Chart elements not found - using new earnings chart instead');
        return;
    }
    
    const period = periodElement.value;
    
    try {
        const response = await fetch(`../backend/api/admin/chart_data.php?period=${period}`);
        const data = await response.json();
        
        if (data.success) {
            statsChart.data.labels = data.labels;
            statsChart.data.datasets[0].data = data.data;
            statsChart.update();
        }
    } catch (error) {
        console.error('Error loading chart data:', error);
    }
}

// Load user data
async function initUserData() {
    try {
        const response = await fetch('../backend/api/user_info.php');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('adminName').textContent = 'Administrator';
            document.getElementById('adminFirstName').textContent = data.user.first_name;
        }
    } catch (error) {
        console.error('Error loading user data:', error);
    }
}

// Real-time updates
function initRealTimeUpdates() {
    // Update dashboard data every 30 seconds
    setInterval(loadDashboardData, 30000);
    
    // Update parking slots every 30 seconds
    setInterval(loadParkingSlots, 30000);
    
    // Update recent activity every 60 seconds
    setInterval(loadRecentActivity, 60000);
    
    // Update pending payments every 30 seconds
    setInterval(loadPendingPayments, 30000);
}

// Navigation functions
function manageSlots() {
    window.location.href = 'manage_slots.php';
}

function manageDrivers() {
    window.location.href = 'manage_drivers.php';
}

function viewReports() {
    window.location.href = 'reports.php';
}

function approvePayments() {
    // Scroll to pending payments section
    document.querySelector('.pending-payments-section').scrollIntoView({ behavior: 'smooth' });
}

function refreshSlots() {
    loadParkingSlots();
    showNotification('Slots refreshed', 'success');
}

// Note: Do not define a function named `logout` here; use the global modal-based one from js/global.js

// Show loading overlay
function showLoading(show) {
    const overlay = document.getElementById('loadingOverlay');
    if (show) {
        overlay.classList.add('show');
    } else {
        overlay.classList.remove('show');
    }
}

// Show notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'error' ? 'exclamation-circle' : type === 'success' ? 'check-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // Remove notification after 5 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 5000);
}

// Add notification styles
const style = document.createElement('style');
style.textContent = `
    .notification {
        position: fixed;
        top: 2rem;
        right: 2rem;
        background-color: #ffffff;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        padding: 1rem 1.5rem;
        z-index: 1000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        max-width: 300px;
    }
    
    .notification.show {
        transform: translateX(0);
    }
    
    .notification-error {
        border-left: 4px solid #ef4444;
    }
    
    .notification-success {
        border-left: 4px solid #10b981;
    }
    
    .notification-info {
        border-left: 4px solid #2563eb;
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .notification-content i {
        color: #ef4444;
    }
    
    .notification-success .notification-content i {
        color: #10b981;
    }
    
    .notification-info .notification-content i {
        color: #2563eb;
    }
    
    .payment-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-top: 1rem;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .info-item label {
        font-weight: 500;
        color: #64748b;
        font-size: 0.875rem;
    }
    
    .info-item span {
        color: #1e293b;
        font-weight: 500;
    }
    
    @media (max-width: 768px) {
        .notification {
            top: 1rem;
            right: 1rem;
            left: 1rem;
            max-width: none;
        }
        
        .payment-info-grid {
            grid-template-columns: 1fr;
        }
    }
`;
document.head.appendChild(style);

// ===== CRUD MODAL FUNCTIONS =====

// Global variables for current slot
let currentSlotId = null;
let currentSlotData = null;

// CREATE - Add Slot Modal Functions
function openAddSlotModal() {
    document.getElementById('addSlotModal').classList.add('show');
    document.getElementById('addSlotForm').reset();
}

function closeAddSlotModal() {
    document.getElementById('addSlotModal').classList.remove('show');
    document.getElementById('addSlotForm').reset();
}

async function addSlot() {
    const form = document.getElementById('addSlotForm');
    const formData = new FormData(form);
    
    // Client-side validation
    const slotNumber = formData.get('slot_number').trim();
    if (!slotNumber) {
        showNotification('Slot number is required', 'error');
        return;
    }
    
    try {
        showLoading(true);
        
        const response = await fetch('../backend/api/admin/add_slot.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Slot added successfully', 'success');
            closeAddSlotModal();
            loadParkingSlots();
        } else {
            showNotification(data.message || 'Failed to add slot', 'error');
        }
    } catch (error) {
        console.error('Error adding slot:', error);
        showNotification('Network error. Please try again.', 'error');
    } finally {
        showLoading(false);
    }
}

// READ - View Slot Details Modal Functions
async function viewSlotDetails(slotId) {
    try {
        showLoading(true);
        
        const response = await fetch(`../backend/api/admin/slot_details.php?id=${slotId}`);
        const data = await response.json();
        
        if (data.success) {
            currentSlotData = data.slot;
            updateViewSlotModal(data.slot);
            document.getElementById('viewSlotModal').classList.add('show');
        } else {
            showNotification(data.message || 'Failed to load slot details', 'error');
        }
    } catch (error) {
        console.error('Error loading slot details:', error);
        showNotification('Network error. Please try again.', 'error');
    } finally {
        showLoading(false);
    }
}

function updateViewSlotModal(slot) {
    const content = document.getElementById('viewSlotContent');
    clearSlotDurationTimer();

    content.innerHTML = `
        <div class="slot-details-view">
            <div class="slot-info-section">
                <h3 class="section-title">
                    <i class="fas fa-parking"></i>
                    Slot Information
                </h3>
                <div class="detail-row">
                    <span class="detail-label">Slot Number:</span>
                    <span class="detail-value">${slot.slot_number}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="status-badge ${slot.status}">${slot.status}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Last Updated:</span>
                    <span class="detail-value">${slot.last_updated}</span>
                </div>
            </div>
            
            <div class="session-info-section">
                <h3 class="section-title">
                    <i class="fas fa-car"></i>
                    Current Session
                </h3>
                ${slot.current_session ? `
                    <div class="detail-row">
                        <span class="detail-label">Driver:</span>
                        <span class="detail-value">${slot.current_session.driver_name}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Vehicle:</span>
                        <span class="detail-value">${slot.current_session.vehicle_plate}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Type:</span>
                        <span class="detail-value">${slot.current_session.vehicle_type}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Parked Since:</span>
                        <span class="detail-value">${slot.current_session.start_time}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Duration:</span>
                        <span class="detail-value session-duration-value" id="sessionDurationValue">--h --m --s</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Session Status:</span>
                        <span class="status-badge ${slot.current_session.session_status}">${slot.current_session.session_status}</span>
                    </div>
                ` : `
                    <div class="no-session">
                        <i class="fas fa-info-circle"></i>
                        <p>No active parking session</p>
                    </div>
                `}
            </div>
        </div>
    `;

    if (slot.current_session && slot.current_session.start_time_iso) {
        const durationEl = document.getElementById('sessionDurationValue');
        const durationOptions = {
            startIso: slot.current_session.start_time_iso,
            serverIso: slot.current_session.server_time_iso,
            initialSeconds: slot.current_session.duration_seconds || 0
        };
        updateSessionDuration(durationEl, durationOptions);
        slotDurationInterval = setInterval(() => {
            updateSessionDuration(durationEl, durationOptions);
        }, 1000);
    }
}

function closeViewSlotModal() {
    document.getElementById('viewSlotModal').classList.remove('show');
    currentSlotData = null;
    clearSlotDurationTimer();
}

function openEditSlotFromView() {
    closeViewSlotModal();
    if (currentSlotData) {
        openEditSlotModal(currentSlotData.slot_id);
    }
}

function clearSlotDurationTimer() {
    if (slotDurationInterval) {
        clearInterval(slotDurationInterval);
        slotDurationInterval = null;
    }
    const durationEl = document.getElementById('sessionDurationValue');
    if (durationEl) {
        delete durationEl.dataset.startMs;
        delete durationEl.dataset.serverOffset;
    }
}

function updateSessionDuration(element, options) {
    if (!element || !options) return;

    const { startIso, serverIso, initialSeconds = 0 } = options;

    let startMs = element.dataset.startMs ? parseInt(element.dataset.startMs, 10) : NaN;
    if (Number.isNaN(startMs)) {
        const startTime = startIso ? new Date(startIso) : null;
        if (!startTime || Number.isNaN(startTime.getTime())) {
            element.textContent = '--h --m --s';
            return;
        }
        startMs = startTime.getTime();
        element.dataset.startMs = String(startMs);
    }

    if (!element.dataset.serverOffset && serverIso) {
        const serverTime = new Date(serverIso);
        if (!Number.isNaN(serverTime.getTime())) {
            element.dataset.serverOffset = String(serverTime.getTime() - Date.now());
        }
    }

    let nowMs = Date.now();
    if (element.dataset.serverOffset) {
        const offset = parseInt(element.dataset.serverOffset, 10);
        if (!Number.isNaN(offset)) {
            nowMs += offset;
        }
    }

    let totalSeconds = Math.floor((nowMs - startMs) / 1000);
    if (!Number.isFinite(totalSeconds) || totalSeconds < 0) {
        totalSeconds = initialSeconds;
    } else if (totalSeconds < initialSeconds) {
        totalSeconds = initialSeconds;
    }

    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    element.textContent = `${hours.toString().padStart(2, '0')}h ${minutes
        .toString()
        .padStart(2, '0')}m ${seconds.toString().padStart(2, '0')}s`;
}

// UPDATE - Edit Slot Modal Functions
async function openEditSlotModal(slotId) {
    try {
        showLoading(true);
        
        const response = await fetch(`../backend/api/admin/slot_details.php?id=${slotId}`);
        const data = await response.json();
        
        if (data.success) {
            currentSlotId = slotId;
            populateEditSlotForm(data.slot);
            document.getElementById('editSlotModal').classList.add('show');
        } else {
            showNotification(data.message || 'Failed to load slot details', 'error');
        }
    } catch (error) {
        console.error('Error loading slot details:', error);
        showNotification('Network error. Please try again.', 'error');
    } finally {
        showLoading(false);
    }
}

function populateEditSlotForm(slot) {
    document.getElementById('editSlotId').value = slot.slot_id;
    document.getElementById('editSlotNumber').value = slot.slot_number;
    document.getElementById('editSlotStatus').value = slot.status;
}

function closeEditSlotModal() {
    document.getElementById('editSlotModal').classList.remove('show');
    document.getElementById('editSlotForm').reset();
    currentSlotId = null;
}

async function updateSlot() {
    const form = document.getElementById('editSlotForm');
    const formData = new FormData(form);
    
    // Client-side validation
    const slotNumber = formData.get('slot_number').trim();
    if (!slotNumber) {
        showNotification('Slot number is required', 'error');
        return;
    }
    
    try {
        showLoading(true);
        
        const response = await fetch('../backend/api/admin/update_slot.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Slot updated successfully', 'success');
            closeEditSlotModal();
            loadParkingSlots();
        } else {
            showNotification(data.message || 'Failed to update slot', 'error');
        }
    } catch (error) {
        console.error('Error updating slot:', error);
        showNotification('Network error. Please try again.', 'error');
    } finally {
        showLoading(false);
    }
}

// DELETE - Delete Slot Modal Functions
async function openDeleteSlotModal(slotId) {
    try {
        showLoading(true);
        
        const response = await fetch(`../backend/api/admin/slot_details.php?id=${slotId}`);
        const data = await response.json();
        
        if (data.success) {
            currentSlotId = slotId;
            updateDeleteModal(data.slot);
            document.getElementById('deleteSlotModal').classList.add('show');
        } else {
            showNotification(data.message || 'Failed to load slot details', 'error');
        }
    } catch (error) {
        console.error('Error loading slot details:', error);
        showNotification('Network error. Please try again.', 'error');
    } finally {
        showLoading(false);
    }
}

function updateDeleteModal(slot) {
    const slotToDelete = document.getElementById('slotToDelete');
    
    slotToDelete.innerHTML = `
        <div class="slot-info">
            <span class="slot-number">${slot.slot_number}</span>
            <span class="slot-status ${slot.status}">${slot.status}</span>
        </div>
    `;
}

function closeDeleteSlotModal() {
    document.getElementById('deleteSlotModal').classList.remove('show');
    currentSlotId = null;
}

async function confirmDeleteSlot() {
    if (!currentSlotId) return;
    
    try {
        showLoading(true);
        
        const response = await fetch('../backend/api/admin/delete_slot.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ slot_id: currentSlotId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Slot deleted successfully', 'success');
            closeDeleteSlotModal();
            loadParkingSlots();
        } else {
            showNotification(data.message || 'Failed to delete slot', 'error');
        }
    } catch (error) {
        console.error('Error deleting slot:', error);
        showNotification('Network error. Please try again.', 'error');
    } finally {
        showLoading(false);
    }
}

// Refresh slots function
function refreshSlots() {
    loadParkingSlots();
    showNotification('Slots refreshed', 'success');
}

function setupSidebarNavigation() {
    // Elements
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    
    // Check if sidebar exists
    if (!sidebar) {
        console.warn('Sidebar not found');
        return;
    }
    
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);
    
    // Toggle sidebar on mobile
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('sidebar-open');
            overlay.style.display = sidebar.classList.contains('sidebar-open') ? 'block' : 'none';
        });
    }
    
    // Close sidebar when overlay is clicked
    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('sidebar-open');
            overlay.style.display = 'none';
        });
    }
    
    // Sidebar nav link click
    const sidebarLinks = document.querySelectorAll('.sidebar-link');
    if (sidebarLinks.length > 0) {
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
                link.classList.add('active');
                // Switch section
                showSection(link.getAttribute('data-section'));
                // Close sidebar if mobile
                if (window.innerWidth < 950) {
                    sidebar.classList.remove('sidebar-open');
                    overlay.style.display = 'none';
                }
            });
        });
    }
    
    // Default: show dashboard
    showSection('dashboard');
}

function showSection(section) {
    // Hide all main sections
    document.querySelectorAll('.main-section').forEach(sec => {
        sec.classList.add('section-hidden');
        sec.setAttribute('aria-hidden', 'true');
    });
    // Show relevant section
    const toShow = document.querySelector('.section-' + section);
    if (toShow) {
        toShow.classList.remove('section-hidden');
        toShow.setAttribute('aria-hidden', 'false');
        
        // Load drivers when drivers section is shown
        if (toShow.classList.contains('section-drivers')) {
            loadDrivers();
        }
    }
}

function initDriverFilters() {
    if (driverFiltersInitialized) return;
    const search = document.getElementById('driverSearch');
    const status = document.getElementById('statusFilter');
    const vtype = document.getElementById('vehicleTypeFilter');
    if (search) search.addEventListener('input', applyDriverFilters);
    if (status) status.addEventListener('change', applyDriverFilters);
    if (vtype) vtype.addEventListener('change', applyDriverFilters);
    driverFiltersInitialized = true;
}

function applyDriverFilters() {
    const search = (document.getElementById('driverSearch')?.value || '').trim().toLowerCase();
    const status = (document.getElementById('statusFilter')?.value || '').trim().toLowerCase();
    const vtype = (document.getElementById('vehicleTypeFilter')?.value || '').trim().toLowerCase();
    let filtered = loadedDrivers.slice();
    if (search) {
        filtered = filtered.filter(d => {
            const name = `${d.full_name || ''}`.toLowerCase();
            const email = `${d.email || ''}`.toLowerCase();
            const plate = `${d.plate_number || ''}`.toLowerCase();
            const vt = `${d.vehicle_type || ''}`.toLowerCase();
            return name.includes(search) || email.includes(search) || plate.includes(search) || vt.includes(search);
        });
    }
    if (status) {
        filtered = filtered.filter(d => (d.status || '').toLowerCase() === status);
    }
    if (vtype) {
        filtered = filtered.filter(d => (d.vehicle_type || '').toLowerCase() === vtype);
    }
    updateDriversTable(filtered);
}

function clearDriverSearch() {
    const el = document.getElementById('driverSearch');
    if (el) {
        el.value = '';
        applyDriverFilters();
        el.focus();
    }
}

// ===== DRIVER MANAGEMENT FUNCTIONS =====

// Load drivers from API
async function loadDrivers() {
    try {
        console.log('Loading drivers...');
        showLoading(true);
        
        const response = await fetch('../backend/api/admin/drivers.php');
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('API Response:', data);
        
        if (data.success) {
            loadedDrivers = data.drivers || [];
            updateDriverStats(loadedDrivers);
            initDriverFilters();
            applyDriverFilters();
        } else {
            console.error('API Error:', data.message);
            showNotification(data.message || 'Failed to load drivers', 'error');
        }
    } catch (error) {
        console.error('Error loading drivers:', error);
        showNotification('Network error. Please try again.', 'error');
    } finally {
        showLoading(false);
    }
}

// Update drivers table
function updateDriversTable(drivers) {
    console.log('Updating drivers table with:', drivers);
    const tableBody = document.getElementById('driversTableBody');
    
    if (!tableBody) {
        console.error('Table body element not found!');
        return;
    }
    
    if (drivers.length === 0) {
        console.log('No drivers found, showing empty state');
        tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3>No drivers found</h3>
                    <p>No drivers have been registered yet</p>
                </td>
            </tr>
        `;
        return;
    }
    
    console.log('Rendering drivers table with', drivers.length, 'drivers');
    tableBody.innerHTML = drivers.map(driver => `
        <tr>
            <td>
                <div class="driver-info">
                    <div class="driver-avatar">
                        ${driver.first_name.charAt(0).toUpperCase()}${driver.last_name.charAt(0).toUpperCase()}
                    </div>
                    <div class="driver-details">
                        <h4>${driver.full_name}</h4>
                        <p>ID: ${driver.user_id}</p>
                    </div>
                </div>
            </td>
            <td>${driver.email}</td>
            <td>
                <span class="status-badge ${driver.status}">${driver.status}</span>
            </td>
            <td>${driver.vehicle_type ? formatVehicleType(driver.vehicle_type) : '-'}</td>
            <td>${driver.plate_number ? driver.plate_number : '-'}</td>
            <td>${driver.last_login}</td>
            <td>${driver.date_registered}</td>
            <td>
                <div class="driver-actions-cell">
                    <button class="action-btn view" onclick="viewDriverDetails(${driver.user_id})" title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="action-btn edit" onclick="openEditDriverModal(${driver.user_id})" title="Edit Driver">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete" onclick="openDeleteDriverModal(${driver.user_id})" title="Delete Driver">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Update driver statistics
function updateDriverStats(drivers) {
    const stats = {
        total: drivers.length,
        active: drivers.filter(d => d.status === 'active').length,
        inactive: drivers.filter(d => d.status === 'inactive').length,
        suspended: drivers.filter(d => d.status === 'suspended').length
    };
    
    document.getElementById('totalDriversCount').textContent = stats.total;
    document.getElementById('activeDriversCount').textContent = stats.active;
    document.getElementById('inactiveDriversCount').textContent = stats.inactive;
    document.getElementById('suspendedDriversCount').textContent = stats.suspended;
}

// Test API function
async function testDriverAPI() {
    try {
        console.log('Testing driver API...');
        showLoading(true);
        
        const response = await fetch('../backend/api/admin/drivers.php');
        console.log('Test API Response status:', response.status);
        
        const data = await response.json();
        console.log('Test API Response:', data);
        
        if (data.success) {
            showNotification(`API Test Successful! Found ${data.drivers.length} drivers`, 'success');
        } else {
            showNotification(`API Test Failed: ${data.message}`, 'error');
        }
    } catch (error) {
        console.error('API Test Error:', error);
        showNotification('API Test Failed: ' + error.message, 'error');
    } finally {
        showLoading(false);
    }
}

// Refresh drivers function
function refreshDrivers() {
    loadDrivers();
    showNotification('Drivers refreshed', 'success');
}

// ===== DRIVER MODAL FUNCTIONS =====

// READ - View Driver Details Modal Functions
async function viewDriverDetails(driverId) {
    try {
        showLoading(true);
        
        const response = await fetch(`../backend/api/admin/driver_details.php?id=${driverId}`);
        const data = await response.json();
        
        if (data.success) {
            currentDriverData = data.driver;
            updateViewDriverModal(data.driver);
            document.getElementById('viewDriverModal').classList.add('show');
        } else {
            showNotification(data.message || 'Failed to load driver details', 'error');
        }
    } catch (error) {
        console.error('Error loading driver details:', error);
        showNotification('Network error. Please try again.', 'error');
    } finally {
        showLoading(false);
    }
}

function updateViewDriverModal(driver) {
    const content = document.getElementById('viewDriverContent');
    
    content.innerHTML = `
        <div class="driver-details-view">
            <div class="driver-info-section">
                <h3 class="section-title">
                    <i class="fas fa-user"></i>
                    Driver Information
                </h3>
                <div class="detail-row">
                    <span class="detail-label">Full Name:</span>
                    <span class="detail-value">${driver.full_name}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value">${driver.email}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="status-badge ${driver.status}">${driver.status}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Last Login:</span>
                    <span class="detail-value">${driver.last_login}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Registered:</span>
                    <span class="detail-value">${driver.date_registered}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Last Updated:</span>
                    <span class="detail-value">${driver.updated_at}</span>
                </div>
            </div>
            
            <div class="vehicles-section">
                <h3 class="section-title">
                    <i class="fas fa-car"></i>
                    Vehicles (${driver.vehicles.length})
                </h3>
                ${driver.vehicles.length > 0 ? `
                    <div class="vehicle-list">
                        ${driver.vehicles.slice(0, 3).map(vehicle => `
                            <div class="vehicle-item">
                                <div class="vehicle-info">
                                    <div class="vehicle-plate">${vehicle.plate_number}</div>
                                    <div class="vehicle-type">${formatVehicleType(vehicle.vehicle_type)} • ${vehicle.color}</div>
                                </div>
                                <span class="vehicle-status ${vehicle.is_active ? 'active' : 'inactive'}">
                                    ${vehicle.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </div>
                        `).join('')}
                        ${driver.vehicles.length > 3 ? `
                            <div class="no-data" style="padding: 0.25rem; font-size: 0.7rem;">
                                <i class="fas fa-ellipsis-h"></i>
                                <p>+${driver.vehicles.length - 3} more</p>
                            </div>
                        ` : ''}
                    </div>
                ` : `
                    <div class="no-data">
                        <i class="fas fa-info-circle"></i>
                        <p>No vehicles registered</p>
                    </div>
                `}
            </div>
        </div>
    `;
}

function closeViewDriverModal() {
    document.getElementById('viewDriverModal').classList.remove('show');
    currentDriverData = null;
}

function openEditDriverFromView() {
    closeViewDriverModal();
    if (currentDriverData) {
        openEditDriverModal(currentDriverData.user_id);
    }
}

// UPDATE - Edit Driver Modal Functions
async function openEditDriverModal(driverId) {
    try {
        showLoading(true);
        
        const response = await fetch(`../backend/api/admin/driver_details.php?id=${driverId}`);
        const data = await response.json();
        
        if (data.success) {
            currentDriverId = driverId;
            populateEditDriverForm(data.driver);
            document.getElementById('editDriverModal').classList.add('show');
        } else {
            showNotification(data.message || 'Failed to load driver details', 'error');
        }
    } catch (error) {
        console.error('Error loading driver details:', error);
        showNotification('Network error. Please try again.', 'error');
    } finally {
        showLoading(false);
    }
}

function populateEditDriverForm(driver) {
    document.getElementById('editDriverId').value = driver.user_id;
    document.getElementById('editDriverFirstName').value = driver.first_name;
    document.getElementById('editDriverLastName').value = driver.last_name;
    document.getElementById('editDriverEmail').value = driver.email;
    document.getElementById('editDriverStatus').value = driver.status;
}

function closeEditDriverModal() {
    document.getElementById('editDriverModal').classList.remove('show');
    document.getElementById('editDriverForm').reset();
    currentDriverId = null;
}

async function updateDriver() {
    const form = document.getElementById('editDriverForm');
    const formData = new FormData(form);
    
    // Client-side validation
    const firstName = formData.get('first_name').trim();
    const lastName = formData.get('last_name').trim();
    const email = formData.get('email').trim();
    
    if (!firstName || !lastName || !email) {
        showNotification('All fields are required', 'error');
        return;
    }
    
    if (!isValidEmail(email)) {
        showNotification('Please enter a valid email address', 'error');
        return;
    }
    
    try {
        showLoading(true);
        
        const response = await fetch('../backend/api/admin/update_driver.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Driver updated successfully', 'success');
            closeEditDriverModal();
            loadDrivers();
        } else {
            showNotification(data.message || 'Failed to update driver', 'error');
        }
    } catch (error) {
        console.error('Error updating driver:', error);
        showNotification('Network error. Please try again.', 'error');
    } finally {
        showLoading(false);
    }
}

// DELETE - Delete Driver Modal Functions
async function openDeleteDriverModal(driverId) {
    try {
        showLoading(true);
        
        const response = await fetch(`../backend/api/admin/driver_details.php?id=${driverId}`);
        const data = await response.json();
        
        if (data.success) {
            currentDriverId = driverId;
            updateDeleteModal(data.driver);
            document.getElementById('deleteDriverModal').classList.add('show');
        } else {
            showNotification(data.message || 'Failed to load driver details', 'error');
        }
    } catch (error) {
        console.error('Error loading driver details:', error);
        showNotification('Network error. Please try again.', 'error');
    } finally {
        showLoading(false);
    }
}

function updateDeleteModal(driver) {
    const driverToDelete = document.getElementById('driverToDelete');
    
    driverToDelete.innerHTML = `
        <div class="driver-info">
            <span class="driver-name">${driver.full_name}</span>
            <span class="driver-status ${driver.status}">${driver.status}</span>
        </div>
        <div style="margin-top: 0.5rem; font-size: 0.9rem; color: var(--text-light);">
            Email: ${driver.email}
        </div>
    `;
}

function closeDeleteDriverModal() {
    document.getElementById('deleteDriverModal').classList.remove('show');
    currentDriverId = null;
}

async function confirmDeleteDriver() {
    if (!currentDriverId) return;
    
    try {
        showLoading(true);
        
        const response = await fetch('../backend/api/admin/delete_driver.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ user_id: currentDriverId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Driver deleted successfully', 'success');
            closeDeleteDriverModal();
            loadDrivers();
        } else {
            showNotification(data.message || 'Failed to delete driver', 'error');
        }
    } catch (error) {
        console.error('Error deleting driver:', error);
        showNotification('Network error. Please try again.', 'error');
    } finally {
        showLoading(false);
    }
}

// Utility function for email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Vehicle type formatter
function formatVehicleType(type) {
    if (!type && type !== 0) return '-';
    const t = String(type).trim().toLowerCase();
    const map = {
        'suv': 'SUV',
        'atv': 'ATV',
        'utv': 'UTV',
        'car': 'Car',
        'van': 'Van',
        'truck': 'Truck',
        'pickup': 'Pickup',
        'pickup_truck': 'Pickup Truck',
        'motorcycle': 'Motorcycle',
        'motorbike': 'Motorcycle',
        'bike': 'Bicycle',
        'bicycle': 'Bicycle',
        'bus': 'Bus'
    };
    if (map[t]) return map[t];
    return t.charAt(0).toUpperCase() + t.slice(1);
}

function formatTimeTo12Hour(time) {
    if (!time) return '--:--';
    const parts = time.split(':');
    if (parts.length < 2) return time;
    let hour = parseInt(parts[0], 10);
    if (Number.isNaN(hour)) return time;
    const minutes = parts[1];
    const period = hour >= 12 ? 'PM' : 'AM';
    hour = hour % 12;
    if (hour === 0) hour = 12;
    return `${hour}:${minutes} ${period}`;
}

// ===== EARNINGS STATISTICS FUNCTIONS =====

let earningsChart = null;
let latestExportData = null;

// Initialize earnings chart when reports section is shown
function initEarningsChart() {
    if (earningsChart) {
        earningsChart.destroy();
    }
    
    const ctx = document.getElementById('earningsChart');
    if (!ctx) return;
    
    earningsChart = new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Earnings',
                data: [],
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                borderWidth: 2.5,
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#2563eb',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointHoverBackgroundColor: '#2563eb',
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 13,
                        weight: '600'
                    },
                    bodyFont: {
                        size: 12
                    },
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return '₱' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        },
                        color: '#64748b',
                        maxRotation: 0,
                        minRotation: 0,
                        autoSkip: true,
                        maxTicksLimit: 12,
                        callback: function(value, index, ticks) {
                            const label = this.getLabelForValue(value);
                            // For hourly data (today view), show every 2 hours
                            if (label && label.includes(':')) {
                                const hour = parseInt(label.split(':')[0]);
                                if (hour % 2 === 0) {
                                    return label;
                                }
                                return '';
                            }
                            return label;
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(37, 99, 235, 0.08)',
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        },
                        color: '#64748b',
                        callback: function(value) {
                            // Dynamic formatting based on value size
                            if (value >= 1000000) {
                                return '₱' + (value / 1000000).toFixed(1) + 'M';
                            } else if (value >= 1000) {
                                return '₱' + (value / 1000).toFixed(0) + 'k';
                            } else if (value > 0) {
                                return '₱' + value.toFixed(0);
                            } else {
                                return '₱0';
                            }
                        }
                    }
                }
            }
        }
    });
    
    // Load initial data
    updateEarningsChart();
}

// Update earnings chart based on selected period
async function updateEarningsChart() {
    const period = document.getElementById('earningsPeriod')?.value || 'today';
    
    try {
        // Fetch real earnings data from backend
        const response = await fetch(`../backend/api/admin/earnings_data.php?period=${period}`);
        
        // Check if response is ok
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            if (earningsChart) {
                earningsChart.data.labels = result.labels;
                earningsChart.data.datasets[0].data = result.data;
                earningsChart.update('none');
            }
            
            // Update stat cards
            updateEarningsStats(result.stats);
        } else {
            console.error('Error loading earnings data:', result.message);
            showNotification('Error: ' + result.message, 'error');
        }
        
    } catch (error) {
        console.error('Error updating earnings chart:', error);
        console.error('Full error details:', error);
        showNotification('Network error loading earnings data', 'error');
    }
}

// Update earnings statistics cards
function updateEarningsStats(stats) {
    const totalEarnings = document.getElementById('totalEarnings');
    const averagePerHour = document.getElementById('averagePerHour');
    const peakHour = document.getElementById('peakHour');
    
    if (totalEarnings) totalEarnings.textContent = '₱' + stats.total.toLocaleString();
    if (averagePerHour) averagePerHour.textContent = '₱' + stats.average.toLocaleString();
    if (peakHour) peakHour.textContent = formatTimeTo12Hour(stats.peakHour);
}

// Export earnings data with preview modal
function exportEarningsData() {
    const period = document.getElementById('earningsPeriod')?.value || 'today';
    
    console.log('Export button clicked, period:', period);
    
    // Check if modal exists first
    const modal = document.getElementById('exportPreviewModal');
    if (!modal) {
        console.error('Export preview modal not found in DOM');
        alert('Preview modal not found. Please refresh the page and try again.');
        return;
    }
    
    // Show the preview modal
    showExportPreviewModal();
    
    // Generate preview data
    generateExportPreview();
}

// Show export preview modal
function showExportPreviewModal() {
    console.log('Attempting to show export preview modal...');
    const modal = document.getElementById('exportPreviewModal');
    console.log('Modal element found:', modal);
    
    if (modal) {
        console.log('Modal found, setting display and show class');
        modal.style.display = 'flex';
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        console.log('Export preview modal should now be visible');
        console.log('Modal classes:', modal.className);
        console.log('Modal style display:', modal.style.display);
    } else {
        console.error('Export preview modal element not found');
        console.log('Available elements with "modal" in ID:', document.querySelectorAll('[id*="modal"]'));
    }
}

// Close export preview modal
function closeExportPreviewModal() {
    const modal = document.getElementById('exportPreviewModal');
    if (modal) {
        modal.classList.remove('show');
        // Use setTimeout to allow the transition to complete
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
        document.body.style.overflow = 'auto';
        
        // Remove any fallback links
        const fallbackLink = modal.querySelector('.fallback-link');
        if (fallbackLink) {
            fallbackLink.remove();
        }
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('exportPreviewModal');
    if (modal && event.target === modal) {
        closeExportPreviewModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('exportPreviewModal');
        if (modal && modal.style.display === 'block') {
            closeExportPreviewModal();
        }
    }
});

// Generate export preview
async function generateExportPreview() {
    const period = document.getElementById('earningsPeriod')?.value || 'today';
    
    // Show loading state
    document.getElementById('exportPreviewLoading').style.display = 'flex';
    document.getElementById('exportPreviewContent').style.display = 'none';
    document.getElementById('exportPreviewError').style.display = 'none';
    document.getElementById('downloadPdfBtn').disabled = true;
    
    try {
        console.log(`Fetching export preview for period: ${period}`);
        // Fetch preview data from backend
        const response = await fetch(`../backend/api/admin/export_earnings_pdf.php?period=${period}&action=preview`);
        
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Error response:', errorText);
            throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            latestExportData = result.data;
            // Populate preview with data
            populateExportPreview(result.data);
            
            // Show content and hide loading
            document.getElementById('exportPreviewLoading').style.display = 'none';
            document.getElementById('exportPreviewContent').style.display = 'block';
            document.getElementById('downloadPdfBtn').disabled = false;
        } else {
            throw new Error(result.message || 'Failed to generate preview');
        }
        
    } catch (error) {
        console.error('Error generating export preview:', error);
        latestExportData = null;
        
        // Show error state
        document.getElementById('exportPreviewLoading').style.display = 'none';
        document.getElementById('exportPreviewError').style.display = 'flex';
    }
}

// Populate export preview with data
function populateExportPreview(data) {
    // Update header
    document.getElementById('previewDate').textContent = formatDateTime(data.generatedAt);
    
    // Update statistics
    document.getElementById('previewTotalEarnings').textContent = '₱' + data.stats.total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('previewAverageEarnings').textContent = '₱' + data.stats.average.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    // Convert peak hour to 12-hour format if it's in 24-hour format
    let peakHour = data.stats.peakHour;
    if (peakHour.includes(':') && !peakHour.includes('AM') && !peakHour.includes('PM')) {
        // Convert 24-hour to 12-hour format
        const [hours, minutes] = peakHour.split(':');
        const hour24 = parseInt(hours);
        const hour12 = hour24 === 0 ? 12 : (hour24 > 12 ? hour24 - 12 : hour24);
        const ampm = hour24 >= 12 ? 'PM' : 'AM';
        peakHour = `${hour12}:${minutes} ${ampm}`;
    }
    document.getElementById('previewPeakHour').textContent = peakHour;
    
    // Update labels based on period
    const period = data.period;
    let averageLabel = 'Average Per Hour';
    let peakLabel = 'Peak Hour';
    let tableHeader = 'Time';
    
    switch (period) {
        case 'week':
            averageLabel = 'Average Per Day';
            peakLabel = 'Peak Day';
            tableHeader = 'Day';
            break;
        case 'month':
            averageLabel = 'Average Per Day';
            peakLabel = 'Peak Day';
            tableHeader = 'Day';
            break;
        case 'year':
            averageLabel = 'Average Per Month';
            peakLabel = 'Peak Month';
            tableHeader = 'Month';
            break;
    }
    
    document.getElementById('previewAverageLabel').textContent = averageLabel;
    document.getElementById('previewPeakLabel').textContent = peakLabel;
    document.getElementById('previewTableHeader').textContent = tableHeader;
    
    // Populate data table
    const tableBody = document.getElementById('previewTableBody');
    tableBody.innerHTML = '';
    
    for (let i = 0; i < data.labels.length; i++) {
        const row = document.createElement('tr');
        let timeLabel = data.labels[i];
        
        // Convert time to 12-hour format if it's in 24-hour format
        if (timeLabel.includes(':') && !timeLabel.includes('AM') && !timeLabel.includes('PM')) {
            const [hours, minutes] = timeLabel.split(':');
            const hour24 = parseInt(hours);
            const hour12 = hour24 === 0 ? 12 : (hour24 > 12 ? hour24 - 12 : hour24);
            const ampm = hour24 >= 12 ? 'PM' : 'AM';
            timeLabel = `${hour12}:${minutes} ${ampm}`;
        }
        
        row.innerHTML = `
            <td>${timeLabel}</td>
            <td>₱${data.data[i].toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
        `;
        tableBody.appendChild(row);
    }
    
    // Update additional statistics
    document.getElementById('previewTotalSessions').textContent = data.additionalStats.total_sessions || 0;
    document.getElementById('previewUniqueDrivers').textContent = data.additionalStats.unique_drivers || 0;

    const averagePaymentValue = Number(data.additionalStats.avg_payment || 0);
    document.getElementById('previewAvgPayment').textContent = '₱' + averagePaymentValue.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Download PDF
async function downloadPDF() {
    const period = document.getElementById('earningsPeriod')?.value || 'today';
    
    try {
        // Show loading state on button
        const downloadBtn = document.getElementById('downloadPdfBtn');
        downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating PDF...';
        downloadBtn.disabled = true;
        
        // Open PDF report in new window for printing
        const params = new URLSearchParams({ period });

        try {
            const clientGeneratedAt = new Date().toISOString();
            params.set('generatedAt', clientGeneratedAt);
        } catch (error) {
            console.warn('Unable to capture client timestamp:', error);
        }

        try {
            const clientTimeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            if (clientTimeZone) {
                params.set('timeZone', clientTimeZone);
            }
        } catch (error) {
            console.warn('Unable to detect client timezone:', error);
        }

        if (latestExportData?.periodLabel) {
            params.set('periodLabel', latestExportData.periodLabel);
        }

        const pdfUrl = `../backend/api/admin/generate_pdf_report.php?${params.toString()}`;
        const pdfWindow = window.open(pdfUrl, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
        
        if (pdfWindow) {
            // Show success notification with instructions
            showNotification('PDF report opened in new window. Use Ctrl+P (Cmd+P on Mac) to print to PDF.', 'success');
            
            // Close modal after a short delay
            setTimeout(() => {
                closeExportPreviewModal();
            }, 1000);
        } else {
            // Fallback: Show instructions for manual export
            showNotification('Popup blocked. Please right-click the Export button and select "Open in new tab" to access the PDF report.', 'warning');
            
            // Provide alternative download link
            const fallbackLink = document.createElement('a');
            fallbackLink.href = pdfUrl;
            fallbackLink.target = '_blank';
            fallbackLink.textContent = 'Open PDF Report';
            fallbackLink.style.display = 'block';
            fallbackLink.style.marginTop = '10px';
            fallbackLink.style.color = '#3498db';
            fallbackLink.style.textDecoration = 'none';
            
            // Add fallback link to modal
            const modalFooter = document.querySelector('.modal-footer');
            if (modalFooter && !modalFooter.querySelector('.fallback-link')) {
                fallbackLink.className = 'fallback-link';
                modalFooter.appendChild(fallbackLink);
            }
        }
        
    } catch (error) {
        console.error('Error opening PDF report:', error);
        showNotification('Error opening PDF report. Please try again.', 'error');
    } finally {
        // Restore button state
        const downloadBtn = document.getElementById('downloadPdfBtn');
        downloadBtn.innerHTML = '<i class="fas fa-download"></i> Download PDF';
        downloadBtn.disabled = false;
    }
}

// Format date and time for display
function formatDateTime(dateString, timezone) {
    const options = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    };

    if (timezone) {
        options.timeZone = timezone;
    }

    const date = new Date(dateString);
    return date.toLocaleString(undefined, options);
}

// Extend showSection to initialize earnings chart when reports section is shown
document.addEventListener('DOMContentLoaded', function() {
    const originalShowSection = window.showSection;
    if (typeof originalShowSection === 'function') {
        window.showSection = function(section) {
            originalShowSection(section);
            
            // Initialize earnings chart when reports section is shown
            if (section === 'reports') {
                setTimeout(() => {
                    initEarningsChart();
                }, 100);
            }
        };
    }
});
