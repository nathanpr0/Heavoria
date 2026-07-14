<!-- 2. PRE-ORDER MODAL (6.1.1 - PICK UP & DELIVERY CALCULATOR) -->
<div id="preorder-modal" class="modal">
  <div class="modal-overlay" id="preorder-modal-overlay"></div>
  <div class="modal-content glass-modal medium-modal">
    <button class="modal-close" id="btn-close-preorder">&times;</button>
    <div class="modal-body">
      <h2 style="font-family: var(--font-serif); color: var(--color-primary-gold); margin-bottom: 15px; text-align: center;">Pre Order Details</h2>
      
      <div class="order-type-tabs">
        <button class="type-tab active" id="tab-type-pickup">Pick Up dari UBD</button>
        <button class="type-tab" id="tab-type-delivery">Delivery</button>
      </div>

      <!-- Delivery Option Grid -->
      <div id="delivery-details-pane" class="delivery-details-pane">
        <div class="input-group">
          <label for="delivery-distance">Jarak Pengiriman (KM):</label>
          <div class="distance-input-box">
            <input type="number" id="delivery-distance" min="1" max="100" value="1">
            <span class="distance-unit">KM</span>
          </div>
          <p class="distance-hint">Biaya kirim: Rp 4.000 / km. Gratis untuk pick up dari Kampus UBD.</p>
        </div>

        <!-- Google Maps Mockup -->
        <div class="google-maps-mock">
          <div class="maps-overlay-info">
            <span>Restoran Heavoria UBD</span>
            <span class="text-gold">📍 Pin Point Active</span>
          </div>
          <div class="maps-circle-pin"></div>
          <div class="maps-label-tag">Customer Location (Est. <span id="display-map-distance">1</span> KM)</div>
        </div>
      </div>

      <div id="pickup-details-pane" class="pickup-details-pane active">
        <div class="pickup-info-card glass-card">
          <h4>📍 Lokasi Pengambilan:</h4>
          <p>Universitas Buddhi Dharma (UBD), Tangerang, Banten</p>
          <p class="text-gold" style="font-size: 0.8rem; margin-top: 5px;">* Bebas Ongkos Kirim (Rp 0)</p>
        </div>
      </div>

      <div class="input-group mt-2">
        <label for="preorder-address">Alamat / Catatan Pengambilan:</label>
        <textarea id="preorder-address" rows="3" placeholder="Contoh: Ambil di lobby UBD atau alamat delivery lengkap"></textarea>
      </div>

      <div class="preorder-summary-box glass-card mt-2">
        <div class="summary-row">
          <span>Subtotal:</span>
          <span id="pre-subtotal">Rp 0</span>
        </div>
        <div class="summary-row">
          <span>Ongkos Kirim:</span>
          <span id="pre-shipping" class="text-gold">Rp 0</span>
        </div>
        <hr style="border-color: #333; margin: 8px 0;">
        <div class="summary-row bold-row">
          <span>Total Akhir:</span>
          <span id="pre-grandtotal" class="text-gold" style="font-size: 1.1rem;">Rp 0</span>
        </div>
      </div>

      <button id="btn-preorder-proceed" class="btn btn-gold w-100 mt-3">Lanjut Ke Pembayaran</button>
    </div>
  </div>
</div>
