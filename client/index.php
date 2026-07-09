<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Heavoria - Taste Of Heaven</title>
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="./css/style.css">
</head>
<body class="customer-body-bg">

  <!-- Background Layer -->
  <div class="app-background"></div>
  <div class="app-overlay"></div>

  <!-- Gold Frame Border -->
  <div class="gold-frame"></div>

  <!-- MAIN CONTAINER (SPA Customer) -->
  <div id="app-container">
    <?php
    // Include SPA Customer screens / sections
    include 'components/welcome.php';
    include 'components/login.php';
    include 'components/register.php';
    include 'components/customer_dashboard.php';
    
    // Include customer modals
    include 'components/modals/cart_drawer.php';
    include 'components/modals/food_detail.php';
    include 'components/modals/preorder.php';
    include 'components/modals/payment.php';
    include 'components/modals/receipt.php';
    include 'components/modals/rating.php';
    
    // OTP & Forgot Password screens
    include 'components/modals/otp_register.php';
    include 'components/modals/otp_forgot.php';
    include 'components/modals/new_password.php';
    include 'components/modals/forgot_gmail.php';
    ?>
  </div>

  <!-- Toast Notification Simulation -->
  <div id="customer-toast-notif" class="toast-notification">
    <div class="toast-content">
      <span class="toast-icon">✓</span>
      <span class="toast-msg" id="customer-toast-msg">Sukses melakukan aksi!</span>
    </div>
  </div>

  <!-- Javascript -->
  <script src="js/app.js"></script>
</body>
</html>
