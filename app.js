/**
 * ==========================================================================
 * HEAVORIA RESTORAN SYSTEM - APPLICATION LOGIC
 * Features: SPA Router, Authentication, Cart, Checkout, Admin CRUD, State Management
 * Data Store: LocalStorage & SessionStorage
 * ==========================================================================
 */

// 1. DUMMY / INITIAL SEED DATA
// 1. DUMMY / INITIAL SEED DATA
const INITIAL_MENU = [
  {
    id: "menu-1",
    name: "Nigiri",
    category: "sushi",
    price: 6000,
    originalPrice: 8000,
    desc: "Delicate sushi rice topped with fresh premium ingredients, offering a rich and satisfying taste in every bite",
    image: "assets/nigiri.jpeg"
  },
  {
    id: "menu-2",
    name: "Sushi Roll",
    category: "sushi",
    price: 5000,
    originalPrice: 7000,
    desc: "Deliciously rolled sushi filled with fresh premium ingredients for a flavorful and satisfying experience",
    image: "assets/sushiroll.jpeg"
  },
  {
    id: "menu-3",
    name: "Towel Cake Grape",
    category: "cake",
    price: 15000,
    originalPrice: 20000,
    desc: "Premium towel crepe cake layered with fresh juicy grapes and rich vanilla diplomat cream",
    image: "assets/grape.jpeg"
  },
  {
    id: "menu-4",
    name: "Towel Cake Strawberry",
    category: "cake",
    price: 12000,
    originalPrice: 16000,
    desc: "Soft towel crepe cake rolled with fresh sweet strawberries and light whipped cream",
    image: "assets/strawberry.jpeg"
  },
  {
    id: "menu-5",
    name: "Towel Cake Mango",
    category: "cake",
    price: 10000,
    originalPrice: 14000,
    desc: "Exotic towel crepe cake featuring ripe golden mangoes and velvety sweet mango cream",
    image: "assets/towelcakemango.jpeg"
  }
];

const INITIAL_USERS = [
  { username: "admin", phone: "08123456789", password: "admin", role: "admin" },
  { username: "customer", phone: "08987654321", password: "customer", role: "user" }
];

// 2. STATE MANAGEMENT (LOCAL STORAGE WRAPPERS)
const getMenu = () => {
  const menu = localStorage.getItem("heavoria_menu");
  // Force reset seed database if it contains old mockups data
  if (!menu || menu.includes("Royal Chocolate Roll Cake")) {
    localStorage.setItem("heavoria_menu", JSON.stringify(INITIAL_MENU));
    localStorage.removeItem("heavoria_orders"); // Clean up old orders referring to stale items
    return INITIAL_MENU;
  }
  return JSON.parse(menu);
};

const saveMenu = (menuList) => {
  localStorage.setItem("heavoria_menu", JSON.stringify(menuList));
};

const getUsers = () => {
  const users = localStorage.getItem("heavoria_users");
  if (!users) {
    localStorage.setItem("heavoria_users", JSON.stringify(INITIAL_USERS));
    return INITIAL_USERS;
  }
  return JSON.parse(users);
};

const saveUser = (userObj) => {
  const users = getUsers();
  users.push(userObj);
  localStorage.setItem("heavoria_users", JSON.stringify(users));
};

const getOrders = () => {
  const orders = localStorage.getItem("heavoria_orders");
  return orders ? JSON.parse(orders) : [];
};

const saveOrders = (ordersList) => {
  localStorage.setItem("heavoria_orders", JSON.stringify(ordersList));
};

// Current Session Info
let currentUser = JSON.parse(sessionStorage.getItem("heavoria_current_user")) || null;
let activeCart = [];
let selectedFoodForModal = null;
let currentQtyInModal = 1;
let shouldOpenCartAfterLogin = false;
let pendingCartItem = null;

// 3. UI HELPER FUNCTIONS
const formatRupiah = (number) => {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0
  }).format(number);
};

// Toggle Screen View (Router SPA)
const navigateTo = (screenId) => {
  document.querySelectorAll(".screen-view").forEach(screen => {
    screen.classList.remove("active");
  });
  const targetScreen = document.getElementById(screenId);
  if (targetScreen) {
    targetScreen.classList.add("active");
  }

  // Khusus jika berpindah ke dashboard
  if (screenId === "customer-dashboard") {
    document.getElementById("customer-name").innerText = currentUser ? currentUser.username : "Guest";
    
    // Update logout/login button title
    document.querySelectorAll(".btn-logout").forEach(btn => {
      btn.title = currentUser ? "Logout" : "Login / Sign In";
    });

    // Reset to Lobby categories screen view by default
    const lobby = document.getElementById("catalog-lobby");
    const products = document.getElementById("catalog-products");
    if (lobby && products) {
      lobby.classList.add("active");
      products.classList.remove("active");
    }

    // Reset header tab styles
    document.querySelectorAll(".header-tab-new").forEach(t => t.classList.remove("active"));
    const firstTab = document.querySelector('.header-tab-new[data-tab="catalog"]');
    if (firstTab) firstTab.classList.add("active");

    // Reset active tab panes
    document.querySelectorAll("#customer-dashboard .tab-pane").forEach(pane => pane.classList.remove("active"));
    const catalogPane = document.getElementById("tab-catalog");
    if (catalogPane) catalogPane.classList.add("active");

    renderCustomerCatalog();
    renderCustomerOrders();
  } else if (screenId === "admin-dashboard") {
    updateAdminStats();
    renderAdminOrders();
    renderAdminMenuTable();
    renderAdminTransactions();
  }
};

