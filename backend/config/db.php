<?php
// Database Configuration for Parking Management System

// Database connection settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'parking_system_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Database connection options
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
]);

// Connection timeout (in seconds)
define('DB_TIMEOUT', 30);

// Maximum connection retries
define('DB_MAX_RETRIES', 3);
?>
