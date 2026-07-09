<!-- 6. RATING / TESTIMONI FEEDBACK MODAL (7.1.5) -->
<div id="rating-modal" class="modal">
  <div class="modal-overlay" id="rating-modal-overlay"></div>
  <div class="modal-content glass-modal small-modal">
    <button class="modal-close" id="btn-close-rating">&times;</button>
    <div class="modal-body text-center">
      <h2 style="font-family: var(--font-serif); color: var(--color-primary-gold); margin-bottom: 10px;">Beri Ulasan Hidangan</h2>
      <p>Bagikan penilaian Anda tentang cita rasa hidangan Heavoria</p>
      
      <input type="hidden" id="rating-order-id">
      
      <!-- Stars Select Box -->
      <div class="rating-select-stars" style="font-size: 2.2rem; margin: 15px 0; cursor: pointer; color: #555;">
        <span class="rating-star-btn" data-star="1">★</span>
        <span class="rating-star-btn" data-star="2">★</span>
        <span class="rating-star-btn" data-star="3">★</span>
        <span class="rating-star-btn" data-star="4">★</span>
        <span class="rating-star-btn" data-star="5">★</span>
      </div>
      
      <div class="input-group">
        <textarea id="rating-comment" placeholder="Tulis masukan atau kesan Anda di sini..." style="height: 80px; width: 100%;" required></textarea>
      </div>
      
      <button id="btn-submit-rating" class="btn btn-gold w-100 mt-2">Kirim Ulasan</button>
    </div>
  </div>
</div>