// 4. AUTHENTICATION CONTROLLER
const handleLogin = (username, password) => {
  const users = getUsers();
  const foundUser = users.find(u => u.username.toLowerCase() === username.toLowerCase() && u.password === password);
  
  const errorEl = document.getElementById("login-error");
  const pwdInput = document.getElementById("login-password");
  
  if (foundUser) {
    errorEl.style.display = "none";
    pwdInput.classList.remove("input-error-field");
    currentUser = foundUser;
    sessionStorage.setItem("heavoria_current_user", JSON.stringify(currentUser));
    
    // Redirect based on role
    if (foundUser.role === "admin") {
      navigateTo("admin-dashboard");
    } else {
      navigateTo("customer-dashboard");
      if (pendingCartItem) {
        const itemToAdd = pendingCartItem;
        pendingCartItem = null;
        addToCart(itemToAdd.item, itemToAdd.qty, itemToAdd.notes);
        setTimeout(() => {
          renderCartDrawer();
          document.getElementById("cart-drawer").classList.add("active");
        }, 100);
      } else if (shouldOpenCartAfterLogin) {
        shouldOpenCartAfterLogin = false;
        setTimeout(() => {
          renderCartDrawer();
          document.getElementById("cart-drawer").classList.add("active");
        }, 100);
      }
    }
    document.getElementById("form-login").reset();
  } else {
    // Show incorrect error state matching mockup
    errorEl.style.display = "block";
    pwdInput.classList.add("input-error-field");
  }
};

const handleRegister = (username, phone, password, confirmPassword) => {
  const errorEl = document.getElementById("reg-error");
  const pwdInput = document.getElementById("reg-password");
  const confInput = document.getElementById("reg-confirm-password");
  
  // Validation password match
  if (password !== confirmPassword) {
    errorEl.style.display = "block";
    pwdInput.classList.add("input-error-field");
    confInput.classList.add("input-error-field");
    return;
  }

  errorEl.style.display = "none";
  pwdInput.classList.remove("input-error-field");
  confInput.classList.remove("input-error-field");
  const users = getUsers();
  
  // Check if username already exists
  if (users.some(u => u.username.toLowerCase() === username.toLowerCase())) {
    errorEl.innerText = "Error! Username sudah terdaftar.";
    errorEl.style.display = "block";
    return;
  }

  const newUser = { username, phone, password, role: "user" };
  saveUser(newUser);

  // Auto login
  currentUser = newUser;
  sessionStorage.setItem("heavoria_current_user", JSON.stringify(currentUser));
  navigateTo("customer-dashboard");
  if (pendingCartItem) {
    const itemToAdd = pendingCartItem;
    pendingCartItem = null;
    addToCart(itemToAdd.item, itemToAdd.qty, itemToAdd.notes);
    setTimeout(() => {
      renderCartDrawer();
      document.getElementById("cart-drawer").classList.add("active");
    }, 100);
  } else if (shouldOpenCartAfterLogin) {
    shouldOpenCartAfterLogin = false;
    setTimeout(() => {
      renderCartDrawer();
      document.getElementById("cart-drawer").classList.add("active");
    }, 100);
  }
  document.getElementById("form-register").reset();
};

const handleLogout = () => {
  currentUser = null;
  sessionStorage.removeItem("heavoria_current_user");
  activeCart = [];
  pendingCartItem = null;
  updateCartBadge();
  navigateTo("customer-dashboard");
};

// Handle Reset Password (via username + phone verification)
const handleResetPassword = (username, phone, newPassword, confirmPassword) => {
  const errorEl = document.getElementById("reset-modal-error");
  const newPwdInput = document.getElementById("reset-new-password");
  const confPwdInput = document.getElementById("reset-confirm-password");

  // Validation: password match
  if (newPassword !== confirmPassword) {
    errorEl.innerText = "Error! Password baru dan konfirmasi password tidak cocok.";
    errorEl.style.display = "block";
    newPwdInput.classList.add("input-error-field");
    confPwdInput.classList.add("input-error-field");
    return;
  }

  const users = getUsers();
  const userIndex = users.findIndex(
    u => u.username.toLowerCase() === username.toLowerCase() && u.phone === phone
  );

  if (userIndex === -1) {
    errorEl.innerText = "Error! Username atau nomor telepon tidak ditemukan.";
    errorEl.style.display = "block";
    newPwdInput.classList.remove("input-error-field");
    confPwdInput.classList.remove("input-error-field");
    return;
  }

  // Update password
  users[userIndex].password = newPassword;
  localStorage.setItem("heavoria_users", JSON.stringify(users));

  // Close modal and show success feedback
  document.getElementById("reset-password-modal").classList.remove("active");
  document.getElementById("form-reset-password").reset();
  errorEl.style.display = "none";
  newPwdInput.classList.remove("input-error-field");
  confPwdInput.classList.remove("input-error-field");

  // Flash a success notice on the login page
  const loginError = document.getElementById("login-error");
  loginError.style.display = "block";
  loginError.style.background = "rgba(46, 204, 113, 0.15)";
  loginError.style.borderColor = "#2ecc71";
  loginError.style.color = "#afffca";
  loginError.innerHTML = "✓ Password berhasil direset! Silakan login dengan password baru Anda.";

  // Auto-revert success notice style after 5 seconds
  setTimeout(() => {
    loginError.style.display = "none";
    loginError.style.background = "";
    loginError.style.borderColor = "";
    loginError.style.color = "";
    loginError.innerHTML = "Error! incorrect password or username.<br>Please enter the correct name and username.";
  }, 5000);
};

// 5. CUSTOMER PAGE CONTROLLERS (CATALOG & CART)
let activeCategoryFilter = "all";
let searchFilterQuery = "";

