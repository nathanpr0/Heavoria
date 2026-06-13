<!-- 5. ADMIN DASHBOARD -->
<section id="admin-dashboard" class="screen-view dashboard-view">
  <header class="dashboard-header">
    <div class="header-brand">
      <div class="header-logo-circle logo-admin">A</div>
      <div>
        <h1 class="brand-text text-gold">HEAVORIA ADMIN</h1>
        <p class="brand-tagline">Management Portal</p>
      </div>
    </div>

    <nav class="admin-nav">
      <button class="nav-item active" data-tab="admin-summary">Ringkasan</button>
      <button class="nav-item" data-tab="admin-orders">Antrean Pesanan</button>
      <button class="nav-item" data-tab="admin-menu">Kelola Menu</button>
      <button class="nav-item" data-tab="admin-transactions">Riwayat Penjualan</button>
    </nav>

    <div class="header-actions">
      <div class="user-info">
        <span class="role-badge badge-admin">Administrator</span>
        <span class="username-display">Admin Resto</span>
      </div>
      <button class="btn-logout btn-icon-only" title="Logout">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
          <polyline points="16 17 21 12 16 7"></polyline>
          <line x1="21" y1="12" x2="9" y2="12"></line>
        </svg>
      </button>
    </div>
  </header>

  <main class="dashboard-body">
    <?php
    include 'tabs/admin_summary.php';
    include 'tabs/admin_orders.php';
    include 'tabs/admin_menu.php';
    include 'tabs/admin_transactions.php';
    ?>
  </main>
</section>
