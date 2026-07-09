<?php
/**
 * Heavoria Restaurant System - Database Configuration & Helpers
 * File: server/config.php
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'Heavoria');

// Establish PDO Database Connection
try {
    $bootstrapPdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
    $bootstrapPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // If database connection fails, return JSON error response
    sendJsonResponse([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ], 500);
}

initializeHeavoriaDatabase($pdo);

function columnExists($pdo, $table, $column) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?
    ");
    $stmt->execute([DB_NAME, $table, $column]);
    return (int)$stmt->fetchColumn() > 0;
}

function ensureColumn($pdo, $table, $column, $definition) {
    if (!columnExists($pdo, $table, $column)) {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
    }
}

function initializeHeavoriaDatabase($pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL UNIQUE,
            email VARCHAR(160) NULL UNIQUE,
            phone VARCHAR(40) NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('user','admin') NOT NULL DEFAULT 'user',
            last_seen DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS menu (
            id VARCHAR(80) PRIMARY KEY,
            name VARCHAR(160) NOT NULL,
            category VARCHAR(40) NOT NULL,
            price INT NOT NULL,
            description TEXT NOT NULL,
            image_url VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orders (
            id VARCHAR(80) PRIMARY KEY,
            username VARCHAR(100) NOT NULL,
            table_number INT NULL DEFAULT 0,
            subtotal INT NOT NULL DEFAULT 0,
            tax INT NOT NULL DEFAULT 0,
            service_charge INT NOT NULL DEFAULT 0,
            delivery_fee INT NOT NULL DEFAULT 0,
            distance_km DECIMAL(6,2) NOT NULL DEFAULT 0,
            fulfillment_type VARCHAR(20) NOT NULL DEFAULT 'pickup',
            address_note TEXT NULL,
            pickup_date DATE NULL,
            rejection_reason TEXT NULL,
            total INT NOT NULL DEFAULT 0,
            payment_method VARCHAR(40) NOT NULL DEFAULT 'QRIS',
            status VARCHAR(40) NOT NULL DEFAULT 'Menunggu Verifikasi',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id VARCHAR(80) NOT NULL,
            menu_id VARCHAR(80) NOT NULL,
            qty INT NOT NULL,
            notes TEXT NULL,
            price_at_order INT NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (menu_id) REFERENCES menu(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    ensureColumn($pdo, 'users', 'email', "VARCHAR(160) NULL UNIQUE AFTER username");
    ensureColumn($pdo, 'users', 'last_seen', "DATETIME NULL");
    ensureColumn($pdo, 'orders', 'delivery_fee', "INT NOT NULL DEFAULT 0 AFTER service_charge");
    ensureColumn($pdo, 'orders', 'distance_km', "DECIMAL(6,2) NOT NULL DEFAULT 0 AFTER delivery_fee");
    ensureColumn($pdo, 'orders', 'fulfillment_type', "VARCHAR(20) NOT NULL DEFAULT 'pickup' AFTER distance_km");
    ensureColumn($pdo, 'orders', 'address_note', "TEXT NULL AFTER fulfillment_type");
    ensureColumn($pdo, 'orders', 'pickup_date', "DATE NULL AFTER address_note");
    ensureColumn($pdo, 'orders', 'rejection_reason', "TEXT NULL AFTER pickup_date");

    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmtAdmin = $pdo->prepare("
        INSERT INTO users (username, email, phone, password, role)
        VALUES ('admin', 'admin@heavoria.com', '0800000000', ?, 'admin')
        ON DUPLICATE KEY UPDATE role = 'admin'
    ");
    $stmtAdmin->execute([$adminPassword]);

    $products = [
        ['nigiri', 'Nigiri', 'sushi', 6000, 'Japanese menu dengan nasi sushi dan topping lembut khas Heavoria.', 'assets/nigiri.jpeg'],
        ['sushi-roll', 'Sushi Roll', 'sushi', 5000, 'Sushi roll praktis dengan rasa gurih dan porsi ringan.', 'assets/sushiroll.jpeg'],
        ['towelcake-strawberry', 'Towelcake Strawberry', 'cake', 10000, 'Towel cake lembut rasa strawberry dengan tampilan manis.', 'assets/towelcake_strawberry.jpeg'],
        ['towelcake-mango', 'Towelcake Mango', 'cake', 10000, 'Towel cake lembut rasa mango yang segar dan creamy.', 'assets/towelcake_mango.jpeg'],
        ['towelcake-grape', 'Towelcake Grape', 'cake', 10000, 'Towel cake lembut rasa grape dengan aroma buah yang ringan.', 'assets/towelcake_grape.jpeg'],
    ];

    $stmtProduct = $pdo->prepare("
        INSERT INTO menu (id, name, category, price, description, image_url)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            name = VALUES(name),
            category = VALUES(category),
            price = VALUES(price),
            description = VALUES(description),
            image_url = VALUES(image_url)
    ");
    foreach ($products as $product) {
        $stmtProduct->execute($product);
    }
}

/**
 * Send JSON response and exit
 * @param array $data Data to send
 * @param int $statusCode HTTP status code
 */
function sendJsonResponse($data, $statusCode = 200) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

/**
 * Read raw JSON input from request body
 * @return array
 */
function getJsonInput() {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?? [];
}

/**
 * Verify if a user is logged in
 * @return array|null User details if logged in, null otherwise
 */
function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

/**
 * Verify if logged in user is an administrator
 * @return bool
 */
function isAdmin() {
    $user = getCurrentUser();
    return $user && $user['role'] === 'admin';
}

/**
 * Helper to require admin privilege
 */
function requireAdmin() {
    if (!isAdmin()) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Unauthorized access. Admin privileges required.'
        ], 403);
    }
}

/**
 * Helper to require login privilege
 */
function requireLogin() {
    if (!getCurrentUser()) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Unauthorized access. Login required.'
        ], 401);
    }
}
