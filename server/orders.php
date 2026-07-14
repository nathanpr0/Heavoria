<?php
/**
 * Heavoria Restaurant System - Orders API Endpoints (OOP Version)
 * File: server/orders.php
 * 
 * File ini menangani seluruh siklus transaksi pemesanan (checkout), pengambilan riwayat pesanan
 * untuk customer dan admin, pembaruan status pemesanan, serta perhitungan tanggal pengambilan
 * otomatis. Menggunakan pendekatan OOP (Object-Oriented Programming) dengan dokumentasi lengkap
 * dalam Bahasa Indonesia untuk membantu proses belajar mahasiswa pemula.
 */

require_once 'config.php';

class OrderController {
    private $pdo;

    /**
     * Constructor untuk menerima instance PDO
     * @param PDO $pdo Koneksi database
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Memproses transaksi pembelian makanan / checkout (Create)
     * Menggunakan database TRANSACTION untuk menjamin konsistensi data (Atomicity).
     * Endpoint: action=checkout (POST)
     */
    public function checkout() {
        requireLogin(); // Kredensial wajib: Hanya pengguna terdaftar yang bisa memesan

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            sendJsonResponse(['success' => false, 'message' => 'Metode request tidak valid'], 405);
        }

        $input = getJsonInput();
        $user = getCurrentUser(); // Mendapatkan identitas pembeli dari Session

        $table = (int)($input['table'] ?? 0);
        $items = $input['items'] ?? [];
        $method = trim($input['method'] ?? 'QRIS');
        $fulfillmentType = trim($input['fulfillmentType'] ?? 'pickup');
        $distanceKm = (float)($input['distanceKm'] ?? 0.0);
        $addressNote = trim($input['addressNote'] ?? '');

        // Validasi pesanan tidak boleh kosong
        if (empty($items)) {
            sendJsonResponse(['success' => false, 'message' => 'Keranjang belanja tidak boleh kosong.'], 400);
        }