const renderCustomerCatalog = () => {
  const menuContainer = document.getElementById("menu-items-container");
  const menuList = getMenu();
  menuContainer.innerHTML = "";

  const filteredMenu = menuList.filter(item => {
    const matchCategory = activeCategoryFilter === "all" || item.category === activeCategoryFilter;
    const matchSearch = item.name.toLowerCase().includes(searchFilterQuery.toLowerCase()) || 
                        item.desc.toLowerCase().includes(searchFilterQuery.toLowerCase());
    return matchCategory && matchSearch;
  });

  if (filteredMenu.length === 0 && activeCategoryFilter !== "drink") {
    menuContainer.innerHTML = `<div class="cart-empty-text" style="grid-column: 1/-1; font-size: 1.1rem; padding: 50px;">Maaf, menu tidak ditemukan.</div>`;
    return;
  }

  filteredMenu.forEach(item => {
    const card = document.createElement("div");
    card.className = "menu-card";
    
    const priceHtml = item.originalPrice 
      ? `<div style="display:flex; align-items:center;"><span class="original-price-cross">${formatRupiah(item.originalPrice)}</span><span class="sale-price-premium">Only ${item.price/1000}K!</span></div>`
      : `<span class="menu-card-price text-gold">${formatRupiah(item.price)}</span>`;

    card.innerHTML = `
      <div class="menu-card-img-box">
        <img src="${item.image}" alt="${item.name}" onerror="this.src='https://placehold.co/300x200/422/fd3?text=${encodeURIComponent(item.name)}'">
        <span class="menu-card-tag">${item.category}</span>
      </div>
      <div class="menu-card-body">
        <h3 style="font-family: var(--font-serif); font-style: italic;">${item.name}</h3>
        <p class="menu-card-desc">${item.desc}</p>
        <div class="menu-card-footer">
          ${priceHtml}
          <button class="btn-add-mini" data-id="${item.id}">+</button>
        </div>
      </div>
    `;

    // Click card to open modal detail
    card.addEventListener("click", (e) => {
      if (e.target.classList.contains("btn-add-mini")) return;
      openFoodDetailModal(item);
    });

    // Add fast order via "+"
    card.querySelector(".btn-add-mini").addEventListener("click", () => {
      addToCart(item, 1, "");
    });

    menuContainer.appendChild(card);
  });

  // Append Coming Soon Card for active categories sushi, cake, drink
  if (activeCategoryFilter === "sushi" || activeCategoryFilter === "cake" || activeCategoryFilter === "drink") {
    const comingSoonCard = document.createElement("div");
    comingSoonCard.className = "coming-soon-card-new";
    comingSoonCard.innerHTML = `<h3>Coming Soon !</h3>`;
    menuContainer.appendChild(comingSoonCard);
  }
};

const openFoodDetailModal = (item) => {
  selectedFoodForModal = item;
  currentQtyInModal = 1;
  
  document.getElementById("modal-food-img").src = item.image;
  document.getElementById("modal-food-img").onerror = function() {
    this.src = `https://placehold.co/300x200/422/fd3?text=${encodeURIComponent(item.name)}`;
  };
  document.getElementById("modal-food-category").innerText = item.category;
  document.getElementById("modal-food-title").innerText = item.name;
  document.getElementById("modal-food-desc").innerText = item.desc;
  document.getElementById("modal-food-price").innerText = formatRupiah(item.price);
  document.getElementById("modal-food-notes").value = "";
  document.getElementById("modal-qty-display").innerText = currentQtyInModal;

  document.getElementById("food-detail-modal").classList.add("active");
};

const updateCartBadge = () => {
  const totalItems = activeCart.reduce((total, item) => total + item.qty, 0);
  document.getElementById("cart-badge-count").innerText = totalItems;
};

const addToCart = (item, qty, notes) => {
  if (!currentUser) {
    pendingCartItem = { item, qty, notes };
    
    const loginError = document.getElementById("login-error");
    loginError.style.display = "block";
    loginError.style.background = "rgba(212, 175, 55, 0.15)";
    loginError.style.borderColor = "var(--color-primary-gold)";
    loginError.style.color = "var(--color-primary-gold)";
    loginError.innerHTML = "Silakan Login atau Sign In terlebih dahulu untuk menambahkan hidangan ke keranjang belanja Anda.";
    
    navigateTo("login-screen");
    return;
  }

  const existingItemIndex = activeCart.findIndex(cartItem => cartItem.id === item.id && cartItem.notes === notes);
  
  if (existingItemIndex > -1) {
    activeCart[existingItemIndex].qty += qty;
  } else {
    activeCart.push({
      id: item.id,
      name: item.name,
      price: item.price,
      image: item.image,
      qty: qty,
      notes: notes
    });
  }

  updateCartBadge();
  renderCartDrawer();
  
  // Notification micro-animation
  const cartBtn = document.getElementById("btn-cart-toggle");
  cartBtn.style.transform = "scale(1.2)";
  setTimeout(() => {
    cartBtn.style.transform = "";
  }, 200);
};

