<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParkSmart - Parking Management System</title>
    <link rel="stylesheet" href="css/global.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="nav">
            <div class="nav-brand">
                <div class="logo">
                    <div class="logo-icon">
                        <div class="bar bar-1"></div>
                        <div class="bar bar-2"></div>
                        <div class="bar bar-3"></div>
                    </div>
                    <span class="logo-text">ParkSmart</span>
                </div>
            </div>
            <button class="mobile-toggle" aria-label="Menu"><i class="fas fa-bars"></i></button>
            <div class="nav-menu">
                <a href="#features" class="nav-link">Features</a>
                <a href="#about" class="nav-link">About</a>
                <a href="#contact" class="nav-link">Contact</a>
                <a href="login.php" class="btn btn-primary">Login</a>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="hero-title">Smart Parking Made Simple</h1>
                <p class="hero-description">No reservations needed! Find available slots instantly, park directly, and pay with cash. Real-time updates keep you informed every step of the way.</p>
                <div class="hero-buttons">
                    <a href="register.php" class="btn btn-primary btn-large">Start Parking Now</a>
                    <a href="login.php" class="btn btn-outline btn-large">Driver Login</a>
                </div>
            </div>
            <div class="hero-visual">
                <div class="parking-demo">
                    <div class="demo-header">
                        <h3>Live Parking Status</h3>
                        <div class="status-indicator">
                            <span class="status-dot active"></span>
                            <span>Real-time Updates</span>
                        </div>
                    </div>
                    <div class="parking-grid">
                        <div class="grid-container">
                            <div class="slot available" title="Available - Click to Park">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="slot occupied" title="Occupied">
                                <i class="fas fa-car"></i>
                            </div>
                            <div class="slot available" title="Available - Click to Park">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="slot maintenance" title="Under Maintenance">
                                <i class="fas fa-tools"></i>
                            </div>
                            <div class="slot available" title="Available - Click to Park">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="slot occupied" title="Occupied">
                                <i class="fas fa-car"></i>
                            </div>
                            <div class="slot available" title="Available - Click to Park">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="slot occupied" title="Occupied">
                                <i class="fas fa-car"></i>
                            </div>
                            <div class="slot available" title="Available - Click to Park">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="slot occupied" title="Occupied">
                                <i class="fas fa-car"></i>
                            </div>
                            <div class="slot available" title="Available - Click to Park">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="slot occupied" title="Occupied">
                                <i class="fas fa-car"></i>
                            </div>
                        </div>
                    </div>
                    <div class="demo-footer">
                        <div class="legend">
                            <div class="legend-item">
                                <div class="legend-color available"></div>
                                <span>Available</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color occupied"></div>
                                <span>Occupied</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color maintenance"></div>
                                <span>Maintenance</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <h2 class="section-title">How It Works</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3 class="feature-title">Find Available Slots</h3>
                    <p class="feature-description">Browse the visual parking grid to see real-time availability. Green slots are ready for immediate parking.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-parking"></i>
                    </div>
                    <h3 class="feature-title">Park Instantly</h3>
                    <p class="feature-description">No reservations needed! Click on an available slot and start parking immediately. Your session begins right away.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="feature-title">Track Your Time</h3>
                    <p class="feature-description">Monitor your parking duration and see real-time fee calculations. Know exactly what you'll pay before exiting.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h3 class="feature-title">Cash Payment</h3>
                    <p class="feature-description">Request exit when ready, pay with cash to the attendant, and get instant approval to leave. Simple and secure.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <h2 class="section-title">System Overview</h2>
            <p class="about-description">ParkSmart is designed for two main user types, each with streamlined workflows for maximum efficiency:</p>
            <div class="user-types">
                <div class="user-card">
                    <div class="user-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3 class="user-title">For Drivers</h3>
                    <ul class="user-features">
                        <li><i class="fas fa-check"></i> Visual slot selection with real-time updates</li>
                        <li><i class="fas fa-check"></i> Direct parking - no reservations needed</li>
                        <li><i class="fas fa-check"></i> Real-time fee tracking and calculation</li>
                        <li><i class="fas fa-check"></i> Simple exit request and cash payment</li>
                        <li><i class="fas fa-check"></i> Parking history and payment status</li>
                    </ul>
                </div>
                <div class="user-card">
                    <div class="user-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h3 class="user-title">For Administrators</h3>
                    <ul class="user-features">
                        <li><i class="fas fa-check"></i> Real-time occupancy monitoring</li>
                        <li><i class="fas fa-check"></i> Quick payment approval/rejection</li>
                        <li><i class="fas fa-check"></i> Driver account management</li>
                        <li><i class="fas fa-check"></i> Slot status control and maintenance</li>
                        <li><i class="fas fa-check"></i> Revenue reports and analytics</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <h2 class="section-title">Get in Touch</h2>
            <div class="contact-info">
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <span class="contact-text">support@parksmart.com</span>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <span class="contact-text">(+63) 9123456789</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-brand">
                <div class="logo">
                    <div class="logo-icon">
                        <div class="bar bar-1"></div>
                        <div class="bar bar-2"></div>
                        <div class="bar bar-3"></div>
                    </div>
                    <span class="logo-text">ParkSmart</span>
                </div>
            </div>
            <div class="footer-copyright">
                <span>&copy; 2025 ParkSmart. All rights reserved.</span>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top Button -->
    <button id="scrollToTop" class="scroll-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script src="js/global.js"></script>
</body>
</html>
