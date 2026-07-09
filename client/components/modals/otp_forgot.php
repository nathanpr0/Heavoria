<!-- 4.1.2 FORGOT PASSWORD OTP VERIFICATION SCREEN -->
<section id="forgot-otp-screen" class="screen-view">
  <div class="logo-container">
    <div class="circular-logo">
      <img src="assets/heavoria_logorev.jpeg" alt="Heavoria Logo" onerror="this.src='https://placehold.co/100x100/422/fd3?text=H'">
    </div>
  </div>

  <div class="welcome-header login-header">
    <h1 class="welcome-title">VERIFICATION</h1>
    <h2 class="welcome-brand">CODE</h2>
    <div class="welcome-divider">
      <span class="diamond"></span>
    </div>
    <p class="welcome-subtitle" id="otp-forgot-subtitle">Simulasi kode OTP terkirim ke Gmail Anda</p>
  </div>

  <div class="auth-box">
    <div class="otp-simulate-banner">
      Simulasi Sistem: Kode verifikasi Anda adalah <strong class="text-gold">123456</strong>
    </div>
    <form id="form-forgot-otp" autocomplete="off">
      <div class="input-group">
        <input type="text" id="forgot-otp-code" placeholder="Enter Verification Code" required style="text-align: center; letter-spacing: 5px; font-weight: bold;">
      </div>
      <div class="error-message" id="forgot-otp-error">
        Error! Invalid verification code.
      </div>
      <button type="submit" class="btn btn-translucent btn-auth">Verify Code</button>
    </form>
  </div>
</section>
