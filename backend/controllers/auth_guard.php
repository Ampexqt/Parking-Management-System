<?php
function ensureSessionStarted() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function requireAdmin() {
    ensureSessionStarted();
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit;
    }
    if ($_SESSION['role'] !== 'admin') {
        if ($_SESSION['role'] === 'driver') {
            header('Location: ../driver/dashboard.php');
            exit;
        }
        header('Location: ../login.php');
        exit;
    }
}

function requireDriver() {
    ensureSessionStarted();
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit;
    }
    if ($_SESSION['role'] !== 'driver') {
        if ($_SESSION['role'] === 'admin') {
            header('Location: ../admin/dashboard.php');
            exit;
        }
        header('Location: ../login.php');
        exit;
    }
}
