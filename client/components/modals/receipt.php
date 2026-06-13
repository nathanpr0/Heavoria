<!-- 4. RECEIPT/STRUK DIGITAL MODAL -->
<div id="receipt-modal" class="modal">
  <div class="modal-overlay" id="receipt-modal-overlay"></div>
  <div class="modal-content glass-modal receipt-modal-box">
    <div class="receipt-paper">
      <div class="receipt-header">
        <h2>HEAVORIA</h2>
        <p>Taste Of Heaven</p>
        <p class="receipt-small">Jl. Golden Paradise No. 77</p>
        <hr class="dotted-divider">
      </div>
      <div class="receipt-info">
        <p><strong>Order ID:</strong> <span id="rec-order-id">ORD-000</span></p>
        <p><strong>Tanggal:</strong> <span id="rec-date">2026-06-06 12:00</span></p>
        <p><strong>Meja:</strong> <span id="rec-table">0</span></p>
        <p><strong>Metode:</strong> <span id="rec-method">QRIS</span></p>
        <hr class="dotted-divider">
      </div>
      <div class="receipt-items" id="rec-items-container">
        <!-- Dinamis -->
      </div>
      <hr class="dotted-divider">
      <div class="receipt-totals">
        <div class="rec-row">
          <span>Subtotal</span>
          <span id="rec-subtotal">Rp 0</span>
        </div>
        <div class="rec-row">
          <span>Pajak (10%)</span>
          <span id="rec-tax">Rp 0</span>
        </div>
        <div class="rec-row">
          <span>Service (5%)</span>
          <span id="rec-service">Rp 0</span>
        </div>
        <div class="rec-row bold-row">
          <span>Total Bayar</span>
          <span id="rec-total">Rp 0</span>
        </div>
      </div>
      <div class="receipt-footer">
        <hr class="dotted-divider">
        <p>Terima Kasih Atas Kunjungan Anda</p>
        <p>~ Taste of Heaven ~</p>
      </div>
    </div>
    <button id="btn-close-receipt" class="btn btn-gold w-100 mt-2">Tutup & Selesai</button>
  </div>
</div>