const renderCartDrawer = () => {
  const cartContainer = document.getElementById("cart-items-container");
  cartContainer.innerHTML = "";

  if (activeCart.length === 0) {
    cartContainer.innerHTML = `<p class="cart-empty-text">Keranjang Anda masih kosong.</p>`;
    updateCartTotals(0);
    return;
  }

  let subtotal = 0;

  activeCart.forEach((item, index) => {
    const itemTotal = item.price * item.qty;
    subtotal += itemTotal;

    const row = document.createElement("div");
    row.className = "cart-item";
    row.innerHTML = `
      <img src="${item.image}" alt="${item.name}" class="cart-item-img" onerror="this.src='https://placehold.co/100x100/422/fd3?text=Food'">
      <div class="cart-item-detail">
        <h4>${item.name}</h4>
        ${item.notes ? `<p class="cart-item-notes">Note: "${item.notes}"</p>` : ""}
        <span class="cart-item-price text-gold">${formatRupiah(item.price)}</span>
      </div>
      <div class="cart-item-actions">
        <button class="btn-remove-cart" data-index="${index}">&times; Hapus</button>
        <div class="cart-qty-control">
          <button class="cart-qty-btn btn-cart-minus" data-index="${index}">-</button>
          <span class="cart-qty-val">${item.qty}</span>
          <button class="cart-qty-btn btn-cart-plus" data-index="${index}">+</button>
        </div>
      </div>
    `;

    // Cart Actions listeners
    row.querySelector(".btn-remove-cart").addEventListener("click", () => {
      activeCart.splice(index, 1);
      updateCartBadge();
      renderCartDrawer();
    });

    row.querySelector(".btn-cart-minus").addEventListener("click", () => {
      if (item.qty > 1) {
        item.qty--;
      } else {
        activeCart.splice(index, 1);
      }
      updateCartBadge();
      renderCartDrawer();
    });

    row.querySelector(".btn-cart-plus").addEventListener("click", () => {
      item.qty++;
      updateCartBadge();
      renderCartDrawer();
    });

    cartContainer.appendChild(row);
  });

  updateCartTotals(subtotal);
};

const updateCartTotals = (subtotal) => {
  const tax = subtotal * 0.1;
  const service = subtotal * 0.05;
  const total = subtotal + tax + service;

  document.getElementById("cart-subtotal").innerText = formatRupiah(subtotal);
  document.getElementById("cart-tax").innerText = formatRupiah(tax);
  document.getElementById("cart-service").innerText = formatRupiah(service);
  document.getElementById("cart-total").innerText = formatRupiah(total);
};

// Order Flow & Payment
const handleCheckout = () => {
  const tableInput = document.getElementById("cart-table-number");
  if (!tableInput.value) {
    alert("Silakan masukkan Nomor Meja terlebih dahulu!");
    tableInput.focus();
    return;
  }

  if (activeCart.length === 0) {
    alert("Keranjang belanja kosong!");
    return;
  }

  // Calculate totals
  const subtotal = activeCart.reduce((acc, item) => acc + (item.price * item.qty), 0);
  const tax = subtotal * 0.1;
  const service = subtotal * 0.05;
  const grandTotal = subtotal + tax + service;

  // Set cash notice inside modal
  document.getElementById("cash-payment-amount").innerText = formatRupiah(grandTotal);

  // Close cart drawer & open payment modal
  document.getElementById("cart-drawer").classList.remove("active");
  document.getElementById("payment-modal").classList.add("active");
};

const processPayment = () => {
  const tableNum = document.getElementById("cart-table-number").value;
  const paymentMethod = document.querySelector('input[name="payment-method"]:checked').value;

  const subtotal = activeCart.reduce((acc, item) => acc + (item.price * item.qty), 0);
  const tax = subtotal * 0.1;
  const service = subtotal * 0.05;
  const total = subtotal + tax + service;

  const orderId = "ORD-" + Date.now().toString().slice(-6);
  const newOrder = {
    id: orderId,
    username: currentUser ? currentUser.username : "Guest",
    table: tableNum,
    items: [...activeCart],
    subtotal: subtotal,
    tax: tax,
    service: service,
    total: total,
    method: paymentMethod,
    status: "Pending", // Pending -> Diproses -> Selesai
    date: new Date().toISOString()
  };

  // Save to LocalStorage
  const orders = getOrders();
  orders.push(newOrder);
  saveOrders(orders);

  // Close payment modal
  document.getElementById("payment-modal").classList.remove("active");

  // Show digital receipt modal
  showDigitalReceipt(newOrder);

  // Clear Cart
  activeCart = [];
  updateCartBadge();
  renderCartDrawer();
  document.getElementById("cart-table-number").value = "";
};

const showDigitalReceipt = (order) => {
  document.getElementById("rec-order-id").innerText = order.id;
  document.getElementById("rec-date").innerText = new Date(order.date).toLocaleString("id-ID");
  document.getElementById("rec-table").innerText = order.table;
  document.getElementById("rec-method").innerText = order.method;

  const itemsContainer = document.getElementById("rec-items-container");
  itemsContainer.innerHTML = "";

  order.items.forEach(item => {
    const itemRow = document.createElement("div");
    itemRow.className = "rec-item";
    itemRow.innerHTML = `
      <div class="rec-item-title-row">
        <span>${item.name} (x${item.qty})</span>
        <span>${formatRupiah(item.price * item.qty)}</span>
      </div>
      ${item.notes ? `<div class="rec-item-notes">Note: "${item.notes}"</div>` : ""}
    `;
    itemsContainer.appendChild(itemRow);
  });

  document.getElementById("rec-subtotal").innerText = formatRupiah(order.subtotal);
  document.getElementById("rec-tax").innerText = formatRupiah(order.tax);
  document.getElementById("rec-service").innerText = formatRupiah(order.service);
  document.getElementById("rec-total").innerText = formatRupiah(order.total);

  document.getElementById("receipt-modal").classList.add("active");
};

