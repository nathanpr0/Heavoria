<?php
/**
 * Heavoria Restaurant System - Menu API Endpoints (OOP Version)
 * File: server/menu.php
 * 
 * File ini menangani seluruh operasi CRUD (Create, Read, Update, Delete)
 * pada daftar menu/makanan restoran. Menggunakan pendekatan OOP (Object-Oriented Programming)
 * dengan penjelasan mendalam tentang konsep SQL Injection Prevention & Database Integrity.
 */

require_once 'config.php';

class MenuController {
    private $pdo;

    /**
     * Constructor untuk menerima instance PDO
     * @param PDO $pdo Koneksi database
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Membaca semua data menu dari database (Read)
     * Endpoint: action=get (GET)
     */
    public function get() {
        try {
            // Melakukan query SELECT untuk mengambil seluruh menu, diurutkan dari yang terbaru dibuat
            $stmt = $this->pdo->query("SELECT * FROM menu ORDER BY created_at DESC");
            $menuItems = [];

            while ($row = $stmt->fetch()) {
                // Memformat data ke bentuk array asosiatif sesuai ekspektasi frontend javascript
                $menuItems[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'category' => $row['category'],
                    'price' => (int)$row['price'],
                    // Menghitung harga asli sebelum diskon (secara simulasi naik 33%)
                    // untuk menampilkan coretan diskon / badge promo di UI.
                    'originalPrice' => (int)($row['price'] * 1.33),
                    'desc' => $row['description'],
                    'image' => $row['image_url']
                ];
            }
            sendJsonResponse($menuItems);
        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'message' => 'Gagal memuat katalog menu: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Menyimpan data menu baru atau memperbarui menu yang sudah ada (Create / Update)
     * Endpoint: action=save (POST)
     */
    public function save() {
        requireAdmin(); // Pengaman: Hanya administrator yang boleh menambah/mengubah produk

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            sendJsonResponse(['success' => false, 'message' => 'Metode request tidak valid'], 405);
        }

        $input = getJsonInput();
        $id = trim($input['id'] ?? '');
        $name = trim($input['name'] ?? '');
        $category = trim($input['category'] ?? '');
        $price = (int)($input['price'] ?? 0);
        $desc = trim($input['desc'] ?? '');
        $image = trim($input['image'] ?? '');

        // Validasi kelengkapan kolom form menu
        if (empty($name) || empty($category) || $price <= 0 || empty($desc) || empty($image)) {
            sendJsonResponse(['success' => false, 'message' => 'Semua kolom data menu wajib diisi dengan benar.'], 400);
        }

        try {
            if (!empty($id)) {
                // Skenario 1: EDIT MODE (ID menu terisi)
                // Menggunakan Prepared Statement untuk mencegah SQL Injection secara total.
                $stmt = $this->pdo->prepare("UPDATE menu SET name = ?, category = ?, price = ?, description = ?, image_url = ? WHERE id = ?");
                $stmt->execute([$name, $category, $price, $desc, $image, $id]);
                sendJsonResponse(['success' => true, 'message' => 'Data menu berhasil diperbarui.']);
            } else {
                // Skenario 2: ADD MODE (ID menu kosong, buat ID baru)
                // ID menu dibentuk secara unik berbasis prefix 'menu-', UNIX timestamp, dan angka acak.
                $newId = 'menu-' . time() . '-' . rand(100, 999);
                $stmt = $this->pdo->prepare("INSERT INTO menu (id, name, category, price, description, image_url) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$newId, $name, $category, $price, $desc, $image]);
                sendJsonResponse(['success' => true, 'message' => 'Menu baru berhasil ditambahkan.', 'id' => $newId]);
            }
        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'message' => 'Gagal memproses data menu: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Menghapus menu dari database (Delete)
     * Endpoint: action=delete (POST)
     */
    public function delete() {
        requireAdmin(); // Pengaman: Hanya administrator yang boleh menghapus produk

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            sendJsonResponse(['success' => false, 'message' => 'Metode request tidak valid'], 405);
        }

        $input = getJsonInput();
        $id = trim($input['id'] ?? '');

        if (empty($id)) {
            sendJsonResponse(['success' => false, 'message' => 'ID menu tidak boleh kosong.'], 400);
        }

        try {
            // Integritas Database / Referential Integrity:
            // Pada database, tabel order_items memiliki FOREIGN KEY menu_id yang mengacu ke menu(id) dengan aksi ON DELETE RESTRICT.
            // RESTRICT mencegah penghapusan menu jika menu tersebut sudah pernah dibeli (memiliki referensi transaksi lama).
            // Kita periksa secara manual terlebih dahulu agar bisa mengembalikan pesan error yang ramah pengguna.
            $stmtCheck = $this->pdo->prepare("SELECT COUNT(*) FROM order_items WHERE menu_id = ?");
            $stmtCheck->execute([$id]);
            
            if ($stmtCheck->fetchColumn() > 0) {
                sendJsonResponse([
                    'success' => false, 
                    'message' => 'Menu ini tidak dapat dihapus karena sudah tercatat dalam riwayat pesanan/transaksi pelanggan.'
                ], 400);
            }

            // Jika aman dari referensi, lakukan proses penghapusan
            $stmt = $this->pdo->prepare("DELETE FROM menu WHERE id = ?");
            $stmt->execute([$id]);
            sendJsonResponse(['success' => true, 'message' => 'Menu berhasil dihapus dari katalog restoran.']);
        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'message' => 'Gagal menghapus menu: ' . $e->getMessage()], 500);
        }
    }
}

// Inisialisasi controller dan routing
$menuController = new MenuController($pdo);
// Mengambil parameter action dari URL
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get':
        $menuController->get();
        break;
    case 'save':
        $menuController->save();
        break;
    case 'delete':
        $menuController->delete();
        break;
    default:
        sendJsonResponse(['success' => false, 'message' => 'Aksi katalog menu tidak dikenal'], 404);
        break;
}
