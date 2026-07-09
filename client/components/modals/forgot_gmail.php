<!-- 4.0 FORGOT PASSWORD GMAIL SCREEN -->
<section id="forgot-pwd-screen" class="screen-view">
  <div class="logo-container">
    <div class="circular-logo">
      <img src="assets/heavoria_logorev.jpeg" alt="Heavoria Logo" onerror="this.src='https://placehold.co/100x100/422/fd3?text=H'">
    </div>
  </div>

  <div class="welcome-header login-header">
    <h1 class="welcome-title">FORGOT</h1>
    <h2 class="welcome-brand">PASSWORD</h2>
    <div class="welcome-divider">
      <span class="diamond"></span>
    </div>
    <p class="welcome-subtitle">Masukkan alamat Gmail Anda</p>
  </div>

  <div class="auth-box">
    <form id="form-forgot-gmail" autocomplete="off">
      <div class="input-group">
        <input type="email" id="forgot-gmail-address" placeholder="Gmail Address" required>
      </div>
      <div class="error-message" id="forgot-gmail-error">
        Error! Gmail address not found / invalid format.
      </div>
      <button type="submit" class="btn btn-translucent btn-auth">Send Verification Code</button>
    </form>
  </div>
  <div class="back-navigation">
    <button class="btn-back" id="btn-back-to-login">
      <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
      </svg> Kembali ke Login
    </button>
  </div>
</section>
