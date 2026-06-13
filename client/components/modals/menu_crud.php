<!-- 5. CRUD MENU MODAL (ADMIN ONLY) -->
<div id="menu-crud-modal" class="modal">
  <div class="modal-overlay" id="menu-crud-overlay"></div>
  <div class="modal-content glass-modal">
    <button class="modal-close" id="btn-close-menu-crud">&times;</button>
    <div class="modal-body">
      <h2 id="crud-modal-title">Tambah Menu Baru</h2>
      <form id="form-menu-crud">
        <input type="hidden" id="crud-item-id">
        
        <div class="input-group">
          <label for="crud-item-name">Nama Menu:</label>
          <input type="text" id="crud-item-name" required placeholder="Contoh: Royal Chocolate Roll Cake">
        </div>

        <div class="input-group">
          <label for="crud-item-category">Kategori:</label>
          <select id="crud-item-category" required>
            <option value="sushi">Sushi & Roll</option>
            <option value="cake">Kue & Desserts</option>
            <option value="drink">Minuman Mewah</option>
          </select>
        </div>

        <div class="input-group">
          <label for="crud-item-price">Harga (Rupiah):</label>
          <input type="number" id="crud-item-price" required min="1000" placeholder="Contoh: 45000">
        </div>

        <div class="input-group">
          <label for="crud-item-desc">Deskripsi:</label>
          <textarea id="crud-item-desc" required placeholder="Jelaskan cita rasa hidangan ini..."></textarea>
        </div>

        <div class="input-group">
          <label for="crud-item-image">Pilihan Gambar Menu:</label>
          <select id="crud-item-image" required>
            <!-- Opsi gambar representatif yang siap pakai -->
            <option value="assets/heavoria_cake.png">Kemewahan Kue Chocolate (heavoria_cake.png)</option>
            <option value="assets/heavoria_sushi.png">Kesegaran Salmon Sushi (heavoria_sushi.png)</option>
            <option value="assets/heavoria_drink.png">Iced Coffee Golden Flakes (heavoria_drink.png)</option>
            <option value="https://placehold.co/300x200/422/fd3?text=Heavoria+Cuisine">Placeholder Makanan Umum</option>
          </select>
        </div>

        <button type="submit" id="btn-submit-crud" class="btn btn-gold w-100 mt-2">Simpan Menu</button>
      </form>
    </div>
  </div>
</div>
