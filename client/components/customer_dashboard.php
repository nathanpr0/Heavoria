<!-- 4. CUSTOMER DASHBOARD -->
<section id="customer-dashboard" class="screen-view dashboard-view active">
  <header class="dashboard-header-new">
    <!-- Home Button -->
    <button id="btn-header-home" class="header-btn-new btn-icon-only-new" title="Home">
      <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
        <polyline points="9 22 9 12 15 12 15 22"></polyline>
      </svg>
    </button>

    <!-- Search Box -->
    <div class="header-search-box-new">
      <input type="text" id="header-search-input" placeholder="Search">
      <svg class="search-icon-new" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="11" cy="11" r="8"></circle>
        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
      </svg>
    </div>

    <!-- Navigation Tabs -->
    <nav class="header-nav-new">
      <button class="header-tab-new active" data-tab="catalog">Our Product</button>
      <button class="header-tab-new" data-tab="testimony">Testimoni</button>
      <button class="header-tab-new" data-tab="contact">Contact Us</button>
      <button class="header-tab-new" data-tab="about">About Us</button>
      <button class="header-tab-new" data-tab="order-history">Your Order</button>
    </nav>

    <!-- Cart Button -->
    <button id="btn-cart-toggle" class="header-btn-new cart-btn-new">
      <span>Cart</span>
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="9" cy="21" r="1"></circle>
        <circle cx="20" cy="21" r="1"></circle>
        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
      </svg>
      <span id="cart-badge-count" class="cart-badge-new">0</span>
    </button>

    <!-- Profile / Logout Button -->
    <button id="btn-header-profile" class="header-btn-new btn-logout btn-icon-only-new" title="Profile/Logout">
      <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
        <circle cx="12" cy="7" r="4"></circle>
      </svg>
      <span id="customer-name" class="header-username-display" style="display:none;">Guest</span>
    </button>
  </header>

  <main class="dashboard-body-new">
    <?php
    include 'tabs/catalog.php';
    include 'tabs/testimony.php';
    include 'tabs/contact.php';
    include 'tabs/about.php';
    include 'tabs/order_history.php';
    ?>
  </main>
</section>