        // Menghitung subtotal transaksi dari daftar menu yang dipesan
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += (int)$item['price'] * (int)$item['qty'];
        }

        // Perhitungan biaya tambahan:
        // Pajak (Tax) = 10%, Biaya Pelayanan (Service Charge) = 5%
        $tax = (int)($subtotal * 0.1);
        $service = (int)($subtotal * 0.05);

        // Ongkos kirim jika tipe pesanan adalah 'delivery':
        // Rp 4.000 per 1 km, minimal jarak dihitung 1 km
        $deliveryFee = 0;
        if ($fulfillmentType === 'delivery') {
            $distanceKm = Math.max(1, $distanceKm);
            $deliveryFee = (int)ceil($distanceKm * 4000);
        } else {
            $distanceKm = 0;
        }

        // Total akhir = Subtotal + Pajak + Service + Ongkir
        $total = $subtotal + $tax + $service + $deliveryFee;

        // Membuat ID Pesanan acak yang unik dengan format ORD-XXXXXX##
        $orderId = "ORD-" . substr(time(), -6) . rand(10, 99);

        try {
            // Memulai Transaksi SQL. Jika salah satu query gagal, semua query sebelumnya dibatalkan (Rollback).
            $this->pdo->beginTransaction();

            // 1. Memasukkan data transaksi utama ke tabel `orders`
            $stmt = $this->pdo->prepare("
                INSERT INTO orders (
                    id, username, table_number, subtotal, tax, service_charge, 
                    delivery_fee, distance_km, fulfillment_type, address_note, 
                    total, payment_method, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Menunggu Verifikasi')
            ");
            $stmt->execute([
                $orderId, $user['username'], $table, $subtotal, $tax, $service,
                $deliveryFee, $distanceKm, $fulfillmentType, $addressNote,
                $total, $method
            ]);

            // 2. Memasukkan rincian item belanja ke tabel detail `order_items`
            $stmtItem = $this->pdo->prepare("
                INSERT INTO order_items (order_id, menu_id, qty, notes, price_at_order) 
                VALUES (?, ?, ?, ?, ?)
            ");
            foreach ($items as $item) {
                $notes = isset($item['notes']) ? trim($item['notes']) : null;
                // Menggunakan price_at_order untuk mencatat harga menu saat transaksi dibuat, 
                // mencegah perubahan laporan penjualan jika kelak admin menaikkan/menurunkan harga menu.
                $stmtItem->execute([$orderId, $item['id'], (int)$item['qty'], $notes, (int)$item['price']]);
            }

            // Menyimpan seluruh perubahan secara permanen jika tidak ada error
            $this->pdo->commit();

            sendJsonResponse([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat!',
                'order' => [
                    'id' => $orderId,
                    'username' => $user['username'],
                    'table' => $table,
                    'items' => $items,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'service' => $service,
                    'deliveryFee' => $deliveryFee,
                    'distanceKm' => $distanceKm,
                    'fulfillmentType' => $fulfillmentType,
                    'addressNote' => $addressNote,
                    'total' => $total,
                    'method' => $method,
                    'status' => 'Menunggu Verifikasi',
                    'date' => date('c')
                ]
            ]);

        } catch (PDOException $e) {
            // Membatalkan transaksi database jika terjadi kegagalan sistem
            $this->pdo->rollBack();
            sendJsonResponse(['success' => false, 'message' => 'Gagal memproses checkout: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Membaca riwayat pesanan (Read)
     * Untuk Admin: Menampilkan seluruh pesanan aktif (kecuali yang sudah Selesai atau Ditolak).
     * Untuk Customer: Menampilkan riwayat pesanan pribadinya.
     * Endpoint: action=get (GET)
     */
    public function getOrders() {
        requireLogin();
        $user = getCurrentUser();

        try {
            if ($user['role'] === 'admin') {
                // Query Admin: Mengambil pesanan aktif untuk diproses di dapur / kasir
                // Menggunakan LEFT JOIN untuk menghubungkan tabel orders, order_items, dan menu
                $stmt = $this->pdo->prepare("
                    SELECT o.*, oi.menu_id, oi.qty, oi.notes, oi.price_at_order, m.name as menu_name, m.image_url as menu_image
                    FROM orders o
                    LEFT JOIN order_items oi ON o.id = oi.order_id
                    LEFT JOIN menu m ON oi.menu_id = m.id
                    WHERE o.status != 'Selesai' AND o.status != 'Ditolak'
                    ORDER BY o.created_at ASC
                ");
                $stmt->execute();
            } else {
                // Query Customer: Mengambil daftar pesanan miliknya sendiri
                $stmt = $this->pdo->prepare("
                    SELECT o.*, oi.menu_id, oi.qty, oi.notes, oi.price_at_order, m.name as menu_name, m.image_url as menu_image
                    FROM orders o
                    LEFT JOIN order_items oi ON o.id = oi.order_id
                    LEFT JOIN menu m ON oi.menu_id = m.id
                    WHERE o.username = ?
                    ORDER BY o.created_at DESC
                ");
                $stmt->execute([$user['username']]);
            }

            $orders = $this->groupOrderRows($stmt);
            sendJsonResponse(array_values($orders));

        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'message' => 'Gagal mengambil riwayat transaksi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mendapatkan laporan penjualan lengkap (seluruh pesanan) untuk Admin
     * Endpoint: action=get_all (GET)
     */
    public function getAllOrders() {
        requireAdmin(); // Proteksi Admin

        try {
            $stmt = $this->pdo->query("
                SELECT o.*, oi.menu_id, oi.qty, oi.notes, oi.price_at_order, m.name as menu_name, m.image_url as menu_image
                FROM orders o
                LEFT JOIN order_items oi ON o.id = oi.order_id
                LEFT JOIN menu m ON oi.menu_id = m.id
                ORDER BY o.created_at DESC
            ");

            $orders = $this->groupOrderRows($stmt);
            sendJsonResponse(array_values($orders));
        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'message' => 'Gagal memuat log penjualan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mengubah/Memperbarui Status Pesanan secara Real-time
     * Alur Status: Menunggu Verifikasi -> Dikonfirmasi -> Siap Diambil -> Selesai
     * Serta mendukung penolakan pesanan (Ditolak) dengan input alasan penolakan.
     * Endpoint: action=update_status (POST)
     */
    public function updateStatus() {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            sendJsonResponse(['success' => false, 'message' => 'Metode request tidak valid'], 405);
        }

        $input = getJsonInput();
        $orderId = trim($input['id'] ?? '');
        $requestedStatus = trim($input['status'] ?? '');
        $reason = trim($input['reason'] ?? '');

        if (empty($orderId)) {
            sendJsonResponse(['success' => false, 'message' => 'ID pesanan wajib diisi.'], 400);
        }

        try {
            // Mengambil status pesanan saat ini dari database
            $stmt = $this->pdo->prepare("SELECT status FROM orders WHERE id = ?");
            $stmt->execute([$orderId]);
            $currentStatus = $stmt->fetchColumn();

            if (!$currentStatus) {
                sendJsonResponse(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
            }

            // Menentukan status baru
            $newStatus = $currentStatus;
            if ($requestedStatus !== '') {
                // Memastikan status baru terdaftar dalam daftar status yang valid
                $allowed = ['Dikonfirmasi', 'Ditolak', 'Siap Diambil', 'Selesai'];
                if (!in_array($requestedStatus, $allowed, true)) {
                    sendJsonResponse(['success' => false, 'message' => 'Tujuan pembaruan status tidak valid.'], 400);
                }
                $newStatus = $requestedStatus;
            } else {
                // Alur otomatis sekuensial jika parameter status tidak ditentukan oleh pengirim
                if ($currentStatus === 'Menunggu Verifikasi') {
                    $newStatus = 'Dikonfirmasi';
                } elseif ($currentStatus === 'Dikonfirmasi') {
                    $newStatus = 'Siap Diambil';
                } elseif ($currentStatus === 'Siap Diambil') {
                    $newStatus = 'Selesai';
                }
            }

            // Aturan Otomatis Tanggal Pengambilan:
            // Jika pesanan ditandai 'Siap Diambil', sistem otomatis menjadwalkan pengambilan
            // pada hari SENIN terdekat.
            $pickupDate = null;
            if ($newStatus === 'Siap Diambil') {
                $today = new DateTime('today');
                $pickupDate = clone $today;
                // Jika hari ini bukan hari Senin (1), cari hari Senin terdekat berikutnya (next monday)
                if ($pickupDate->format('N') !== '1') {
                    $pickupDate->modify('next monday');
                }
            }

            // Memperbarui database
            $stmtUpdate = $this->pdo->prepare("
                UPDATE orders
                SET status = ?,
                    pickup_date = COALESCE(?, pickup_date),
                    rejection_reason = CASE WHEN ? = 'Ditolak' THEN ? ELSE rejection_reason END
                WHERE id = ?
            ");
            $stmtUpdate->execute([
                $newStatus,
                $pickupDate ? $pickupDate->format('Y-m-d') : null,
                $newStatus,
                $reason,
                $orderId
            ]);

            sendJsonResponse([
                'success' => true,
                'message' => 'Status pesanan berhasil diperbarui.',
                'newStatus' => $newStatus
            ]);

        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'message' => 'Gagal mengubah status pesanan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper privat untuk mengelompokkan baris data relasional (JOIN) database
     * dari format database (tabular/flat) menjadi format bersarang (nested order & order_items)
     */
    private function groupOrderRows($stmt) {
        $orders = [];
        while ($row = $stmt->fetch()) {
            $orderId = $row['id'];
            if (!isset($orders[$orderId])) {
                $orders[$orderId] = [
                    'id' => $row['id'],
                    'username' => $row['username'],
                    'table' => $row['table_number'],
                    'fulfillmentType' => $row['fulfillment_type'] ?? 'pickup',
                    'distanceKm' => (float)($row['distance_km'] ?? 0),
                    'deliveryFee' => (int)($row['delivery_fee'] ?? 0),
                    'addressNote' => $row['address_note'] ?? '',
                    'pickupDate' => $row['pickup_date'] ?? null,
                    'rejectionReason' => $row['rejection_reason'] ?? '',
                    'subtotal' => (int)$row['subtotal'],
                    'tax' => (int)$row['tax'],
                    'service' => (int)$row['service_charge'],
                    'total' => (int)$row['total'],
                    'method' => $row['payment_method'],
                    'status' => $row['status'],
                    'date' => date('c', strtotime($row['created_at'])),
                    'items' => []
                ];
            }
            
            // Masukkan item masakan jika baris memuat relasi produk menu
            if ($row['menu_id']) {
                $orders[$orderId]['items'][] = [
                    'id' => $row['menu_id'],
                    'name' => $row['menu_name'],
                    'price' => (int)$row['price_at_order'],
                    'image' => $row['menu_image'],
                    'qty' => (int)$row['qty'],
                    'notes' => $row['notes']
                ];
            }
        }
        return $orders;
    }
}

// Inisialisasi controller dan routing
$orderController = new OrderController($pdo);

// Mengambil parameter action dari URL
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'checkout':
        $orderController->checkout();
        break;
    case 'get':
        $orderController->getOrders();
        break;
    case 'get_all':
        $orderController->getAllOrders();
        break;
    case 'update_status':
        $orderController->updateStatus();
        break;
    default:
        sendJsonResponse(['success' => false, 'message' => 'Aksi transaksi tidak dikenal'], 404);
        break;
}
