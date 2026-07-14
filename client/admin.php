<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Heavoria - Admin Management Portal</title>
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="./css/style.css?v=20260714-sales-print-fix">
</head>
<body class="admin-body-bg">

  <!-- MAIN CONTAINER (SPA Admin) -->
  <div id="app-container">
    <?php
    // Include SPA Admin screens
    include 'components/admin_login.php';
    include 'components/admin_dashboard.php';
    
    // Include CRUD modal
    include 'components/modals/menu_crud.php';
    ?>
  </div>

  <!-- Admin Toast Notification -->
  <div id="admin-toast-notif" class="toast-notification">
    <div class="toast-content">
      <span class="toast-icon">✓</span>
      <span class="toast-msg" id="admin-toast-msg">Sukses melakukan aksi!</span>
    </div>
  </div>

  <!-- Javascript -->
  <script src="js/app.js?v=20260714-sales-complete"></script>
</body>
</html>
