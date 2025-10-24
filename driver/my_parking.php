<?php
require_once '../backend/controllers/auth_guard.php';
requireDriver();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Parking - ParkSmart</title>
  <link rel="stylesheet" href="../css/global.css">
  <link rel="stylesheet" href="../css/driver/dashboard.css">
  <link rel="stylesheet" href="../css/driver/find_parking.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
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
          <h1 class="welcome-title">My Parking</h1>
          <p class="welcome-subtitle">View your current parking status and details</p>
        </div>
        <div class="welcome-time">
          <i class="fas fa-clock"></i>
          <span id="currentTime"></span>
        </div>
      </section>
      <section id="activeBannerSection" style="display:none;">
        <div class="active-banner">
          <div class="banner-left">
            <div class="check-icon"><i class="fas fa-check"></i></div>
            <div>
              <div class="banner-title">Parking Active</div>
              <div class="banner-sub" id="bannerSub">Slot —</div>
            </div>
          </div>
          <div class="banner-right">
            <div class="banner-caption">Started at</div>
            <div class="banner-time" id="bannerStart">--:--</div>
          </div>
        </div>
      </section>

      <section id="durationSection" style="display:none;">
        <div class="duration-card">
          <div class="duration-title"><i class="far fa-clock"></i> Parking Duration</div>
          <div class="duration-grid">
            <div class="duration-col"><div class="big" id="durHours">00</div><div class="label">hours</div></div>
            <div class="sep">:</div>
            <div class="duration-col"><div class="big" id="durMinutes">00</div><div class="label">minutes</div></div>
            <div class="sep">:</div>
            <div class="duration-col"><div class="big" id="durSeconds">00</div><div class="label">seconds</div></div>
          </div>
          <div class="duration-actions">
            <button class="btn btn-danger" id="btnExitInDuration"><i class="fas fa-sign-out-alt"></i> End Parking Session</button>
          </div>
        </div>
      </section>

      <section id="statusSection">
        <div class="parking-card" id="statusCard">
          <div class="parking-info">
            <div class="parking-icon" id="statusIcon"><i class="fas fa-parking"></i></div>
            <div class="parking-details">
              <h3 id="statusTitle">Loading...</h3>
              <p id="statusDesc">Please wait while we get your parking status.</p>
              <div id="statusExtras" style="margin-top:8px; display:none;">
                <div><strong>Duration: </strong><span id="liveDuration">--:--:--</span></div>
                <div><strong>Hourly Rate: </strong>₱<span id="rateValue">50</span>/hr</div>
                <div><strong>Est. Amount Due: </strong>₱<span id="amountDue">0</span></div>
              </div>
            </div>
          </div>
          <div class="parking-actions" id="statusActions">
            <!-- buttons injected by JS -->
          </div>
        </div>
      </section>

      <section id="paymentDetailsSection" style="display:none;">
        <div class="info-card">
          <div class="info-title"><span class="mini-dot mini-blue"></span> Payment Details</div>
          <div class="info-body">
            <div class="info-row"><span>Hourly Rate</span><span>₱<span id="rateValueCard">50</span>/hr</span></div>
            <div class="info-row"><span>Amount Due</span><span>₱<span id="amountDueCard">0</span></span></div>
          </div>
        </div>
      </section>


      <section id="reminderSection" style="display:none;">
        <div class="info-card muted">
          <div class="info-title"><span class="mini-dot mini-amber"></span> Payment Reminder</div>
          <div class="info-body">
            <div class="info-row"><span>Please settle your payment before leaving.</span></div>
          </div>
        </div>
      </section>


      <section id="pendingPaymentSection" style="display:none;">
        <div class="info-card muted">
          <div class="info-title"><span class="mini-dot mini-amber"></span> Payment Pending</div>
          <div class="info-body">
            <div class="info-row"><span>Reference</span><span id="pendingRef">-</span></div>
            <div class="info-row"><span>Slot</span><span id="pendingSlot">-</span></div>
            <div class="info-row"><span>Duration</span><span id="pendingDur">-</span></div>
            <div class="info-row"><span>Amount</span><span>₱<span id="pendingAmt">0</span></span></div>
          </div>
        </div>
      </section>


      <section>
        <div class="notice" style="padding:12px 14px;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:8px;color:#374151;">
          Tip: If you don’t see an active session, go to Find Parking to start one.
        </div>
      </section>
      
      <!-- End Session Modal -->
      <div id="endModal" class="ps-modal" style="display:none;">
        <div class="ps-modal-backdrop" id="endModalBackdrop"></div>
        <div class="ps-modal-content" role="dialog" aria-modal="true" aria-labelledby="endModalTitle">
          <div class="ps-modal-header">
            <h3 id="endModalTitle">End Parking Session</h3>
          </div>
          <div class="ps-modal-body">
            <p>Are you sure you want to end your parking session now?</p>
            <div class="modal-info">
              <div class="modal-row"><span>Started at</span><span id="modalStart">--:--</span></div>
              <div class="modal-row"><span>Duration</span><span id="modalDuration">00:00:00</span></div>
              <div class="modal-row strong"><span>Total Amount</span><span>₱<span id="modalAmount">0</span></span></div>
            </div>
          </div>
          <div class="ps-modal-actions">
            <button type="button" class="btn" id="endModalCancel">Cancel</button>
            <button type="button" class="btn btn-danger" id="endModalConfirm"><i class="fas fa-sign-out-alt"></i> End Session</button>
          </div>
        </div>
      </div>
    </main>

    <nav class="bottom-nav">
      <a href="dashboard.php" class="nav-item">
        <i class="fas fa-home"></i>
        <span>Home</span>
      </a>
      <a href="find_parking.php" class="nav-item">
        <i class="fas fa-search"></i>
        <span>Find Parking</span>
      </a>
      <a href="my_parking.php" class="nav-item active">
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
  <script>
    // Simple clock (avoid dashboard.js dependency)
    const currentTimeEl = document.getElementById('currentTime');
    function updateCurrentTime() {
      if (!currentTimeEl) return;
      const now = new Date();
      const t = now.toLocaleTimeString('en-US', {hour:'2-digit', minute:'2-digit', hour12:true});
      const d = now.toLocaleDateString('en-US', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
      currentTimeEl.textContent = `${t} • ${d}`;
    }
    updateCurrentTime();
    setInterval(updateCurrentTime, 1000);

    // Load user name
    (async () => {
      try {
        const r = await fetch('../backend/api/user_info.php', { credentials:'include' });
        const j = await r.json();
        if (j.success && j.user) {
          const nameEl = document.getElementById('userName');
          if (nameEl) nameEl.textContent = j.user.full_name || j.user.username;
        }
      } catch {}
    })();

    // Constants
    const HOURLY_RATE = 10; // PHP per hour (display + should match backend)

    // Load parking status
    async function loadStatus() {
      try {
        const res = await fetch('../backend/api/driver_status.php', { credentials:'include' });
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Failed');
        renderStatus(data.parking_status);
      } catch (e) {
        renderError(e.message || 'Unable to load status');
      }
    }

    function renderStatus(s) {
      window.__lastParkingStatus = s || null;
      const icon = document.getElementById('statusIcon');
      const title = document.getElementById('statusTitle');
      const desc = document.getElementById('statusDesc');
      const extras = document.getElementById('statusExtras');
      const liveDurationEl = document.getElementById('liveDuration');
      const amountDueEl = document.getElementById('amountDue');
      const rateValueEl = document.getElementById('rateValue');
      const actions = document.getElementById('statusActions');
      const statusSection = document.getElementById('statusSection');
      const bannerSection = document.getElementById('activeBannerSection');
      const durationSection = document.getElementById('durationSection');
      const bannerSub = document.getElementById('bannerSub');
      const bannerStart = document.getElementById('bannerStart');
      const durH = document.getElementById('durHours');
      const durM = document.getElementById('durMinutes');
      const durS = document.getElementById('durSeconds');
      rateValueEl.textContent = HOURLY_RATE;

      // clear previous timer if any
      if (window.__parkingTimerId) {
        clearInterval(window.__parkingTimerId);
        window.__parkingTimerId = null;
      }

      if (s && s.is_parked) {
        icon.classList.add('parked');
        title.textContent = `Parked at Slot ${s.slot_number}`;
        desc.textContent = `Started at ${s.start_time}`;
        extras.style.display = '';
        actions.innerHTML = `
          <button class="btn btn-primary" id="btnExit"><i class="fas fa-sign-out-alt"></i> End Parking Session</button>
        `;
        document.getElementById('btnExit').addEventListener('click', openEndModal);
        // Show new banner + duration card; hide legacy status card container
        if (statusSection) statusSection.style.display = 'none';
        if (bannerSection) bannerSection.style.display = '';
        if (durationSection) durationSection.style.display = '';
        if (bannerSub) bannerSub.textContent = `Slot ${s.slot_number}`;
        if (bannerStart) bannerStart.textContent = s.start_time;
        const pendingSec = document.getElementById('pendingPaymentSection');
        if (pendingSec) pendingSec.style.display = 'none';
        // Clear any stored pending info since we now have an active session
        try { localStorage.removeItem('ps_last_payment_info'); } catch {}

        // start live ticker using start_time_iso from API
        const startIso = s.start_time_iso || null;
        if (startIso) {
          const startTs = new Date(startIso.replace(' ', 'T')).getTime();
          const tick = () => {
            const now = Date.now();
            let diff = Math.max(0, Math.floor((now - startTs) / 1000));
            const h = Math.floor(diff / 3600);
            diff %= 3600;
            const m = Math.floor(diff / 60);
            const sec = diff % 60;
            if (liveDurationEl) liveDurationEl.textContent = `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(sec).padStart(2,'0')}`;
            if (durH) durH.textContent = String(h).padStart(2,'0');
            if (durM) durM.textContent = String(m).padStart(2,'0');
            if (durS) durS.textContent = String(sec).padStart(2,'0');
            // Estimated due (rounded up to next hour, min 1 hour)
            const billableHours = Math.max(1, h + (m > 0 || sec > 0 ? 1 : 0));
            if (amountDueEl) amountDueEl.textContent = (billableHours * HOURLY_RATE).toString();
            const amountDueCardEl = document.getElementById('amountDueCard');
            if (amountDueCardEl) amountDueCardEl.textContent = (billableHours * HOURLY_RATE).toString();
            // Update modal summary if present
            const modalDur = document.getElementById('modalDuration');
            const modalAmt = document.getElementById('modalAmount');
            if (modalDur) modalDur.textContent = `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(sec).padStart(2,'0')}`;
            if (modalAmt) modalAmt.textContent = (billableHours * HOURLY_RATE).toString();
          };
          tick();
          window.__parkingTimerId = setInterval(tick, 1000);
        }

        const rateValueCard = document.getElementById('rateValueCard');
        if (rateValueCard) rateValueCard.textContent = HOURLY_RATE;
        document.getElementById('paymentDetailsSection').style.display = '';
        document.getElementById('reminderSection').style.display = '';
        const btnExitInDuration = document.getElementById('btnExitInDuration');
        if (btnExitInDuration) btnExitInDuration.onclick = openEndModal;
      } else {
        icon.classList.remove('parked');
        title.textContent = 'No Active Parking';
        desc.textContent = 'You are not currently parked';
        extras.style.display = 'none';
        actions.innerHTML = `
          <a class="btn btn-primary" href="find_parking.php"><i class="fas fa-search"></i> Find Parking</a>
        `;
        document.getElementById('paymentDetailsSection').style.display = 'none';
        document.getElementById('reminderSection').style.display = 'none';
        if (statusSection) statusSection.style.display = '';
        if (bannerSection) bannerSection.style.display = 'none';
        if (durationSection) durationSection.style.display = 'none';
        // If we have a recent payment from end_parking, show a pending summary
        let info = window.__lastPaymentInfo;
        if (!info) {
          try {
            const raw = localStorage.getItem('ps_last_payment_info');
            if (raw) {
              const obj = JSON.parse(raw);
              // Optional expiry: 24h
              if (!obj.expires_at || Date.now() < obj.expires_at) info = obj.data;
            }
          } catch {}
        }
        if (info) {
          const pendingSec = document.getElementById('pendingPaymentSection');
          if (pendingSec) pendingSec.style.display = '';
          const pr = document.getElementById('pendingRef');
          const ps = document.getElementById('pendingSlot');
          const pd = document.getElementById('pendingDur');
          const pa = document.getElementById('pendingAmt');
          if (pr) pr.textContent = info.reference_number || '-';
          if (ps) ps.textContent = info.slot_number || '-';
          if (pd) pd.textContent = info.duration_hms || '-';
          if (pa) pa.textContent = (info.amount ?? '0');
          // Always verify with backend to reflect admin approval changes
          // Start/refresh a poll to auto-hide when admin approves
          if (window.__pendingPollId) { clearInterval(window.__pendingPollId); window.__pendingPollId = null; }
          window.__pendingPollId = setInterval(() => {
            fetch('../backend/api/pending_payment.php', { credentials:'include' })
              .then(r => r.json()).then(pp => {
                const pendingSec2 = document.getElementById('pendingPaymentSection');
                if (pp && pp.success && pp.pending && pp.data) {
                  window.__lastPaymentInfo = pp.data;
                  try { localStorage.setItem('ps_last_payment_info', JSON.stringify({ data: pp.data, saved_at: Date.now(), expires_at: Date.now() + 24*60*60*1000 })); } catch {}
                  if (pendingSec2) pendingSec2.style.display = '';
                  const pr2 = document.getElementById('pendingRef');
                  const ps2 = document.getElementById('pendingSlot');
                  const pd2 = document.getElementById('pendingDur');
                  const pa2 = document.getElementById('pendingAmt');
                  if (pr2) pr2.textContent = pp.data.reference_number || '-';
                  if (ps2) ps2.textContent = pp.data.slot_number || '-';
                  if (pd2) pd2.textContent = pp.data.duration_hms || '-';
                  if (pa2) pa2.textContent = (pp.data.amount ?? '0');
                } else {
                  window.__lastPaymentInfo = null;
                  try { localStorage.removeItem('ps_last_payment_info'); } catch {}
                  if (pendingSec2) pendingSec2.style.display = 'none';
                  if (window.__pendingPollId) { clearInterval(window.__pendingPollId); window.__pendingPollId = null; }
                }
              }).catch(() => {});
          }, 15000);

          fetch('../backend/api/pending_payment.php', { credentials:'include' })
            .then(r => r.json()).then(pp => {
              const pendingSec2 = document.getElementById('pendingPaymentSection');
              if (pp && pp.success && pp.pending && pp.data) {
                window.__lastPaymentInfo = pp.data;
                try { localStorage.setItem('ps_last_payment_info', JSON.stringify({ data: pp.data, saved_at: Date.now(), expires_at: Date.now() + 24*60*60*1000 })); } catch {}
                if (pendingSec2) pendingSec2.style.display = '';
                const pr2 = document.getElementById('pendingRef');
                const ps2 = document.getElementById('pendingSlot');
                const pd2 = document.getElementById('pendingDur');
                const pa2 = document.getElementById('pendingAmt');
                if (pr2) pr2.textContent = pp.data.reference_number || '-';
                if (ps2) ps2.textContent = pp.data.slot_number || '-';
                if (pd2) pd2.textContent = pp.data.duration_hms || '-';
                if (pa2) pa2.textContent = (pp.data.amount ?? '0');
              } else {
                window.__lastPaymentInfo = null;
                try { localStorage.removeItem('ps_last_payment_info'); } catch {}
                if (pendingSec2) pendingSec2.style.display = 'none';
              }
            }).catch(() => {});
        } else {
          // Try backend to see if there is a pending payment for this user
          fetch('../backend/api/pending_payment.php', { credentials:'include' })
            .then(r => r.json()).then(pp => {
              if (pp && pp.success && pp.pending && pp.data) {
                window.__lastPaymentInfo = pp.data;
                try { localStorage.setItem('ps_last_payment_info', JSON.stringify({ data: pp.data, saved_at: Date.now(), expires_at: Date.now() + 24*60*60*1000 })); } catch {}
                const pendingSec = document.getElementById('pendingPaymentSection');
                if (pendingSec) pendingSec.style.display = '';
                const pr = document.getElementById('pendingRef');
                const ps = document.getElementById('pendingSlot');
                const pd = document.getElementById('pendingDur');
                const pa = document.getElementById('pendingAmt');
                if (pr) pr.textContent = pp.data.reference_number || '-';
                if (ps) ps.textContent = pp.data.slot_number || '-';
                if (pd) pd.textContent = pp.data.duration_hms || '-';
                if (pa) pa.textContent = (pp.data.amount ?? '0');
              } else {
                const pendingSec = document.getElementById('pendingPaymentSection');
                if (pendingSec) pendingSec.style.display = 'none';
                window.__lastPaymentInfo = null;
                try { localStorage.removeItem('ps_last_payment_info'); } catch {}
                if (window.__pendingPollId) { clearInterval(window.__pendingPollId); window.__pendingPollId = null; }
              }
            }).catch(() => {
              const pendingSec = document.getElementById('pendingPaymentSection');
              if (pendingSec) pendingSec.style.display = 'none';
              if (window.__pendingPollId) { clearInterval(window.__pendingPollId); window.__pendingPollId = null; }
            });
        }
      }
    }

    function renderError(msg) {
      const title = document.getElementById('statusTitle');
      const desc = document.getElementById('statusDesc');
      title.textContent = 'Unable to load status';
      desc.textContent = msg;
    }

    function openEndModal() {
      const modal = document.getElementById('endModal');
      if (!modal) return;
      modal.style.display = 'block';
      // trigger CSS transition
      requestAnimationFrame(() => modal.classList.add('show'));
      document.getElementById('endModalCancel').onclick = closeEndModal;
      document.getElementById('endModalBackdrop').onclick = closeEndModal;
      document.getElementById('endModalConfirm').onclick = async () => {
        await requestExit();
        closeEndModal();
      };
      document.addEventListener('keydown', escHandler);
      function escHandler(e){ if (e.key === 'Escape') { closeEndModal(); document.removeEventListener('keydown', escHandler); } }
      // Seed modal fields immediately
      const s = window.__lastParkingStatus;
      const modalStart = document.getElementById('modalStart');
      if (s && modalStart) modalStart.textContent = s.start_time || '--:--';
    }

    function closeEndModal() {
      const modal = document.getElementById('endModal');
      if (modal) {
        modal.classList.remove('show');
        setTimeout(() => { if (modal) modal.style.display = 'none'; }, 200);
      }
    }

    async function requestExit() {
      try {
        const res = await fetch('../backend/api/end_parking.php', { method:'POST', credentials:'include' });
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Failed to end session');
        const d = data.data || {};
        window.__lastPaymentInfo = d;
        try {
          localStorage.setItem('ps_last_payment_info', JSON.stringify({ data: d, saved_at: Date.now(), expires_at: Date.now() + 24*60*60*1000 }));
        } catch {}
        (function(){
          const msg = 'The payment is being processed.';
          if (typeof window.showToast === 'function') {
            window.showToast(msg, 'info', 5000);
          } else {
            alert(msg);
          }
        })();
        // Refresh status after a brief delay
        setTimeout(loadStatus, 500);
      } catch (e) {
        if (typeof window.showToast === 'function') {
          window.showToast(e.message || 'Unable to end session', 'error');
        } else {
          alert(e.message || 'Unable to end session');
        }
      }
    }

    loadStatus();
  </script>
</body>
</html>
