<!-- 5. ADMIN DASHBOARD -->
<section id="admin-dashboard" class="screen-view dashboard-view">
  <header class="dashboard-header">
    <div class="header-brand">
      <div>
        <h1 class="brand-text text-gold">◇ HEAVORIA ◇</h1>
        <p class="brand-tagline">ADMIN MANAGEMENT PORTAL</p>
      </div>
    </div>

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
    <section id="admin-menu-home" class="admin-menu-home" aria-labelledby="admin-menu-title">
      <div class="admin-welcome">
        <p class="admin-welcome-kicker">WELCOME TO</p>
        <h2>HEAVORIA</h2>
        <p>Selamat datang, Administrator</p>
      </div>

      <h3 id="admin-menu-title" class="admin-menu-title">ADMIN MENU</h3>
      <nav class="admin-menu-grid admin-nav" aria-label="Menu admin">
        <button type="button" class="nav-item admin-menu-card" data-tab="admin-summary">Statistik<br>Penjualan</button>
        <button type="button" class="nav-item admin-menu-card" data-tab="admin-orders">Verifikasi<br>Pesanan<br>Client</button>
        <button type="button" class="nav-item admin-menu-card" data-tab="admin-menu">Tambah/Hapus<br>Stok<br>Produk</button>
        <button type="button" class="nav-item admin-menu-card" data-tab="admin-confirmed-orders">Lihat<br>List<br>Pesanan</button>
        <button type="button" class="nav-item admin-menu-card" data-tab="admin-transactions">Cetak Laporan<br>Penjualan</button>
        <button type="button" class="nav-item admin-menu-card" data-tab="admin-clients">Lihat<br>List<br>Client</button>
      </nav>
    </section>

    <section id="admin-content-panel" class="admin-content-panel" aria-live="polite">
      <button type="button" id="btn-admin-menu-back" class="btn-admin-menu-back">← Kembali ke menu admin</button>
      <?php
      include 'tabs/admin_summary.php';
      include 'tabs/admin_orders.php';
      include 'tabs/admin_confirmed_orders.php';
      include 'tabs/admin_menu.php';
      include 'tabs/admin_transactions.php';
      include 'tabs/admin_clients.php';
      ?>
    </section>
  </main>
</section>
