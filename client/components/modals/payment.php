<!-- 3. PAYMENT MODAL -->
<div id="payment-modal" class="modal">
  <div class="modal-overlay" id="payment-modal-overlay"></div>
  <div class="modal-content glass-modal small-modal">
    <button class="modal-close" id="btn-close-payment">&times;</button>
    <div class="modal-body text-center">
      <h2>Pilih Metode Pembayaran</h2>
      <p>Silakan pilih metode transaksi yang Anda inginkan</p>

      <div class="payment-methods-grid">
        <label class="payment-method-card active">
          <input type="radio" name="payment-method" value="QRIS" checked>
          <div class="method-box">
            <div class="method-icon">QR</div>
            <span>QRIS Digital</span>
          </div>
        </label>
        <label class="payment-method-card">
          <input type="radio" name="payment-method" value="Kartu">
          <div class="method-box">
            <div class="method-icon">💳</div>
            <span>Debit / Kartu</span>
          </div>
        </label>
        <label class="payment-method-card">
          <input type="radio" name="payment-method" value="Tunai">
          <div class="method-box">
            <div class="method-icon">💵</div>
            <span>Tunai / Kasir</span>
          </div>
        </label>
      </div>

      <!-- Dynamic Payment Details (QR Code Mockup) -->
      <div id="payment-details-qris" class="payment-details-pane active">
        <div class="qris-box">
          <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Heavoria-Restaurant-Payment-Sim" alt="QRIS Code" class="qris-code">
          <p class="text-sm">Scan QRIS di atas untuk melakukan simulasi pembayaran</p>
        </div>
      </div>

      <div id="payment-details-card" class="payment-details-pane">
        <div class="card-form-mock">
          <input type="text" placeholder="Nomor Kartu (Simulasi)" class="w-100 card-input">
          <input type="text" placeholder="Nama Pemegang Kartu" class="w-100 card-input">
        </div>
      </div>

      <div id="payment-details-cash" class="payment-details-pane">
        <p class="cash-notice">Silakan lakukan pembayaran tunai sebesar <strong id="cash-payment-amount" class="text-gold">Rp 0</strong> langsung ke kasir dengan menyebutkan Nomor Meja Anda.</p>
      </div>

      <button id="btn-process-payment" class="btn btn-gold w-100 mt-2">Bayar Sekarang</button>
    </div>
  </div>
</div>
