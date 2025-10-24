// Driver Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard
    initDashboard();
    initRealTimeUpdates();
    initUserData();
});

// Initialize dashboard
function initDashboard() {
    updateCurrentTime();
    setInterval(updateCurrentTime, 1000);
    loadParkingStatus();
    loadRecentActivity();
    loadVehicleInfo();
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

// Load parking status
async function loadParkingStatus() {
    try {
        const response = await fetch('../backend/api/driver_status.php', { credentials: 'include' });
        const data = await response.json();
        
        if (data.success) {
            updateParkingStatus(data.parking_status);
        }
    } catch (error) {
        console.error('Error loading parking status:', error);
    }
}

// Update parking status display
function updateParkingStatus(status) {
    window.__currentParkingStatus = status;
    const statusDot = document.getElementById('statusDot');
    const statusText = document.getElementById('statusText');
    const parkingIcon = document.getElementById('parkingIcon');
    const parkingTitle = document.getElementById('parkingTitle');
    const parkingDescription = document.getElementById('parkingDescription');
    const parkingActions = document.getElementById('parkingActions');

    if (status.is_parked) {
        if (statusDot) statusDot.className = 'status-dot parked';
        if (statusText) statusText.textContent = 'Currently Parked';
        if (parkingIcon) {
            parkingIcon.className = 'fas fa-parking';
            if (parkingIcon.parentElement) parkingIcon.parentElement.className = 'parking-icon parked';
        }
        if (parkingTitle) parkingTitle.textContent = `Parked at Slot ${status.slot_number}`;
        if (parkingDescription) parkingDescription.textContent = `Started at ${status.start_time} • Duration: ${status.duration}`;
        if (parkingActions) {
            parkingActions.innerHTML = `
                <button class="btn btn-primary" onclick="endSession()">
                    <i class="fas fa-sign-out-alt"></i>
                    End Session
                </button>
            `;
        }
    } else {
        if (statusDot) statusDot.className = 'status-dot';
        if (statusText) statusText.textContent = 'Not Parked';
        if (parkingIcon) {
            parkingIcon.className = 'fas fa-parking';
            if (parkingIcon.parentElement) parkingIcon.parentElement.className = 'parking-icon';
        }
        if (parkingTitle) parkingTitle.textContent = 'No Active Parking';
        if (parkingDescription) parkingDescription.textContent = 'You are not currently parked';
        if (parkingActions) {
            parkingActions.innerHTML = `
                <button class="btn btn-primary" onclick="findParking()">
                    <i class="fas fa-search"></i>
                    Find Parking
                </button>
            `;
        }
    }
}

// Load recent activity
async function loadRecentActivity() {
    try {
        const response = await fetch('../backend/api/recent_activity.php', { credentials: 'include' });
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
    if (!activityList) {
        return;
    }

    if (activities.length === 0) {
        activityList.innerHTML = `
            <div class="activity-item">
                <div class="activity-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="activity-details">
                    <h4 class="activity-title">No recent activity</h4>
                    <p class="activity-time">Start parking to see your activity here</p>
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
            <div class="activity-status ${activity.status}">
                <i class="fas fa-${getStatusIcon(activity.status)}"></i>
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
        'register': 'user-plus'
    };
    return icons[type] || 'info-circle';
}

// Get status icon based on status
function getStatusIcon(status) {
    const icons = {
        'completed': 'check',
        'pending': 'clock',
        'failed': 'times'
    };
    return icons[status] || 'info';
}

// Load vehicle information
async function loadVehicleInfo() {
    try {
        const response = await fetch('../backend/api/vehicle_info.php', { credentials: 'include' });
        const data = await response.json();
        
        if (data.success) {
            updateVehicleInfo(data.vehicle);
        }
    } catch (error) {
        console.error('Error loading vehicle info:', error);
    }
}

// Update vehicle information display
function updateVehicleInfo(vehicle) {
    const vehiclePlateEl = document.getElementById('vehiclePlate');
    const vehicleTypeEl = document.getElementById('vehicleType');
    const vehicleColorEl = document.getElementById('vehicleColor');
    const vehicleIcon = document.getElementById('vehicleIcon');

    if (!vehiclePlateEl && !vehicleTypeEl && !vehicleColorEl && !vehicleIcon) {
        return;
    }

    if (vehiclePlateEl) {
        vehiclePlateEl.textContent = vehicle.plate_number || 'Not specified';
    }

    if (vehicleTypeEl) {
        const vehicleType = vehicle.vehicle_type ? vehicle.vehicle_type.charAt(0).toUpperCase() + vehicle.vehicle_type.slice(1) : 'Not specified';
        vehicleTypeEl.textContent = vehicleType;
    }

    if (vehicleColorEl) {
        vehicleColorEl.textContent = vehicle.color || 'Not specified';
    }
    
    // Update vehicle icon based on type
    const iconMap = {
        'car': 'fas fa-car',
        'motorcycle': 'fas fa-motorcycle',
        'van': 'fas fa-truck',
        'truck': 'fas fa-truck',
        'suv': 'fas fa-car'
    };
    if (vehicleIcon) {
        const iconKey = vehicle.vehicle_type && iconMap[vehicle.vehicle_type] ? vehicle.vehicle_type : 'car';
        vehicleIcon.className = iconMap[iconKey] || 'fas fa-car';
    }
}

// Load user data
async function initUserData() {
    try {
        const response = await fetch('../backend/api/user_info.php', { credentials: 'include' });
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('userName').textContent = data.user.full_name;
            document.getElementById('userFirstName').textContent = data.user.first_name;
        }
    } catch (error) {
        console.error('Error loading user data:', error);
    }
}

// Real-time updates
function initRealTimeUpdates() {
    // Update parking status every 30 seconds
    setInterval(loadParkingStatus, 30000);
    
    // Update recent activity every 60 seconds
    setInterval(loadRecentActivity, 60000);
}

// Navigation functions
function findParking() {
    window.location.href = 'find_parking.php';
}

function viewMyParking() {
    window.location.href = 'my_parking.php';
}

function viewHistory() {
    window.location.href = 'history.php';
}

function getHelp() {
    // Show help modal or redirect to help page
    showNotification('Help feature coming soon!', 'info');
}

function editVehicle() {
    // Show edit vehicle modal
    showNotification('Edit vehicle feature coming soon!', 'info');
}

// Request exit from parking
async function requestExit() {
    try {
        showLoading(true);
        
        const response = await fetch('../backend/api/end_parking.php', {
            method: 'POST',
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Parking session ended and slot freed.', 'success');
            loadParkingStatus(); // Refresh status
        } else {
            showNotification(data.message || 'Failed to submit exit request', 'error');
        }
    } catch (error) {
        console.error('Error requesting exit:', error);
        showNotification('Network error. Please try again.', 'error');
    } finally {
        showLoading(false);
    }
}

// Legacy handler used by inline buttons on driver dashboard
function endSession() {
    openEndSessionModal();
}

// Note: Do not define a function named `logout` here; use the global modal-based one from js/global.js

// Dashboard end session modal helpers
let dashboardEndModalInitialized = false;

function ensureEndSessionModal() {
    if (dashboardEndModalInitialized) return;

    const modalWrapper = document.createElement('div');
    modalWrapper.id = 'dashboardEndModal';
    modalWrapper.className = 'ps-modal';
    modalWrapper.innerHTML = `
        <div class="ps-modal-backdrop" id="dashboardEndModalBackdrop"></div>
        <div class="ps-modal-content" role="dialog" aria-modal="true" aria-labelledby="dashboardEndModalTitle">
            <div class="ps-modal-header">
                <h3 id="dashboardEndModalTitle">End Parking Session</h3>
            </div>
            <div class="ps-modal-body">
                <p>Are you sure you want to end your parking session now?</p>
                <div class="modal-info">
                    <div class="modal-row"><span>Slot</span><span id="dashboardModalSlot">--</span></div>
                    <div class="modal-row"><span>Started at</span><span id="dashboardModalStart">--:--</span></div>
                    <div class="modal-row"><span>Duration</span><span id="dashboardModalDuration">--</span></div>
                </div>
            </div>
            <div class="ps-modal-actions">
                <button type="button" class="btn" id="dashboardEndModalCancel">Cancel</button>
                <button type="button" class="btn btn-danger" id="dashboardEndModalConfirm"><i class="fas fa-sign-out-alt"></i> End Session</button>
            </div>
        </div>
    `;

    document.body.appendChild(modalWrapper);
    dashboardEndModalInitialized = true;
}

function openEndSessionModal() {
    ensureEndSessionModal();

    const status = window.__currentParkingStatus;
    if (!status || !status.is_parked) {
        showNotification('No active parking session found.', 'info');
        return;
    }

    const modal = document.getElementById('dashboardEndModal');
    const backdrop = document.getElementById('dashboardEndModalBackdrop');
    const cancelBtn = document.getElementById('dashboardEndModalCancel');
    const confirmBtn = document.getElementById('dashboardEndModalConfirm');

    const slotEl = document.getElementById('dashboardModalSlot');
    const startEl = document.getElementById('dashboardModalStart');
    const durationEl = document.getElementById('dashboardModalDuration');

    if (slotEl) slotEl.textContent = status.slot_number || '--';
    if (startEl) startEl.textContent = status.start_time || '--:--';
    if (durationEl) durationEl.textContent = status.duration || '00 hours 00 minutes';

    const closeModal = () => {
        if (!modal) return;
        modal.classList.remove('show');
        setTimeout(() => { modal.style.display = 'none'; }, 200);
        cleanupListeners();
    };

    const cleanupListeners = () => {
        if (cancelBtn) cancelBtn.removeEventListener('click', onCancel);
        if (backdrop) backdrop.removeEventListener('click', onCancel);
        if (confirmBtn) confirmBtn.removeEventListener('click', onConfirm);
        document.removeEventListener('keydown', onKeyDown);
    };

    const onCancel = () => closeModal();
    const onConfirm = async () => {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ending...';
        await requestExit();
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = '<i class="fas fa-sign-out-alt"></i> End Session';
        closeModal();
    };

    const onKeyDown = (event) => {
        if (event.key === 'Escape') closeModal();
        if (event.key === 'Enter') onConfirm();
    };

    if (cancelBtn) cancelBtn.addEventListener('click', onCancel);
    if (backdrop) backdrop.addEventListener('click', onCancel);
    if (confirmBtn) confirmBtn.addEventListener('click', onConfirm);
    document.addEventListener('keydown', onKeyDown);

    modal.style.display = 'block';
    requestAnimationFrame(() => modal.classList.add('show'));
}

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
        background-color: var(--white);
        border-radius: 0.5rem;
        box-shadow: var(--shadow-lg);
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
        border-left: 4px solid var(--danger-red);
    }
    
    .notification-success {
        border-left: 4px solid var(--success-green);
    }
    
    .notification-info {
        border-left: 4px solid var(--primary-blue);
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .notification-content i {
        color: var(--danger-red);
    }
    
    .notification-success .notification-content i {
        color: var(--success-green);
    }
    
    .notification-info .notification-content i {
        color: var(--primary-blue);
    }
    
    @media (max-width: 768px) {
        .notification {
            top: 1rem;
            right: 1rem;
            left: 1rem;
            max-width: none;
        }
    }
`;
document.head.appendChild(style);
