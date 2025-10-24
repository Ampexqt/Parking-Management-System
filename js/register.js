// Registration Form JavaScript

document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    const submitButton = registerForm.querySelector('button[type="submit"]');
    
    // Initialize form validation
    initFormValidation();
    initPasswordToggle();
    initFormSubmission();
});

// Form Validation
function initFormValidation() {
    const form = document.getElementById('registerForm');
    const inputs = form.querySelectorAll('input, select');
    
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
    
    // Password validation (no confirmation needed)
}

// Validate individual field
function validateField(field) {
    const fieldName = field.name;
    const value = field.value.trim();
    
    clearFieldError(field);
    
    switch (fieldName) {
        case 'first_name':
            if (!value) {
                showFieldError(field, 'First name is required');
                return false;
            }
            if (value.length < 2) {
                showFieldError(field, 'First name must be at least 2 characters');
                return false;
            }
            break;
            
        case 'last_name':
            if (!value) {
                showFieldError(field, 'Last name is required');
                return false;
            }
            if (value.length < 2) {
                showFieldError(field, 'Last name must be at least 2 characters');
                return false;
            }
            break;
            
        case 'email':
            if (!value) {
                showFieldError(field, 'Email is required');
                return false;
            }
            if (!isValidEmail(value)) {
                showFieldError(field, 'Email must include "@" and end with ".com"');
                return false;
            }
            break;
            
        case 'plate_number':
            if (!value) {
                showFieldError(field, 'Vehicle plate number is required');
                return false;
            }
            if (value.length < 3) {
                showFieldError(field, 'Plate number must be at least 3 characters');
                return false;
            }
            break;
            
        case 'vehicle_type':
            if (!value) {
                showFieldError(field, 'Vehicle type is required');
                return false;
            }
            break;
            
        case 'password':
            if (!value) {
                showFieldError(field, 'Password is required');
                return false;
            }
            if (!isValidPassword(value)) {
                showFieldError(field, 'Password must be 8 alphanumeric characters');
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
    const emailRegex = /^[^\s@]+@[^\s@]+\.com$/i;
    return emailRegex.test(email);
}

function isValidPassword(password) {
    const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8}$/;
    return passwordRegex.test(password);
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
    const form = document.getElementById('registerForm');
    const submitButton = form.querySelector('button[type="submit"]');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate all fields
        const inputs = form.querySelectorAll('input[required], select[required]');
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
    
    try {
        const response = await fetch('backend/controllers/driver_controller.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccessMessage();
        } else {
            showNotification(result.message || 'Registration failed. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Registration error:', error);
        showNotification('Network error. Please check your connection and try again.', 'error');
    } finally {
        setLoadingState(false);
    }
}

// Set loading state
function setLoadingState(loading) {
    const submitButton = document.querySelector('button[type="submit"]');
    
    if (loading) {
        submitButton.disabled = true;
        submitButton.classList.add('btn-loading');
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
    } else {
        submitButton.disabled = false;
        submitButton.classList.remove('btn-loading');
        submitButton.innerHTML = '<i class="fas fa-user-plus"></i> Create Driver Account';
    }
}

// Show success message
function showSuccessMessage() {
    const form = document.getElementById('registerForm');
    form.innerHTML = `
        <div class="form-success">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h2 class="success-title">Account Created Successfully!</h2>
            <p class="success-message">Your driver account has been created. You can now log in to start using the parking system.</p>
            <div class="success-actions">
                <a href="login.php" class="btn btn-primary">Go to Login</a>
                <a href="index.php" class="btn btn-outline">Back to Home</a>
            </div>
        </div>
    `;
}

// Show notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
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
            document.body.removeChild(notification);
        }, 300);
    }, 5000);
}

// Add error styles to CSS
const style = document.createElement('style');
style.textContent = `
    .form-input.error,
    .form-select.error {
        border-color: var(--danger-red);
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
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
    
    .success-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 1.5rem;
    }
`;
document.head.appendChild(style);
