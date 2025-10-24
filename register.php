<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register as Driver - ParkSmart</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/register.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="register-container">
        <!-- Back to Home Link -->
        <div class="back-link">
            <a href="index.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Home</span>
            </a>
        </div>

        <!-- Registration Form Card -->
        <div class="register-card">
            <!-- Logo -->
            <div class="register-logo">
                <div class="logo">
                    <div class="logo-icon">
                        <div class="bar bar-1"></div>
                        <div class="bar bar-2"></div>
                        <div class="bar bar-3"></div>
                    </div>
                    <span class="logo-text">ParkSmart</span>
                </div>
            </div>

            <!-- Form Header -->
            <div class="form-header">
                <h1 class="form-title">Create Driver Account</h1>
                <p class="form-description">Register as a driver to start using our parking system</p>
            </div>

            <!-- Registration Form -->
            <form id="registerForm" class="register-form" action="backend/controllers/driver_controller.php" method="POST">
                <!-- Form Fields -->
                <div class="form-fields">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" id="first_name" name="first_name" class="form-input" 
                                   placeholder="Enter your first name" required>
                            <div class="error-message" id="first_name_error"></div>
                        </div>
                        <div class="form-group">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" class="form-input" 
                                   placeholder="Enter your last name" required>
                            <div class="error-message" id="last_name_error"></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" id="email" name="email" class="form-input" 
                                   placeholder="Enter your email address" required>
                            <div class="error-message" id="email_error"></div>
                        </div>
                        <div class="form-group">
                            <label for="plate_number" class="form-label">Vehicle Plate Number *</label>
                            <input type="text" id="plate_number" name="plate_number" class="form-input" 
                                   placeholder="Enter your vehicle plate number" required>
                            <div class="error-message" id="plate_number_error"></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="vehicle_type" class="form-label">Vehicle Type *</label>
                            <select id="vehicle_type" name="vehicle_type" class="form-select" required>
                                <option value="">Select vehicle type</option>
                                <option value="car">Car</option>
                                <option value="motorcycle">Motorcycle</option>
                                <option value="van">Van</option>
                                <option value="truck">Truck</option>
                                <option value="suv">SUV</option>
                            </select>
                            <div class="error-message" id="vehicle_type_error"></div>
                        </div>
                        <div class="form-group">
                            <label for="vehicle_color" class="form-label">Vehicle Color</label>
                            <input type="text" id="vehicle_color" name="vehicle_color" class="form-input" 
                                   placeholder="Enter vehicle color (optional)">
                            <div class="error-message" id="vehicle_color_error"></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password" class="form-label">Password *</label>
                            <div class="password-input">
                                <input type="password" id="password" name="password" class="form-input" 
                                       placeholder="Enter your password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="error-message" id="password_error"></div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-large btn-full">
                        <i class="fas fa-user-plus"></i>
                        Create Driver Account
                    </button>
                </div>

                <!-- Login Link -->
                <div class="form-footer">
                    <p>Already have an account? <a href="login.php" class="login-link">Sign In</a></p>
                </div>
            </form>
        </div>
    </div>

    <script src="js/global.js"></script>
    <script src="js/register.js"></script>
</body>
</html>
