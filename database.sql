-- ==========================================================================
-- HEAVORIA RESTAURANT DATABASE SCHEMA
-- File: database.sql
-- Digunakan sebagai cetak biru jika Anda ingin memindahkan sistem ini ke 
-- Database relasional nyata (seperti MySQL, PostgreSQL, atau SQLite).
-- ==========================================================================

-- Hapus tabel jika sudah ada (untuk keperluan reset)
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS menu;
DROP TABLE IF EXISTS users;

-- 1. TABEL USERS (Menyimpan akun pengguna & peran mereka)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL, -- Di produksi asli, ini harus di-hash (misal menggunakan bcrypt)
    role ENUM('admin', 'user') DEFAULT 'user' NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. TABEL MENU (Menyimpan katalog hidangan restoran)
CREATE TABLE menu (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category ENUM('sushi', 'cake', 'drink') NOT NULL,
    price INT NOT NULL,
    description TEXT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. TABEL ORDERS (Menyimpan transaksi pesanan meja)
CREATE TABLE orders (
    id VARCHAR(50) PRIMARY KEY, -- ID pesanan (misal: ORD-123456)
    username VARCHAR(50) NOT NULL,
    table_number INT NOT NULL,
    subtotal INT NOT NULL,
    tax INT NOT NULL,
    service_charge INT NOT NULL,
    total INT NOT NULL,
    payment_method ENUM('QRIS', 'Kartu', 'Tunai') NOT NULL,
    status ENUM('Pending', 'Diproses', 'Selesai') DEFAULT 'Pending' NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (username) REFERENCES users(username) ON DELETE CASCADE
);

-- 4. TABEL ORDER ITEMS (Menyimpan rincian item di dalam setiap pesanan - Relasi Many-to-Many)
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(50) NOT NULL,
    menu_id VARCHAR(50) NOT NULL,
    qty INT NOT NULL,
    notes TEXT,
    price_at_order INT NOT NULL, -- Menyimpan harga saat dipesan agar tidak berubah jika menu di-update
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menu(id) ON DELETE RESTRICT
);


-- ==========================================================================
-- SEED DATA (Data Awal untuk Pengujian)
-- ==========================================================================

-- Memasukkan Data Pengguna Bawaan
-- Password didefinisikan secara plain untuk simulasi awal
INSERT INTO users (username, phone, password, role) VALUES
('admin', '08123456789', 'admin', 'admin'),
('customer', '08987654321', 'customer', 'user');

-- Memasukkan Data Menu Awal (Heavoria Cuisine)
INSERT INTO menu (id, name, category, price, description, image_url) VALUES
('menu-1', 'Royal Chocolate Roll Cake', 'cake', 45000, 'Kue gulung cokelat premium yang lembut, disajikan dengan parutan cokelat Belgia murni dan taburan emas yang mewah.', 'assets/heavoria_cake.png'),
('menu-2', 'Gold Mango Mousse Cake', 'cake', 38000, 'Mousse mangga segar dengan rasa manis seimbang, dibalut lapisan glaze mengkilap dan hiasan buah segar.', 'assets/heavoria_cake.png'),
('menu-3', 'Strawberry Dream Tart', 'cake', 42000, 'Tart panggang renyah dengan krim vanila lembut, buah stroberi merah ranum, dan siraman madu organik.', 'assets/heavoria_cake.png'),
('menu-4', 'Salmon Nigiri Platter', 'sushi', 68000, 'Tiga potong sushi salmon premium segar diletakkan di atas nasi sushi Jepang pulen beraroma cuka anggur putih.', 'assets/heavoria_sushi.png'),
('menu-5', 'Golden Dragon Roll', 'sushi', 78000, 'Sushi roll isi ebi tempura renyah, dilapisi alpukat lembut dan salmon bakar saus unagi emas di atasnya.', 'assets/heavoria_sushi.png'),
('menu-6', 'Spicy Amber Tuna Roll', 'sushi', 55000, 'Tuna pedas cincang segar dengan taburan kremesan renyah, dibungkus rumput laut nori panggang terbaik.', 'assets/heavoria_sushi.png'),
('menu-7', 'Gold Flake Iced Latte', 'drink', 32000, 'Espresso premium arabika disajikan dingin dengan susu segar lembut dan sentuhan serpihan emas 24k food-grade.', 'assets/heavoria_drink.png'),
('menu-8', 'Honey Amber Jasmine Tea', 'drink', 24000, 'Teh melati seduhan dingin berkualitas tinggi yang dicampur dengan madu hutan alami manis alami dan lemon segar.', 'assets/heavoria_drink.png'),
('menu-9', 'Matcha Emerald Cream', 'drink', 28000, 'Teh hijau matcha Uji otentik yang dikocok dingin dengan susu krim premium dan krim kocok vanila di atasnya.', 'assets/heavoria_drink.png');
