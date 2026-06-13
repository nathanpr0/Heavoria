<!-- 2. DETAIL FOOD MODAL (USER ONLY) -->
<div id="food-detail-modal" class="modal">
  <div class="modal-overlay modal-close-btn"></div>
  <div class="modal-content glass-modal">
    <button class="modal-close modal-close-btn">&times;</button>
    <div class="modal-body food-detail-layout">
      <div class="food-detail-image-box">
        <img id="modal-food-img" src="" alt="Food Image">
      </div>
      <div class="food-detail-info-box">
        <span id="modal-food-category" class="food-category-tag">Kategori</span>
        <h2 id="modal-food-title">Nama Makanan</h2>
        <p id="modal-food-desc" class="food-description">Deskripsi detail mengenai hidangan lezat Heavoria ini.</p>
        <h3 id="modal-food-price" class="food-price text-gold">Rp 0</h3>

        <!-- Options -->
        <div class="food-options">
          <label for="modal-food-notes">Catatan Tambahan:</label>
          <textarea id="modal-food-notes" placeholder="Contoh: Kurang manis, tanpa saus, atau pedas level 3..."></textarea>
        </div>

        <div class="quantity-row">
          <div class="qty-control">
            <button class="qty-btn" id="btn-qty-minus">-</button>
            <span id="modal-qty-display" class="qty-val">1</span>
            <button class="qty-btn" id="btn-qty-plus">+</button>
          </div>
          <button id="btn-add-to-cart" class="btn btn-gold flex-grow">Tambahkan ke Keranjang</button>
        </div>
      </div>
    </div>
  </div>
</div>
