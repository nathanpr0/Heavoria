<!-- 1. SIDE CART DRAWER (USER ONLY) -->
<div id="cart-drawer" class="drawer">
  <div class="drawer-overlay" id="cart-drawer-overlay"></div>
  <div class="drawer-content">
    <div class="drawer-header">
      <h2>Keranjang Belanja</h2>
      <button class="btn-close-drawer" id="btn-close-cart">
        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>

    <div class="drawer-body">
      <div class="input-meja-group">
        <label for="cart-table-number">Nomor Meja:</label>
        <input type="number" id="cart-table-number" min="1" max="50" placeholder="Contoh: 12" required>
      </div>

      <div class="cart-items-list" id="cart-items-container">
        <!-- Dinamis via JS -->
      </div>

      <div class="cart-summary-card">
        <div class="summary-row">
          <span>Subtotal</span>
          <span id="cart-subtotal">Rp 0</span>
        </div>
        <div class="summary-row">
          <span>Pajak (10%)</span>
          <span id="cart-tax">Rp 0</span>
        </div>
        <div class="summary-row">
          <span>Service Charge (5%)</span>
          <span id="cart-service">Rp 0</span>
        </div>
        <hr class="summary-divider">
        <div class="summary-row total-row">
          <span>Total Bayar</span>
          <span id="cart-total" class="text-gold">Rp 0</span>
        </div>
      </div>
    </div>

    <div class="drawer-footer">
      <button id="btn-checkout" class="btn btn-gold w-100">Checkout & Bayar</button>
    </div>
  </div>
</div>
