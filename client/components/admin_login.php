<!-- 2.0 ADMIN LOGIN SCREEN -->
<section id="admin-login-screen" class="screen-view active">
  <div class="logo-container">
    <div class="circular-logo logo-gold-border">
      <img src="assets/heavoria_logorev.jpeg" alt="Heavoria Logo" onerror="this.src='https://placehold.co/100x100/222/d4af37?text=H'">
    </div>
  </div>

  <div class="welcome-header login-header">
    <h1 class="welcome-title text-gold-accent">ADMIN PORTAL</h1>
    <h2 class="welcome-brand">HEAVORIA</h2>
    <div class="welcome-divider">
      <span class="diamond"></span>
    </div>
    <p class="welcome-subtitle">Taste Of Heaven Resto</p>
  </div>

  <div class="auth-box">
    <form id="form-admin-login" autocomplete="off">
      <div class="input-group">
        <input type="text" id="admin-login-identifier" placeholder="Gmail Address / Phone Number" required>
      </div>
      <div class="input-group password-group">
        <input type="password" id="admin-login-password" placeholder="Enter Password" required>
        <span class="toggle-password" data-target="admin-login-password">
          <svg class="eye-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
          </svg>
        </span>
      </div>
      
      <div class="error-message" id="admin-login-error">
        Error! Incorrect password or admin credentials.<br>Please enter the correct Gmail/Phone and Password.
      </div>

      <button type="submit" class="btn btn-translucent btn-auth">Login as Admin!</button>
    </form>
  </div>
</section>
