<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ParkSmart</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/login.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <!-- Back to Home Link -->
        <div class="back-link">
            <a href="index.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Home</span>
            </a>
        </div>

        <!-- Login Form Card -->
        <div class="login-card">
            <!-- Logo -->
            <div class="login-logo">
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
                <h1 class="form-title">Welcome Back</h1>
                <p class="form-description">Sign in to your account to continue</p>
            </div>

            <!-- Login Form -->
            <form id="loginForm" class="login-form" action="backend/controllers/auth_controller.php" method="POST">
                <!-- Username/Email Field -->
                <div class="form-group">
                    <label for="username" class="form-label">Username/Email</label>
                    <input type="text" id="username" name="username" class="form-input" 
                           placeholder="Enter your username or email" required>
                    <div class="error-message" id="username_error"></div>
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" class="form-input" 
                               placeholder="Enter your password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="error-message" id="password_error"></div>
                </div>

                <!-- Submit Button -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-large btn-full">
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In
                    </button>
                </div>

                <!-- Register Link -->
                <div class="form-footer">
                    <p>Don't have an account? <a href="register.php" class="register-link">Register as Driver</a></p>
                </div>
            </form>
        </div>
    </div>

    <script src="js/global.js"></script>
    <script src="js/login.js"></script>
</body>
</html>
