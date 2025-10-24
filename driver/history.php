<?php
require_once '../backend/controllers/auth_guard.php';
requireDriver();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Parking History - ParkSmart</title>
  <link rel="stylesheet" href="../css/global.css">
  <link rel="stylesheet" href="../css/driver/dashboard.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
              <span class="user-name" id="userName">Driver</span>
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
          <h1 class="welcome-title">Parking History</h1>
          <p class="welcome-subtitle">View past parking sessions and payments</p>
        </div>
        <div class="welcome-time"><i class="fas fa-clock"></i><span id="currentTime"></span></div>
      </section>

      <section>
        <div id="historyList" class="history-list"></div>
        <div id="historyEmpty" class="info-card muted" style="display:none;">
          <div class="info-title"><span class="mini-dot mini-blue"></span> No parking history yet.</div>
          <div class="info-body"><div class="info-row"><span>Start your first parking session!</span></div></div>
        </div>
      </section>
    </main>

    <nav class="bottom-nav">
      <a href="dashboard.php" class="nav-item"><i class="fas fa-home"></i><span>Home</span></a>
      <a href="find_parking.php" class="nav-item"><i class="fas fa-search"></i><span>Find Parking</span></a>
      <a href="my_parking.php" class="nav-item"><i class="fas fa-car"></i><span>My Parking</span></a>
      <a href="history.php" class="nav-item active"><i class="fas fa-history"></i><span>History</span></a>
    </nav>
  </div>

  <script src="../js/global.js"></script>
  <script>
    const elTime = document.getElementById('currentTime');
    function tickNow(){ const now = new Date(); elTime.textContent = now.toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit',hour12:true}) + ' • ' + now.toLocaleDateString('en-US',{weekday:'long',year:'numeric',month:'long',day:'numeric'});} tickNow(); setInterval(tickNow,1000);

    // Simple 12-hour datetime formatter for values like "2025-10-23 18:10:59"
    function formatDateTime12(val){
      if(!val) return '-';
      try{
        const s = String(val).trim().replace(' ', 'T');
        const d = new Date(s);
        if(isNaN(d)) return val;
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth()+1).padStart(2,'0');
        const dd = String(d.getDate()).padStart(2,'0');
        let h = d.getHours();
        const ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12; if(h === 0) h = 12;
        const min = String(d.getMinutes()).padStart(2,'0');
        const sec = String(d.getSeconds()).padStart(2,'0');
        return `${yyyy}-${mm}-${dd} ${h}:${min}:${sec} ${ampm}`;
      }catch(_){ return val; }
    }

    async function loadUser(){ try{ const r=await fetch('../backend/api/user_info.php',{credentials:'include'}); const j=await r.json(); if(j.success&&j.user){ const n=document.getElementById('userName'); if(n) n.textContent=j.user.full_name||j.user.username; } }catch(e){} }
    loadUser();

    async function loadHistory(){
      const list = document.getElementById('historyList');
      const empty = document.getElementById('historyEmpty');
      list.innerHTML = '';
      try{
        const r = await fetch('../backend/api/driver_history.php',{credentials:'include'});
        const j = await r.json();
        if(!j.success) throw new Error(j.message||'Failed to load history');
        const items = j.history||[];
        if(items.length===0){ empty.style.display=''; return; } else { empty.style.display='none'; }
        list.innerHTML = items.map((x,i)=>{
          const paid = (x.payment_status||'').toLowerCase();
          const badge = paid==='approved'||paid==='completed' ? 'badge-paid' : paid==='pending' ? 'badge-pending' : 'badge-na';
          const method = (x.payment_method||'').toUpperCase();
          return `
            <div class="history-card" data-idx="${i}">
              <div class="history-head">
                <div class="left"><i class="fas fa-parking"></i><div class="meta"><div class="slot">Slot ${x.slot_number||'-'}</div><div class="date">${x.date_label}</div></div></div>
                <div class="right">
                  <div class="amount">₱${x.amount==null? '0': x.amount}</div>
                  <span class="badge ${badge}">${paid==='approved'||paid==='completed'?'Paid':'Pending'} ${method? '• '+method : ''}</span>
                </div>
              </div>
              <div class="history-sub">
                <div class="duration"><i class="far fa-clock"></i> ${x.duration_label}</div>
                <button class="btn btn-primary btn-sm toggle">View Details</button>
              </div>
              <div class="history-body" style="display:none;">
                <div class="row"><span>Start</span><span>${formatDateTime12(x.start_time)}</span></div>
                <div class="row"><span>End</span><span>${formatDateTime12(x.end_time)}</span></div>
                <div class="row"><span>Payment Method</span><span>${x.payment_method||'n/a'}</span></div>
                <div class="row"><span>Reference</span><span>${x.reference_number||'-'}</span></div>
              </div>
            </div>`;
        }).join('');

        // wire toggles
        list.querySelectorAll('.history-card .toggle').forEach((btn)=>{
          btn.addEventListener('click', (e)=>{
            const card = e.target.closest('.history-card');
            const body = card.querySelector('.history-body');
            body.style.display = body.style.display==='none' ? '' : 'none';
            btn.textContent = body.style.display==='none' ? 'View Details' : 'Hide Details';
          });
        });
      }catch(err){
        list.innerHTML = `<div class="info-card muted"><div class="info-title"><span class="mini-dot mini-amber"></span> Unable to load history</div><div class="info-body"><div class="info-row"><span>${err.message||'Error'}</span></div></div></div>`;
      }
    }
    loadHistory();

    // Use global modal-based logout provided by js/global.js
  </script>
</body>
</html>
