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
<body>

  <!-- MAIN CONTAINER (SPA) -->
  <div id="app-container">
    <?php
    // Include SPA screens / sections
    include 'components/welcome.php';
    include 'components/login.php';
    include 'components/register.php';
    include 'components/customer_dashboard.php';
    include 'components/admin_dashboard.php';
    
    // Include modals
    include 'components/modals/cart_drawer.php';
    include 'components/modals/food_detail.php';
    include 'components/modals/payment.php';
    include 'components/modals/receipt.php';
    include 'components/modals/menu_crud.php';
    include 'components/modals/reset_password.php';
    ?>
  </div>

  <!-- Javascript -->
  <script src="js/app.js"></script>
</body>
</html>