// Customer Orders Status list
const renderCustomerOrders = () => {
  const container = document.getElementById("customer-orders-container");
  container.innerHTML = "";

  const orders = getOrders();
  const myOrders = orders.filter(o => o.username === (currentUser ? currentUser.username : "Guest"));

  if (myOrders.length === 0) {
    container.innerHTML = `<div class="cart-empty-text" style="font-size: 1.1rem; padding: 50px;">Anda belum melakukan pesanan apa pun.</div>`;
    return;
  }

  // Show latest first
  myOrders.reverse().forEach(order => {
    const card = document.createElement("div");
    card.className = "order-card";
    
    let statusClass = "status-pending";
    let statusLabel = "Menunggu Antrean (Pending)";
    if (order.status === "Diproses") {
      statusClass = "status-cooking";
      statusLabel = "Sedang Dimasak (Diproses)";
    } else if (order.status === "Selesai") {
      statusClass = "status-completed";
      statusLabel = "Selesai Disajikan";
    }

    const itemsSummaryList = order.items.map(it => `${it.name} (x${it.qty})`).join(", ");

    card.innerHTML = `
      <div class="order-card-header">
        <div>
          <span class="order-id-label">${order.id}</span>
          <span class="order-time-label"> - Meja ${order.table} | ${new Date(order.date).toLocaleTimeString("id-ID")}</span>
        </div>
        <span class="status-badge ${statusClass}">${statusLabel}</span>
      </div>
      <div class="order-card-body">
        <div class="order-items-summary">
          <p><strong>Pesanan:</strong> ${itemsSummaryList}</p>
          <p><strong>Metode Bayar:</strong> ${order.method}</p>
          <button class="btn-view-receipt" data-id="${order.id}">Lihat Struk</button>
        </div>
        <div class="order-total-price text-gold">
          <span style="font-size: 0.75rem; color:#aaa; font-weight: normal; display:block;">Total Transaksi:</span>
          ${formatRupiah(order.total)}
        </div>
      </div>
    `;

    card.querySelector(".btn-view-receipt").addEventListener("click", () => {
      showDigitalReceipt(order);
    });

    container.appendChild(card);
  });
};

const selectCategory = (category) => {
  activeCategoryFilter = category;
  
  const titleHeader = document.getElementById("category-title-header");
  if (category === "sushi") titleHeader.innerText = "❖ JAPANESE MENU ❖";
  else if (category === "cake") titleHeader.innerText = "❖ TOWEL CAKES ❖";
  else if (category === "drink") titleHeader.innerText = "❖ ELIXIR DRINK ❖";
  
  document.getElementById("catalog-lobby").classList.remove("active");
  document.getElementById("catalog-products").classList.add("active");
  
  renderCustomerCatalog();
};

// 6. ADMIN DASHBOARD CONTROLLERS
const updateAdminStats = () => {
  const orders = getOrders();
  const completedOrders = orders.filter(o => o.status === "Selesai");

  // Calc Total Revenue
  const totalRevenue = completedOrders.reduce((sum, o) => sum + o.total, 0);
  document.getElementById("stat-total-revenue").innerText = formatRupiah(totalRevenue);

  // Calc Total Orders
  document.getElementById("stat-total-orders").innerText = orders.length;

  // Calc Popular Menu Item
  const itemCounts = {};
  completedOrders.forEach(o => {
    o.items.forEach(it => {
      itemCounts[it.name] = (itemCounts[it.name] || 0) + it.qty;
    });
  });

  let popularItem = "-";
  let maxCount = 0;
  for (const name in itemCounts) {
    if (itemCounts[name] > maxCount) {
      maxCount = itemCounts[name];
      popularItem = `${name} (${maxCount} porsi)`;
    }
  }
  document.getElementById("stat-popular-item").innerText = popularItem;

  // Render Category performance bar chart using purely CSS variables
  renderAdminCategoryChart(completedOrders);
};

const renderAdminCategoryChart = (completedOrders) => {
  const chartContainer = document.getElementById("category-chart");
  chartContainer.innerHTML = "";

  const categoryTotals = { sushi: 0, cake: 0, drink: 0 };
  
  completedOrders.forEach(o => {
    o.items.forEach(it => {
      // Find category of this item from current menu state
      const menuList = getMenu();
      const menuItem = menuList.find(m => m.id === it.id);
      const category = menuItem ? menuItem.category : "sushi";
      if (categoryTotals[category] !== undefined) {
        categoryTotals[category] += (it.price * it.qty);
      }
    });
  });

  const maxVal = Math.max(categoryTotals.sushi, categoryTotals.cake, categoryTotals.drink, 1);

  const categories = [
    { key: "sushi", label: "Sushi & Roll" },
    { key: "cake", label: "Kue & Desserts" },
    { key: "drink", label: "Minuman Mewah" }
  ];

  categories.forEach(cat => {
    const salesVal = categoryTotals[cat.key];
    const pct = (salesVal / maxVal) * 150; // max height is 150px

    const barWrapper = document.createElement("div");
    barWrapper.className = "chart-bar-wrapper";
    barWrapper.innerHTML = `
      <span class="chart-bar-val">${formatRupiah(salesVal)}</span>
      <div class="chart-bar" style="height: ${Math.max(pct, 5)}px"></div>
      <span class="chart-bar-label">${cat.label}</span>
    `;
    chartContainer.appendChild(barWrapper);
  });
};

let activeAdminStatusFilter = "all";

const renderAdminOrders = () => {
  const container = document.getElementById("admin-orders-container");
  container.innerHTML = "";

  const orders = getOrders();
  
  const filteredOrders = orders.filter(o => {
    if (activeAdminStatusFilter === "all") return o.status !== "Selesai";
    return o.status === activeAdminStatusFilter;
  });

  if (filteredOrders.length === 0) {
    container.innerHTML = `<div class="cart-empty-text" style="font-size: 1.1rem; padding: 40px;">Tidak ada antrean pesanan aktif saat ini.</div>`;
    return;
  }

  // Show oldest first for kitchen queuing
  filteredOrders.forEach(order => {
    const row = document.createElement("div");
    row.className = "admin-order-row";

    let actionBtnHtml = "";
    if (order.status === "Pending") {
      actionBtnHtml = `<button class="btn-action-status btn-action-cook" data-id="${order.id}">Mulai Masak</button>`;
    } else if (order.status === "Diproses") {
      actionBtnHtml = `<button class="btn-action-status btn-action-complete" data-id="${order.id}">Sajikan / Selesai</button>`;
    }

    const itemsText = order.items.map(it => `${it.name} [x${it.qty}] ${it.notes ? `(Note: "${it.notes}")` : ""}`).join(", ");

    row.innerHTML = `
      <div class="order-meta-info">
        <span class="order-id-label">${order.id} | Meja ${order.table}</span>
        <span class="order-time-label">Waktu: ${new Date(order.date).toLocaleTimeString("id-ID")} | Metode: ${order.method}</span>
        <p class="order-items-detail-text"><strong>Rincian:</strong> ${itemsText}</p>
      </div>
      <div class="order-row-actions">
        <span class="status-badge ${order.status === "Pending" ? "status-pending" : "status-cooking"}">${order.status}</span>
        ${actionBtnHtml}
      </div>
    `;

    // Action button listeners
    const actionBtn = row.querySelector(".btn-action-status");
    if (actionBtn) {
      actionBtn.addEventListener("click", () => {
        updateOrderStatus(order.id);
      });
    }

    container.appendChild(row);
  });
};

