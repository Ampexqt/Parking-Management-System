// Login Form JavaScript

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const submitButton = loginForm.querySelector('button[type="submit"]');
    
    // Initialize form validation
    initFormValidation();
    initPasswordToggle();
    initFormSubmission();
});

// Form Validation
function initFormValidation() {
    const form = document.getElementById('loginForm');
    const inputs = form.querySelectorAll('input');
    
    inputs.forEach(input => {
        // Real-time validation on input change
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        // Clear errors on input
        input.addEventListener('input', function() {
            clearFieldError(this);
        });
    });
}

// Validate individual field
function validateField(field) {
    const fieldName = field.name;
    const value = field.value.trim();
    
    clearFieldError(field);
    
    switch (fieldName) {
        case 'username':
            if (!value) {
                showFieldError(field, 'Username or email is required');
                return false;
            }
            // Check if it's an email format
            if (value.includes('@')) {
                if (!isValidEmail(value)) {
                    showFieldError(field, 'Please enter a valid email address');
                    return false;
                }
            } else {
                if (value.length < 3) {
                    showFieldError(field, 'Username must be at least 3 characters');
                    return false;
                }
            }
            break;
            
        case 'password':
            if (!value) {
                showFieldError(field, 'Password is required');
                return false;
            }
            if (value.length < 6) {
                showFieldError(field, 'Password must be at least 6 characters');
                return false;
            }
            break;
    }
    
    return true;
}

// Show field error
function showFieldError(field, message) {
    const errorElement = document.getElementById(field.name + '_error');
    if (errorElement) {
        errorElement.textContent = message;
        field.classList.add('error');
    }
}

// Clear field error
function clearFieldError(field) {
    const errorElement = document.getElementById(field.name + '_error');
    if (errorElement) {
        errorElement.textContent = '';
        field.classList.remove('error');
    }
}

// Email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Password toggle functionality
function initPasswordToggle() {
    // This is handled by the onclick attribute in HTML
}

function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const toggle = field.parentElement.querySelector('.password-toggle i');
    
    if (field.type === 'password') {
        field.type = 'text';
        toggle.classList.remove('fa-eye');
        toggle.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        toggle.classList.remove('fa-eye-slash');
        toggle.classList.add('fa-eye');
    }
}

// Form submission
function initFormSubmission() {
    const form = document.getElementById('loginForm');
    const submitButton = form.querySelector('button[type="submit"]');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate all fields
        const inputs = form.querySelectorAll('input[required]');
        let isValid = true;
        
        inputs.forEach(input => {
            if (!validateField(input)) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            showNotification('Please fix the errors before submitting', 'error');
            return;
        }
        
        // Show loading state
        setLoadingState(true);
        
        // Submit form data
        submitForm(form);
    });
}

// Submit form data
async function submitForm(form) {
    const formData = new FormData(form);
    const started = Date.now();
    
    try {
        const response = await fetch('backend/controllers/auth_controller.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            await ensureMinDelay(started, 1000);
            if (result.data && result.data.role === 'admin') {
                window.location.href = 'admin/dashboard.php';
            } else {
                window.location.href = 'driver/dashboard.php';
            }
        } else {
            await ensureMinDelay(started, 1000);
            setLoadingState(false);
            showNotification(result.message || 'Login failed. Please check your credentials.', 'error');
        }
    } catch (error) {
        console.error('Login error:', error);
        await ensureMinDelay(started, 1000);
        setLoadingState(false);
        showNotification('Network error. Please check your connection and try again.', 'error');
    }
}

function ensureMinDelay(started, minMs) {
    const elapsed = Date.now() - started;
    const wait = Math.max(0, minMs - elapsed);
    return new Promise(resolve => setTimeout(resolve, wait));
}

// Set loading state
function setLoadingState(loading) {
    const submitButton = document.querySelector('button[type="submit"]');
    const ensureOverlay = () => {
        let overlay = document.querySelector('.loader-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'loader-overlay';
            overlay.innerHTML = '<div class="loader" aria-label="Loading" role="status"></div>';
            document.body.appendChild(overlay);
        }
        return overlay;
    };
    
    if (loading) {
        submitButton.disabled = true;
        submitButton.classList.add('btn-loading');
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
        if (typeof window.showLoader === 'function') {
            window.showLoader();
        } else {
            ensureOverlay().classList.add('show');
            document.body.style.cursor = 'progress';
        }
    } else {
        submitButton.disabled = false;
        submitButton.classList.remove('btn-loading');
        submitButton.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In';
        if (typeof window.hideLoader === 'function') {
            window.hideLoader();
        } else {
            ensureOverlay().classList.remove('show');
            document.body.style.cursor = '';
        }
    }
}

// Show notification
function showNotification(message, type = 'info') {
    // Determine icon and display duration based on message content
    let icon = 'info-circle';
    let duration = 5000;
    
    if (type === 'error') {
        icon = 'exclamation-circle';
        // Longer duration for suspended/inactive account messages
        if (message.includes('suspended') || message.includes('inactive')) {
            icon = 'ban';
            duration = 7000;
        }
    } else if (type === 'success') {
        icon = 'check-circle';
    } else if (type === 'warning') {
        icon = 'exclamation-triangle';
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${icon}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // Remove notification after specified duration
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, duration);
}

// Add error styles to CSS
const style = document.createElement('style');
style.textContent = `
    .form-input.error {
        border-color: var(--danger-red);
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.1);
    }
    
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
`;
document.head.appendChild(style);
