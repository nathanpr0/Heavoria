<!-- ADMIN TRANSACTIONS TAB -->
<div id="tab-admin-transactions" class="tab-pane">
  <div class="section-header flex-header">
    <div>
      <h2>Laporan Penjualan</h2>
      <p>Riwayat transaksi pembayaran pelanggan yang telah diselesaikan.</p>
    </div>
    <button type="button" id="btn-print-sales-report" class="btn-print-report">Cetak Laporan</button>
  </div>
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID Transaksi</th>
          <th>Waktu</th>
          <th>Pickup / Delivery</th>
          <th>Pesanan</th>
          <th>Pembayaran</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody id="admin-transactions-table-body">
        <!-- Dinamis via JS -->
      </tbody>
    </table>
  </div>
</div>
