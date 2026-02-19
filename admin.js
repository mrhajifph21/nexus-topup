// =========================================
// NEXUS TOP UP - ADMIN JAVASCRIPT
// =========================================

document.addEventListener('DOMContentLoaded', () => {

  // ---- Sidebar toggle (mobile) ----
  const sidebarToggle = document.getElementById('sidebar-toggle');
  const sidebar = document.querySelector('.admin-sidebar');

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('open');
    });
  }

  // ---- Active sidebar link ----
  const currentPath = window.location.pathname;
  document.querySelectorAll('.sidebar-nav a').forEach(link => {
    if (link.href.includes(currentPath.split('/').pop())) {
      link.classList.add('active');
    }
  });

  // ---- Update transaction status ----
  window.updateStatus = function(transactionId, currentStatus) {
    const statusOptions = ['pending', 'processing', 'success', 'failed', 'refunded'];
    
    const modal = document.createElement('div');
    modal.className = 'modal-overlay active';
    modal.innerHTML = `
      <div class="modal">
        <h3 style="margin-bottom: 20px; font-family: var(--font-display);">Update Status Transaksi</h3>
        <div class="form-group">
          <label class="form-label">Status Baru</label>
          <select class="form-control" id="new-status">
            ${statusOptions.map(s => `<option value="${s}" ${s === currentStatus ? 'selected' : ''}>${s.charAt(0).toUpperCase() + s.slice(1)}</option>`).join('')}
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Catatan (opsional)</label>
          <textarea class="form-control" id="status-notes" rows="3" placeholder="Tambahkan catatan..."></textarea>
        </div>
        <div class="flex gap-3 mt-6">
          <button class="btn btn-primary" onclick="submitStatusUpdate(${transactionId})">Update Status</button>
          <button class="btn btn-ghost" onclick="this.closest('.modal-overlay').remove()">Batal</button>
        </div>
      </div>
    `;
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
    
    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        modal.remove();
        document.body.style.overflow = '';
      }
    });
  };

  window.submitStatusUpdate = async function(transactionId) {
    const status = document.getElementById('new-status').value;
    const notes = document.getElementById('status-notes').value;

    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('transaction_id', transactionId);
    formData.append('status', status);
    formData.append('notes', notes);

    try {
      const res = await fetch('../php/transaction.php', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();

      if (data.success) {
        document.querySelector('.modal-overlay')?.remove();
        document.body.style.overflow = '';
        showAdminNotif('Status berhasil diupdate!', 'success');
        setTimeout(() => location.reload(), 1500);
      } else {
        showAdminNotif('Gagal update status: ' + data.message, 'error');
      }
    } catch (err) {
      showAdminNotif('Terjadi kesalahan!', 'error');
    }
  };

  // ---- Delete confirmation ----
  window.confirmDelete = function(message, callback) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay active';
    modal.innerHTML = `
      <div class="modal" style="max-width: 380px; text-align: center;">
        <div style="font-size: 3rem; margin-bottom: 16px;">⚠️</div>
        <h3 style="margin-bottom: 12px; font-family: var(--font-display);">Konfirmasi Hapus</h3>
        <p style="color: var(--text-muted); margin-bottom: 28px; font-size: 0.9rem;">${message}</p>
        <div class="flex gap-3 flex-center">
          <button class="btn btn-danger" onclick="confirmDeleteAction(this)">Ya, Hapus</button>
          <button class="btn btn-ghost" onclick="this.closest('.modal-overlay').remove(); document.body.style.overflow=''">Batal</button>
        </div>
      </div>
    `;
    modal.dataset.callback = callback;
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
  };

  window.confirmDeleteAction = function(btn) {
    const modal = btn.closest('.modal-overlay');
    const callbackName = modal.dataset.callback;
    modal.remove();
    document.body.style.overflow = '';
    if (window[callbackName]) window[callbackName]();
  };

  // ---- Notification ----
  window.showAdminNotif = function(message, type = 'info') {
    const notif = document.createElement('div');
    notif.className = `flash-message flash-${type}`;
    notif.style.cssText = 'position:fixed; top:85px; right:24px; z-index:999;';
    notif.innerHTML = `<span>${type === 'success' ? '✓' : '✕'}</span><span>${message}</span>`;
    document.body.appendChild(notif);
    setTimeout(() => notif.remove(), 4000);
  };

  // ---- Chart placeholder (simple stats bars) ----
  const chartContainer = document.getElementById('revenue-chart');
  if (chartContainer && window.chartData) {
    renderSimpleChart(chartContainer, window.chartData);
  }

  function renderSimpleChart(container, data) {
    const max = Math.max(...data.values);
    container.innerHTML = `
      <div style="display:flex; align-items:flex-end; gap:8px; height:120px; padding: 0 4px;">
        ${data.values.map((v, i) => `
          <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:4px;">
            <div style="
              width:100%; 
              height:${Math.round((v / max) * 100)}%; 
              background: linear-gradient(to top, #1d4ed8, #60a5fa);
              border-radius: 4px 4px 0 0;
              transition: height 0.5s ease;
              min-height: 4px;
            "></div>
            <span style="font-size:0.65rem; color:var(--text-muted);">${data.labels[i]}</span>
          </div>
        `).join('')}
      </div>
    `;
  }

  // ---- Image preview for file inputs ----
  document.querySelectorAll('.file-input-preview').forEach(input => {
    input.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        const previewId = input.dataset.preview;
        reader.onload = (ev) => {
          const preview = document.getElementById(previewId);
          if (preview) preview.src = ev.target.result;
        };
        reader.readAsDataURL(file);
      }
    });
  });

  // ---- Table row search filter ----
  const tableSearch = document.getElementById('table-search');
  if (tableSearch) {
    tableSearch.addEventListener('input', (e) => {
      const q = e.target.value.toLowerCase();
      document.querySelectorAll('tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  }

});
