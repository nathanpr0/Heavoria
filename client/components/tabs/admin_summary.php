<!-- ADMIN SUMMARY TAB -->
<div id="tab-admin-summary" class="tab-pane">
  <div class="admin-stats-grid">
    <div class="stat-card">
      <div class="stat-icon icon-revenue">
        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="12" y1="1" x2="12" y2="23"></line>
          <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
        </svg>
      </div>
      <div class="stat-content">
        <h3>Total Pendapatan</h3>
        <p class="stat-number" id="stat-total-revenue">Rp 0</p>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon icon-orders">
        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
          <polyline points="14 2 14 8 20 8"></polyline>
          <line x1="16" y1="13" x2="8" y2="13"></line>
          <line x1="16" y1="17" x2="8" y2="17"></line>
          <polyline points="10 9 9 9 8 9"></polyline>
        </svg>
      </div>
      <div class="stat-content">
        <h3>Jumlah Pesanan</h3>
        <p class="stat-number" id="stat-total-orders">0</p>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-icon icon-popular">
        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
          <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
        </svg>
      </div>
      <div class="stat-content">
        <h3>Menu Terlaris</h3>
        <p class="stat-number" id="stat-popular-item">-</p>
      </div>
    </div>
  </div>

  <div class="chart-container-row">
    <div class="summary-chart-card">
      <h3>Performa Penjualan Kategori</h3>
      <div class="custom-bar-chart" id="category-chart">
        <!-- Bar chart dinamis digambar via CSS -->
      </div>
    </div>
  </div>
</div>
