<!-- CONTACT US TAB -->
<div id="tab-contact" class="tab-pane">
  <div class="section-header">
    <h2>Hubungi Kami</h2>
    <p>Kami siap melayani kebutuhan bersantap mewah Anda.</p>
  </div>
  <div class="contact-layout">
    <div class="contact-info glass-card">
      <h3>Restoran Heavoria</h3>
      <p>📍 Jl. Golden Paradise No. 77, Jakarta</p>
      <p>📞 +62 812-3456-7890</p>
      <p>✉ info@heavoria.com</p>
      <p>🕒 Jam Operasional: 10:00 - 22:00 WIB</p>
    </div>
    <div class="contact-form-pane glass-card">
      <h3>Kirim Pesan</h3>
      <form id="form-contact" onsubmit="event.preventDefault(); alert('Pesan Anda telah terkirim! Terima kasih.'); this.reset();">
        <input type="text" placeholder="Nama Anda" required class="contact-input">
        <input type="email" placeholder="Email Anda" required class="contact-input">
        <textarea placeholder="Pesan Anda..." required class="contact-input" style="height:80px;"></textarea>
        <button type="submit" class="btn btn-gold w-100">Kirim Pesan</button>
      </form>
    </div>
  </div>
</div>
