<!-- 4.1.4 INPUT NEW PASSWORD SCREEN -->
<section id="forgot-new-pwd-screen" class="screen-view">
  <div class="logo-container">
    <div class="circular-logo">
      <img src="assets/heavoria_logorev.jpeg" alt="Heavoria Logo" onerror="this.src='https://placehold.co/100x100/422/fd3?text=H'">
    </div>
  </div>

  <div class="welcome-header login-header">
    <h1 class="welcome-title">RESET</h1>
    <h2 class="welcome-brand">PASSWORD</h2>
    <div class="welcome-divider">
      <span class="diamond"></span>
    </div>
    <p class="welcome-subtitle">Masukkan password baru Anda</p>
  </div>

  <div class="auth-box">
    <form id="form-new-password" autocomplete="off">
      <div class="input-group password-group">
        <input type="password" id="forgot-new-pwd" placeholder="Enter New Password" required>
        <span class="toggle-password" data-target="forgot-new-pwd">
          <svg class="eye-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
          </svg>
        </span>
      </div>
      <div class="input-group password-group">
        <input type="password" id="forgot-confirm-new-pwd" placeholder="Confirm Password" required>
        <span class="toggle-password" data-target="forgot-confirm-new-pwd">
          <svg class="eye-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
          </svg>
        </span>
      </div>
      <div class="error-message" id="forgot-new-pwd-error">
        Error! Passwords do not match.
      </div>
      <button type="submit" class="btn btn-translucent btn-auth">Reset Password & Login</button>
    </form>
  </div>
</section>