const updateOrderStatus = (orderId) => {
  const orders = getOrders();
  const orderIndex = orders.findIndex(o => o.id === orderId);

  if (orderIndex > -1) {
    const currentStatus = orders[orderIndex].status;
    if (currentStatus === "Pending") {
      orders[orderIndex].status = "Diproses";
    } else if (currentStatus === "Diproses") {
      orders[orderIndex].status = "Selesai";
    }

    saveOrders(orders);
    
    // Refresh admin dashboards
    updateAdminStats();
    renderAdminOrders();
    renderAdminTransactions();
  }
};

// Admin Menu CRUD Table
const renderAdminMenuTable = () => {
  const tbody = document.getElementById("admin-menu-table-body");
  tbody.innerHTML = "";

  const menuList = getMenu();

  menuList.forEach(item => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td><img src="${item.image}" alt="${item.name}" class="table-img" onerror="this.src='https://placehold.co/100x70/422/fd3?text=Heavoria'"></td>
      <td><strong>${item.name}</strong></td>
      <td><span style="text-transform:uppercase; font-size:0.75rem; color:#d4af37">${item.category}</span></td>
      <td><span class="price-tag text-gold">${formatRupiah(item.price)}</span></td>
      <td style="max-width:300px; font-size:0.8rem; color:#aaa;">${item.desc}</td>
      <td>
        <div class="table-actions">
          <button class="btn-table-edit" data-id="${item.id}">Edit</button>
          <button class="btn-table-delete" data-id="${item.id}">Hapus</button>
        </div>
      </td>
    `;

    tr.querySelector(".btn-table-edit").addEventListener("click", () => {
      openMenuCrudModal(item);
    });

    tr.querySelector(".btn-table-delete").addEventListener("click", () => {
      if (confirm(`Apakah Anda yakin ingin menghapus menu "${item.name}"?`)) {
        deleteMenuItem(item.id);
      }
    });

    tbody.appendChild(tr);
  });
};

const openMenuCrudModal = (item = null) => {
  const modal = document.getElementById("menu-crud-modal");
  const titleEl = document.getElementById("crud-modal-title");
  
  if (item) {
    // Edit Mode
    titleEl.innerText = "Edit Menu Restoran";
    document.getElementById("crud-item-id").value = item.id;
    document.getElementById("crud-item-name").value = item.name;
    document.getElementById("crud-item-category").value = item.category;
    document.getElementById("crud-item-price").value = item.price;
    document.getElementById("crud-item-desc").value = item.desc;
    document.getElementById("crud-item-image").value = item.image;
  } else {
    // Add Mode
    titleEl.innerText = "Tambah Menu Baru";
    document.getElementById("form-menu-crud").reset();
    document.getElementById("crud-item-id").value = "";
  }

  modal.classList.add("active");
};

const saveMenuItem = (formData) => {
  const menuList = getMenu();
  
  if (formData.id) {
    // Update Mode
    const index = menuList.findIndex(m => m.id === formData.id);
    if (index > -1) {
      menuList[index] = { ...menuList[index], ...formData };
    }
  } else {
    // Insert Mode
    const newId = "menu-" + Date.now();
    menuList.push({
      id: newId,
      ...formData
    });
  }

  saveMenu(menuList);
  document.getElementById("menu-crud-modal").classList.remove("active");
  renderAdminMenuTable();
  renderCustomerCatalog(); // Sync user view as well
};

const deleteMenuItem = (itemId) => {
  let menuList = getMenu();
  menuList = menuList.filter(m => m.id !== itemId);
  saveMenu(menuList);
  renderAdminMenuTable();
  renderCustomerCatalog(); // Sync user view
};

// Admin Completed Transactions log
const renderAdminTransactions = () => {
  const tbody = document.getElementById("admin-transactions-table-body");
  tbody.innerHTML = "";

  const orders = getOrders();
  const completed = orders.filter(o => o.status === "Selesai");

  if (completed.length === 0) {
    tbody.innerHTML = `<tr><td colspan="6" class="cart-empty-text" style="text-align:center;">Belum ada riwayat transaksi selesai.</td></tr>`;
    return;
  }

  // Show latest first
  completed.reverse().forEach(order => {
    const tr = document.createElement("tr");
    const itemNames = order.items.map(it => `${it.name} (x${it.qty})`).join(", ");
    
    tr.innerHTML = `
      <td><span class="order-id-label">${order.id}</span></td>
      <td style="font-size:0.8rem; color:#aaa;">${new Date(order.date).toLocaleString("id-ID")}</td>
      <td><strong>Meja ${order.table}</strong></td>
      <td style="font-size:0.85rem; max-width: 300px;">${itemNames}</td>
      <td><span class="status-badge status-completed">${order.method}</span></td>
      <td><strong class="text-gold">${formatRupiah(order.total)}</strong></td>
    `;
    tbody.appendChild(tr);
  });
};

// ==========================================================================
// 7. INITIALIZE DOM & EVENT LISTENERS
// ==========================================================================
document.addEventListener("DOMContentLoaded", () => {
  // Pre-seed Storage
  getMenu();
  getUsers();

  // If user session is already logged in, navigate
  if (currentUser && currentUser.role === "admin") {
    navigateTo("admin-dashboard");
  } else {
    // Default to customer dashboard for Guests and regular Users on startup
    navigateTo("customer-dashboard");
  }

  // 7.1 Router Navigation Listeners
  document.getElementById("btn-goto-login").addEventListener("click", () => {
    document.getElementById("login-error").style.display = "none";
    navigateTo("login-screen");
  });

  document.getElementById("btn-goto-register").addEventListener("click", () => {
    document.getElementById("reg-error").style.display = "none";
    navigateTo("register-screen");
  });

  document.querySelectorAll(".btn-back-welcome").forEach(btn => {
    btn.addEventListener("click", () => {
      navigateTo("customer-dashboard");
    });
  });

  // Logout / Login Listeners
  document.querySelectorAll(".btn-logout").forEach(btn => {
    btn.addEventListener("click", () => {
      if (currentUser) {
        handleLogout();
      } else {
        document.getElementById("login-error").style.display = "none";
        navigateTo("login-screen");
      }
    });
  });

  // Navigation links inside auth screens
  document.getElementById("link-goto-register-from-login").addEventListener("click", (e) => {
    e.preventDefault();
    document.getElementById("reg-error").style.display = "none";
    navigateTo("register-screen");
  });

  document.getElementById("link-goto-login-from-reg").addEventListener("click", (e) => {
    e.preventDefault();
    document.getElementById("login-error").style.display = "none";
    navigateTo("login-screen");
  });

  // Clear login error state on typing
  const loginUser = document.getElementById("login-username");
  const loginPass = document.getElementById("login-password");
  const loginError = document.getElementById("login-error");

  const clearLoginError = () => {
    loginError.style.display = "none";
    loginPass.classList.remove("input-error-field");
  };

  loginUser.addEventListener("input", clearLoginError);
  loginPass.addEventListener("input", clearLoginError);

  // Clear register error state on typing
  const regUser = document.getElementById("reg-username");
  const regPhone = document.getElementById("reg-phone");
  const regPass = document.getElementById("reg-password");
  const regConf = document.getElementById("reg-confirm-password");
  const regError = document.getElementById("reg-error");

  const clearRegisterError = () => {
    regError.style.display = "none";
    regPass.classList.remove("input-error-field");
    regConf.classList.remove("input-error-field");
  };

  regUser.addEventListener("input", clearRegisterError);
  regPhone.addEventListener("input", clearRegisterError);
  regPass.addEventListener("input", clearRegisterError);
  regConf.addEventListener("input", clearRegisterError);

  // 7.2 Auth Form Submissions
  document.getElementById("form-login").addEventListener("submit", (e) => {
    e.preventDefault();
    const user = loginUser.value;
    const pass = loginPass.value;
    handleLogin(user, pass);
  });

  document.getElementById("form-register").addEventListener("submit", (e) => {
    e.preventDefault();
    const user = regUser.value;
    const phone = regPhone.value;
    const pass = regPass.value;
    const conf = regConf.value;
    handleRegister(user, phone, pass, conf);
  });

  // Eye Toggle Password Visibility (matches mockup spec)
  document.querySelectorAll(".toggle-password").forEach(toggle => {
    toggle.addEventListener("click", () => {
      const targetId = toggle.getAttribute("data-target");
      const pwdInput = document.getElementById(targetId);
      
      if (pwdInput.type === "password") {
        pwdInput.type = "text";
        // Update eye icon SVG slightly (change stroke opacity or add crossline)
        toggle.style.color = "var(--color-primary-gold)";
      } else {
        pwdInput.type = "password";
        toggle.style.color = "";
      }
    });
  });

  // 7.3 Customer Navigation (Tabs) - NEW MOCKUP
  document.querySelectorAll(".header-tab-new").forEach(item => {
    item.addEventListener("click", () => {
      document.querySelectorAll(".header-tab-new").forEach(nav => nav.classList.remove("active"));
      item.classList.add("active");

      const tabId = item.getAttribute("data-tab");
      document.querySelectorAll("#customer-dashboard .tab-pane").forEach(pane => pane.classList.remove("active"));
      document.getElementById("tab-" + tabId).classList.add("active");
      
      if (tabId === "catalog") {
        // Reset to category lobby list
        document.getElementById("catalog-products").classList.remove("active");
        document.getElementById("catalog-lobby").classList.add("active");
      }
    });
  });

  // Category Banner clicks in Lobby
  document.querySelectorAll(".category-banner-card").forEach(banner => {
    banner.addEventListener("click", () => {
      const category = banner.getAttribute("data-category");
      selectCategory(category);
    });
  });

  // Search Input in Header
  document.getElementById("header-search-input").addEventListener("input", (e) => {
    searchFilterQuery = e.target.value;
    if (searchFilterQuery.trim() !== "") {
      activeCategoryFilter = "all";
      document.getElementById("category-title-header").innerText = "❖ Hasil Pencarian ❖";
      document.getElementById("catalog-lobby").classList.remove("active");
      document.getElementById("catalog-products").classList.add("active");
      renderCustomerCatalog();
    } else {
      document.getElementById("catalog-products").classList.remove("active");
      document.getElementById("catalog-lobby").classList.add("active");
    }
  });

  // Home Button in Header
  document.getElementById("btn-header-home").addEventListener("click", () => {
    document.getElementById("catalog-products").classList.remove("active");
    document.getElementById("catalog-lobby").classList.add("active");
    document.querySelector('.header-tab-new[data-tab="catalog"]').click();
  });

  // Cart Drawer toggling (with login restriction for Guests)
  document.getElementById("btn-cart-toggle").addEventListener("click", () => {
    if (!currentUser) {
      const loginError = document.getElementById("login-error");
      loginError.style.display = "block";
      loginError.style.background = "rgba(212, 175, 55, 0.15)";
      loginError.style.borderColor = "var(--color-primary-gold)";
      loginError.style.color = "var(--color-primary-gold)";
      loginError.innerHTML = "Silakan Login atau Sign In terlebih dahulu untuk melihat keranjang belanja Anda.";
      
      shouldOpenCartAfterLogin = true;
      navigateTo("login-screen");
    } else {
      renderCartDrawer();
      document.getElementById("cart-drawer").classList.add("active");
    }
  });

  document.getElementById("btn-close-cart").addEventListener("click", () => {
    document.getElementById("cart-drawer").classList.remove("active");
  });

  document.getElementById("cart-drawer-overlay").addEventListener("click", () => {
    document.getElementById("cart-drawer").classList.remove("active");
  });

  // Modal food details interactions
  const qtyMinus = document.getElementById("btn-qty-minus");
  const qtyPlus = document.getElementById("btn-qty-plus");
  const qtyDisplay = document.getElementById("modal-qty-display");

  qtyMinus.addEventListener("click", () => {
    if (currentQtyInModal > 1) {
      currentQtyInModal--;
      qtyDisplay.innerText = currentQtyInModal;
    }
  });

  qtyPlus.addEventListener("click", () => {
    currentQtyInModal++;
    qtyDisplay.innerText = currentQtyInModal;
  });

  document.getElementById("btn-add-to-cart").addEventListener("click", () => {
    if (selectedFoodForModal) {
      const notes = document.getElementById("modal-food-notes").value.trim();
      addToCart(selectedFoodForModal, currentQtyInModal, notes);
      document.getElementById("food-detail-modal").classList.remove("active");
    }
  });

  // Close food detail modal
  document.querySelectorAll("#food-detail-modal .modal-close-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      document.getElementById("food-detail-modal").classList.remove("active");
    });
  });

  // Checkout flows
  document.getElementById("btn-checkout").addEventListener("click", handleCheckout);

  // Close payment modal
  document.getElementById("btn-close-payment").addEventListener("click", () => {
    document.getElementById("payment-modal").classList.remove("active");
  });
  document.getElementById("payment-modal-overlay").addEventListener("click", () => {
    document.getElementById("payment-modal").classList.remove("active");
  });

  // Payment method option toggling
  document.querySelectorAll('input[name="payment-method"]').forEach(radio => {
    radio.addEventListener("change", (e) => {
      // Deactivate all panes
      document.querySelectorAll(".payment-details-pane").forEach(pane => pane.classList.remove("active"));
      document.querySelectorAll(".payment-method-card").forEach(c => c.classList.remove("active"));

      // Activate selected pane
      const method = e.target.value.toLowerCase();
      let targetPaneId = "payment-details-cash";
      if (method === "qris") targetPaneId = "payment-details-qris";
      else if (method === "kartu") targetPaneId = "payment-details-card";

      document.getElementById(targetPaneId).classList.add("active");
      e.target.closest(".payment-method-card").classList.add("active");
    });
  });

  // Confirm payment
  document.getElementById("btn-process-payment").addEventListener("click", processPayment);

  // Close receipt
  document.getElementById("btn-close-receipt").addEventListener("click", () => {
    document.getElementById("receipt-modal").classList.remove("active");
    // Switch to order history tab so user can see cooking progress
    document.querySelector('.customer-nav .nav-item[data-tab="order-history"]').click();
  });
  document.getElementById("receipt-modal-overlay").addEventListener("click", () => {
    document.getElementById("receipt-modal").classList.remove("active");
  });

  // 7.4 Admin Nav & Tabs Controller
  document.querySelectorAll(".admin-nav .nav-item").forEach(item => {
    item.addEventListener("click", () => {
      document.querySelectorAll(".admin-nav .nav-item").forEach(nav => nav.classList.remove("active"));
      item.classList.add("active");

      const tabId = item.getAttribute("data-tab");
      document.querySelectorAll("#admin-dashboard .tab-pane").forEach(pane => pane.classList.remove("active"));
      document.getElementById("tab-" + tabId).classList.add("active");
    });
  });

  // Admin Order filters
  document.querySelectorAll(".status-filter-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      document.querySelectorAll(".status-filter-btn").forEach(b => b.classList.remove("active"));
      btn.classList.add("active");
      activeAdminStatusFilter = btn.getAttribute("data-status-filter");
      renderAdminOrders();
    });
  });

  // Admin CRUD Modal Trigger
  document.getElementById("btn-add-menu-modal").addEventListener("click", () => {
    openMenuCrudModal();
  });

  document.getElementById("btn-close-menu-crud").addEventListener("click", () => {
    document.getElementById("menu-crud-modal").classList.remove("active");
  });
  document.getElementById("menu-crud-overlay").addEventListener("click", () => {
    document.getElementById("menu-crud-modal").classList.remove("active");
  });

  // CRUD Menu Submit
  document.getElementById("form-menu-crud").addEventListener("submit", (e) => {
    e.preventDefault();
    const id = document.getElementById("crud-item-id").value;
    const name = document.getElementById("crud-item-name").value;
    const category = document.getElementById("crud-item-category").value;
    const price = parseInt(document.getElementById("crud-item-price").value, 10);
    const desc = document.getElementById("crud-item-desc").value;
    const image = document.getElementById("crud-item-image").value;

    saveMenuItem({ id, name, category, price, desc, image });
  });

});
