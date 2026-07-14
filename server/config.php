<?php
/**
 * Heavoria Restaurant System - Database Configuration & Helpers
 * File: server/config.php
 * 
 * File ini berfungsi sebagai pusat konfigurasi sistem, inisialisasi session,
 * koneksi database berbasis PDO (PHP Data Objects), pembuatan skema tabel secara otomatis (autobootstrap),
 * serta penyediaan fungsi helper/utilitas global untuk otorisasi dan respon JSON.
 * Cocok untuk bahan pembelajaran pemrograman web berbasis PHP OOP & Relational Database (MySQL).
 */

// Mengaktifkan session secara global jika belum berjalan.
// Session digunakan untuk menyimpan data login user/admin lintas halaman di sisi server.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mendefinisikan konstanta konfigurasi database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'heavoria');

/**
 * Class Database
 * Mengimplementasikan pola Singleton untuk memastikan koneksi database (PDO)
 * hanya dibuat satu kali selama siklus eksekusi script berjalan.
 */
class Database {
    private static $instance = null;
    private $pdo = null;

    // Private constructor mencegah instansiasi langsung menggunakan kata kunci 'new'
    private function __construct() {
        try {
            // Langkah 1: Hubungkan ke MySQL server terlebih dahulu (tanpa memilih database)
            $bootstrapPdo = new PDO(
                "mysql:host=" . DB_HOST . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false, // Mencegah emulasi query agar lebih aman dari SQL Injection
                ]
            );
            
            // Langkah 2: Buat database Heavoria jika belum ada
            $bootstrapPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            // Langkah 3: Koneksikan kembali PDO secara spesifik ke database Heavoria
            $this->pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );

            // Langkah 4: Inisialisasi skema tabel dan seed data awal
            $this->initializeSchema();

        } catch (PDOException $e) {
            // Jika koneksi gagal, kembalikan respon error berformat JSON
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Mengambil instance tunggal dari class Database
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Mengambil objek PDO aktif untuk keperluan query database
     * @return PDO
     */
    public function getConnection() {
        return $this->pdo;
    }

    /**
     * Memeriksa apakah sebuah kolom ada di dalam suatu tabel database
     */
    private function columnExists($table, $column) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?
        ");
        $stmt->execute([DB_NAME, $table, $column]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Memastikan kolom tertentu terbuat di dalam tabel
     */
    private function ensureColumn($table, $column, $definition) {
        if (!$this->columnExists($table, $column)) {
            $this->pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        }
    }

    /**
     * Membuat skema tabel dan memasukkan data sampel produk (Seeder)
     */
    private function initializeSchema() {
        // Tabel Users: Menyimpan data akun pelanggan dan administrator
        $this->pdo->exec("
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

        // Tabel Menu: Menyimpan katalog menu makanan/minuman restoran
        $this->pdo->exec("
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

        // Tabel Orders: Menyimpan transaksi pemesanan
        $this->pdo->exec("
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

        // Tabel Order Items: Menyimpan rincian item produk dalam setiap order (tabel jembatan/pivot)
        $this->pdo->exec("
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

        // Migrasi penambahan kolom tambahan secara dinamis jika belum ada
        $this->ensureColumn('users', 'email', "VARCHAR(160) NULL UNIQUE AFTER username");
        $this->ensureColumn('users', 'last_seen', "DATETIME NULL");
        $this->ensureColumn('orders', 'delivery_fee', "INT NOT NULL DEFAULT 0 AFTER service_charge");
        $this->ensureColumn('orders', 'distance_km', "DECIMAL(6,2) NOT NULL DEFAULT 0 AFTER delivery_fee");
        $this->ensureColumn('orders', 'fulfillment_type', "VARCHAR(20) NOT NULL DEFAULT 'pickup' AFTER distance_km");
        $this->ensureColumn('orders', 'address_note', "TEXT NULL AFTER fulfillment_type");
        $this->ensureColumn('orders', 'pickup_date', "DATE NULL AFTER address_note");
        $this->ensureColumn('orders', 'rejection_reason', "TEXT NULL AFTER pickup_date");

        // Menambahkan akun admin bawaan (Username: admin, Password: admin123)
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmtAdmin = $this->pdo->prepare("
            INSERT INTO users (username, email, phone, password, role)
            VALUES ('admin', 'admin@heavoria.com', '0800000000', ?, 'admin')
            ON DUPLICATE KEY UPDATE role = 'admin'
        ");
        $stmtAdmin->execute([$adminPassword]);

        // Menambahkan menu bawaan ke katalog sesuai instruksi terbaru
        $products = [
            ['nigiri', 'Nigiri', 'sushi', 6000, 'Japanese menu dengan nasi sushi premium dan topping salmon lembut khas Heavoria.', 'assets/nigiri.jpeg'],
            ['sushi-roll', 'Sushi Roll', 'sushi', 5000, 'Sushi roll praktis isi ebi tempura dengan rasa gurih yang lezat.', 'assets/sushiroll.jpeg'],
            ['towelcake-strawberry', 'Towelcake Strawberry', 'cake', 10000, 'Towel cake lembut rasa strawberry dengan isian krim manis segar.', 'assets/towelcake_strawberry.jpeg'],
            ['towelcake-mango', 'Towelcake Mango', 'cake', 10000, 'Towel cake lembut rasa mango premium yang segar dan gurih.', 'assets/towelcake_mango.jpeg'],
            ['towelcake-grape', 'Towelcake Grape', 'cake', 10000, 'Towel cake lembut rasa grape dengan aroma buah anggur segar yang memikat.', 'assets/towelcake_grape.jpeg'],
        ];

        $stmtProduct = $this->pdo->prepare("
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
}

// Mendapatkan koneksi database global
$dbInstance = Database::getInstance();
$pdo = $dbInstance->getConnection();

/**
 * Mengirimkan respon JSON ke client browser
 * @param array $data Data yang akan dikirim dalam format array asosiatif
 * @param int $statusCode Kode status HTTP (200 untuk OK, 400 Bad Request, 500 Server Error, dll.)
 */
function sendJsonResponse($data, $statusCode = 200) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

/**
 * Membaca input JSON mentah dari request body (biasanya dikirim via Fetch API POST)
 * @return array Array berisi data yang dikirim client
 */
function getJsonInput() {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?? [];
}

/**
 * Mengambil data user yang sedang aktif dari Session server
 * @return array|null Objek array data user jika login, atau null jika tidak login
 */
function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

/**
 * Memeriksa apakah user yang sedang login adalah admin
 * @return bool True jika login sebagai admin
 */
function isAdmin() {
    $user = getCurrentUser();
    return $user && $user['role'] === 'admin';
}

/**
 * Guard Helper: Menghentikan program dengan respon error 403 jika pengakses bukan admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Akses ditolak. Halaman ini memerlukan hak akses Administrator.'
        ], 403);
    }
}

/**
 * Guard Helper: Menghentikan program dengan respon error 401 jika pengakses belum login
 */
function requireLogin() {
    if (!getCurrentUser()) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Akses ditolak. Silakan login terlebih dahulu.'
        ], 401);
    }
}
