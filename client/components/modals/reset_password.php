<!-- 6. RESET PASSWORD MODAL -->
<div id="reset-password-modal" class="modal">
  <div class="modal-overlay" id="reset-password-overlay"></div>
  <div class="modal-content glass-modal small-modal">
    <button class="modal-close" id="btn-close-reset-modal">&times;</button>
    <div class="modal-body">
      <h2 style="font-family: var(--font-serif); text-align: center; margin-bottom: 20px; color: var(--color-primary-gold);">Reset Password</h2>
      <p style="font-size: 0.8rem; text-align: center; color: var(--color-text-muted); margin-top: -10px; margin-bottom: 20px;">Masukkan username & nomor telepon terdaftar untuk mengganti kata sandi Anda</p>
      <form id="form-reset-password">
        <div class="input-group">
          <input type="text" id="reset-username" placeholder="Username" required>
        </div>
        <div class="input-group">
          <input type="tel" id="reset-phone" placeholder="Phone Number" required>
        </div>
        <div class="input-group password-group">
          <input type="password" id="reset-new-password" placeholder="New Password" required>
          <span class="toggle-password" data-target="reset-new-password">
            <svg class="eye-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
              <circle cx="12" cy="12" r="3"></circle>
            </svg>
          </span>
        </div>
        <div class="input-group password-group">
          <input type="password" id="reset-confirm-password" placeholder="Confirm New Password" required>
          <span class="toggle-password" data-target="reset-confirm-password">
            <svg class="eye-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
              <circle cx="12" cy="12" r="3"></circle>
            </svg>
          </span>
        </div>

        <div class="error-message" id="reset-modal-error">
          Error! Data tidak cocok atau password baru tidak sesuai.
        </div>

        <button type="submit" class="btn btn-translucent btn-auth mt-2">Reset Password!</button>
      </form>
    </div>
  </div>
</div>
