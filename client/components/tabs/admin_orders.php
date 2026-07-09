<!-- ADMIN ORDERS TAB -->
<div id="tab-admin-orders" class="tab-pane">
  <div class="section-header flex-header">
    <div>
      <h2>Verifikasi Pesanan Client</h2>
      <p>Konfirmasi, tolak, tandai siap, dan selesaikan pesanan pelanggan.</p>
    </div>
    <div class="filter-group">
      <button class="status-filter-btn active" data-status-filter="all">Semua</button>
      <button class="status-filter-btn" data-status-filter="Menunggu Verifikasi">Waiting</button>
      <button class="status-filter-btn" data-status-filter="Dikonfirmasi">Confirmed</button>
      <button class="status-filter-btn" data-status-filter="Siap Diambil">Ready</button>
    </div>
  </div>
  <div class="admin-orders-list" id="admin-orders-container">
    <!-- Dinamis via JS -->
  </div>
</div>
