<?php
require_once '../backend/controllers/auth_guard.php';
requireDriver();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Find Parking - ParkSmart</title>
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/driver/dashboard.css">
    <link rel="stylesheet" href="../css/driver/find_parking.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <script>
      // Prevent caching on back/forward
      window.addEventListener('pageshow', (event) => { if (event.persisted) window.location.reload(); });
    </script>
</head>
<body>
  <div class="dashboard-container">
    <header class="dashboard-header">
      <div class="header-content">
        <div class="header-left">
          <div class="logo">
            <div class="logo-icon">
              <div class="bar bar-1"></div>
              <div class="bar bar-2"></div>
              <div class="bar bar-3"></div>
            </div>
            <span class="logo-text">ParkSmart</span>
          </div>
        </div>
        <div class="header-right">
          <div class="user-info">
            <div class="user-avatar"><i class="fas fa-user"></i></div>
            <div class="user-details">
              <span class="user-name" id="userName">John Doe</span>
              <span class="user-role">Driver</span>
            </div>
          </div>
          <button class="logout-btn" onclick="logout()"><i class="fas fa-sign-out-alt"></i></button>
        </div>
      </div>
    </header>

    <main class="dashboard-main">
      <section class="welcome-section">
        <div class="welcome-content">
          <h1 class="welcome-title">Find Parking</h1>
          <p class="welcome-subtitle">Walk-in only — tap an available slot to start</p>
        </div>
        <div class="welcome-time">
          <i class="fas fa-clock"></i>
          <span id="currentTime"></span>
        </div>
      </section>

      <section style="margin-top: 12px;">

        <div class="notice" style="padding:12px 14px;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:8px;margin-bottom:14px;color:#374151;">
          Walk-in only: tap an available slot to start your parking session immediately.
        </div>

        <div class="toolbar" style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
          <button id="refreshBtn" class="btn btn-primary"><i class="fas fa-rotate"></i> Refresh</button>
        </div>

        <div id="slotGrid" class="slot-grid"></div>
      </section>
    </main>

    <nav class="bottom-nav">
      <a href="dashboard.php" class="nav-item">
        <i class="fas fa-home"></i>
        <span>Home</span>
      </a>
      <a href="find_parking.php" class="nav-item active">
        <i class="fas fa-search"></i>
        <span>Find Parking</span>
      </a>
      <a href="my_parking.php" class="nav-item">
        <i class="fas fa-car"></i>
        <span>My Parking</span>
      </a>
      <a href="history.php" class="nav-item">
        <i class="fas fa-history"></i>
        <span>History</span>
      </a>
    </nav>
  </div>

  <script src="../js/global.js"></script>
  <script src="../js/driver/find_parking.js"></script>
</body>
</html>
