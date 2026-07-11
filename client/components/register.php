<!-- 3.0 REGISTER (SIGN IN) SCREEN -->
<section id="register-screen" class="screen-view">
  <div class="logo-container">
    <div class="circular-logo">
      <img src="assets/heavoria_logorev.jpeg" alt="Heavoria Logo" onerror="this.src='https://placehold.co/100x100/422/fd3?text=H'">
    </div>
  </div>

  <div class="welcome-header login-header">
    <h1 class="welcome-title">WELCOME TO</h1>
    <h2 class="welcome-brand">HEAVORIA</h2>
    <div class="welcome-divider">
      <span class="diamond"></span>
    </div>
    <p class="welcome-subtitle">Taste Of Heaven</p>
  </div>

  <div class="auth-box register-box">
    <form id="form-register" autocomplete="off">
      <div class="input-group">
        <input type="text" id="reg-username" placeholder="Username" required>
      </div>
      <div class="input-group">
        <input type="tel" id="reg-phone" placeholder="Phone Number" required>
      </div>
      <div class="input-group">
        <input type="email" id="reg-email" placeholder="Gmail Address" required>
      </div>
      <div class="input-group password-group">
        <input type="password" id="reg-password" placeholder="Enter Password" required>
        <span class="toggle-password" data-target="reg-password">
          <svg class="eye-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
          </svg>
        </span>
      </div>
      <div class="input-group password-group">
        <input type="password" id="reg-confirm-password" placeholder="Confirm Password" required>
        <span class="toggle-password" data-target="reg-confirm-password">
          <svg class="eye-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
          </svg>
        </span>
      </div>
      
      <div class="error-message" id="reg-error">
        Error! The passwords do not match.
      </div>

      <button type="submit" class="btn btn-translucent btn-auth">Sign In!</button>
    </form>
    <div class="auth-links" style="display: flex; justify-content: center; margin-top: 15px;">
      <a href="#" class="auth-link" id="link-goto-login-from-reg">Sudah punya akun? Login!</a>
    </div>
  </div>

  <div class="back-navigation">
    <button class="btn-back btn-back-welcome">
      <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 5"></polyline>
      </svg> Kembali ke Lobby
    </button>
  </div>
</section>
