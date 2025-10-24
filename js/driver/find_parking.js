(() => {
  const gridEl = document.getElementById('slotGrid');
  const refreshBtn = document.getElementById('refreshBtn');
  const currentTimeEl = document.getElementById('currentTime');
  const userNameEl = document.getElementById('userName');
  const userFirstNameEl = document.getElementById('userFirstName');

  async function fetchSlots() {
    try {
      const res = await fetch('../backend/api/available_slots.php?status=available', { credentials: 'include' });
      const data = await res.json();
      if (!data.success) throw new Error(data.message || 'Failed to load slots');
      renderSlots(data.data.slots || []);
    } catch (err) {
      renderError(err.message || 'Error fetching slots');
    }
  }

  function renderError(msg) {
    gridEl.innerHTML = `<div class="notice" role="alert">${msg}</div>`;
  }

  function renderSlots(slots) {
    if (!Array.isArray(slots) || slots.length === 0) {
      gridEl.innerHTML = '<div class="notice">No slots found.</div>';
      return;
    }
    // Sort: available first, then maintenance, then occupied; then by slot_number
    const order = { available: 0, maintenance: 1, occupied: 2 };
    const sorted = [...slots].sort((a, b) => {
      const oa = order[a.status] ?? 99;
      const ob = order[b.status] ?? 99;
      if (oa !== ob) return oa - ob;
      return String(a.slot_number).localeCompare(String(b.slot_number), undefined, { numeric: true, sensitivity: 'base' });
    });

    gridEl.innerHTML = '';
    for (const s of sorted) {
      const item = document.createElement('button');
      item.className = `slot ${s.status}`;
      item.setAttribute('role', 'gridcell');
      const statusClass = escapeHtml(s.status);
      item.innerHTML = `
        <div class="icon ${statusClass}"><i class="fas fa-parking"></i></div>
        <div class="label">${escapeHtml(s.slot_number)}</div>
        <div class="status-pill ${statusClass}"><span class="dot"></span>${statusClass.charAt(0).toUpperCase()}${statusClass.slice(1)}</div>
      `;
      if (s.status === 'available') {
        item.addEventListener('click', () => confirmAndStart(s));
      } else {
        item.disabled = true;
      }
      gridEl.appendChild(item);
    }
  }

  async function confirmAndStart(slot) {
    let ok = true;
    if (typeof window.psConfirm === 'function') {
      ok = await window.psConfirm(`Start parking on slot ${slot.slot_number}?`, { title: 'Start Parking', confirmText: 'Start' });
    } else {
      ok = window.confirm(`Start parking on slot ${slot.slot_number}?`);
    }
    if (!ok) return;
    try {
      const form = new FormData();
      form.append('slot_id', slot.slot_id);
      const res = await fetch('../backend/api/start_parking.php', {
        method: 'POST',
        body: form,
        credentials: 'include'
      });
      const data = await res.json();
      if (!data.success) throw new Error(data.message || 'Failed to start parking');
      if (typeof window.showToast === 'function') {
        window.showToast('Parking started successfully.', 'success');
      } else {
        alert('Parking started successfully.');
      }
      await fetchSlots();
      // Optionally redirect to My Parking page if exists
      // window.location.href = '/driver/my_parking.php';
    } catch (err) {
      if (typeof window.showToast === 'function') {
        window.showToast(err.message || 'Unable to start parking.', 'error');
      } else {
        alert(err.message || 'Unable to start parking.');
      }
    }
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  refreshBtn?.addEventListener('click', fetchSlots);
  fetchSlots();
  // Polling to keep grid fresh (every 10s)
  setInterval(fetchSlots, 10000);

  // Lightweight clock updater for this page
  function updateCurrentTime() {
    if (!currentTimeEl) return;
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
    const dateString = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    currentTimeEl.textContent = `${timeString} • ${dateString}`;
  }
  updateCurrentTime();
  setInterval(updateCurrentTime, 1000);

  // Load user info to replace placeholder name in header
  async function loadUserInfo() {
    try {
      const res = await fetch('../backend/api/user_info.php', { credentials: 'include' });
      if (!res.ok) return;
      const data = await res.json();
      if (data && data.success && data.user) {
        if (userNameEl) userNameEl.textContent = data.user.full_name || data.user.username || 'User';
        if (userFirstNameEl) userFirstNameEl.textContent = data.user.first_name || 'User';
      }
    } catch (_) {
      // ignore; keep placeholder
    }
  }
  loadUserInfo();
})();

