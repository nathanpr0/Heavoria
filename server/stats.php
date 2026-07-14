<?php
/**
 * Heavoria Restaurant System - Analytics & Stats API (OOP Version)
 * File: server/stats.php
 * 
 * File ini menangani perhitungan data statistik, laporan grafik, KPI penjualan, 
 * menu terlaris, dan rincian performa kategori produk untuk dashboard admin.
 * Menggunakan pendekatan OOP (Object-Oriented Programming) dengan penjelasan query SQL
 * Agregat (SUM, COUNT, GROUP BY) dalam Bahasa Indonesia untuk mahasiswa pemula.
 */

require_once 'config.php';

class StatsController {
    private $pdo;

    /**
     * Constructor untuk menerima instance PDO database
     * @param PDO $pdo Koneksi database
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Mendapatkan rangkuman statistik analisis penjualan lengkap
     * Endpoint utama untuk dashboard admin.
     */
    public function getAnalyticsSummary() {
        requireAdmin(); // Pengaman: Hanya administrator yang boleh melihat statistik finansial

        try {
            // 1. Menghitung Total Pendapatan (Hanya dari pesanan yang sukses/selesai)
            // Fungsi agregat SUM() menjumlahkan seluruh nilai kolom 'total'.
            $stmtRevenue = $this->pdo->query("SELECT SUM(total) FROM orders WHERE status = 'Selesai'");
            $totalRevenue = (int)$stmtRevenue->fetchColumn();

            // 2. Menghitung Total Seluruh Pesanan masuk (baik Pending, Diproses, maupun Selesai)
            // Fungsi agregat COUNT(*) menghitung total baris tabel.
            $stmtOrdersCount = $this->pdo->query("SELECT COUNT(*) FROM orders");
            $totalOrders = (int)$stmtOrdersCount->fetchColumn();

            // 3. Menemukan Menu Terlaris (Popular Menu Item)
            // Melakukan JOIN antara order_items, orders, dan menu untuk menghitung jumlah kuantitas terbayar.
            // Diurutkan berdasarkan jumlah terbanyak (DESC) dengan LIMIT 1 untuk mengambil menu teratas.
            $stmtPopular = $this->pdo->query("
                SELECT m.name, SUM(oi.qty) as total_qty
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                JOIN menu m ON oi.menu_id = m.id
                WHERE o.status = 'Selesai'
                GROUP BY m.id
                ORDER BY total_qty DESC
                LIMIT 1
            ");
            $popularRow = $stmtPopular->fetch();
            $popularItem = $popularRow ? $popularRow['name'] . " (" . $popularRow['total_qty'] . " porsi)" : "-";

            // 4. Menghitung Pendapatan per Kategori Menu (Japanese Menu, Towel Cake, Elixir Drink)
            // Menghubungkan pesanan selesai dengan kategori menu masing-masing menggunakan GROUP BY.
            $stmtCategory = $this->pdo->query("
                SELECT m.category, SUM(oi.price_at_order * oi.qty) as revenue
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                JOIN menu m ON oi.menu_id = m.id
                WHERE o.status = 'Selesai'
                GROUP BY m.category
            ");

            // Template penampung data kategori default
            $categoryTotals = [
                'sushi' => 0, // Kategori Japanese Menu
                'cake' => 0,  // Kategori Towel Cake
                'drink' => 0  // Kategori Elixir Drink
            ];

            // Memasukkan hasil query database ke penampung
            while ($row = $stmtCategory->fetch()) {
                $cat = $row['category'];
                if (isset($categoryTotals[$cat])) {
                    $categoryTotals[$cat] = (int)$row['revenue'];
                }
            }

            // Mengirimkan respon analitik lengkap ke frontend dalam format JSON
            sendJsonResponse([
                'success' => true,
                'stats' => [
                    'totalRevenue' => $totalRevenue,
                    'totalOrders' => $totalOrders,
                    'popularItem' => $popularItem,
                    'categoryTotals' => $categoryTotals
                ]
            ]);

        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'message' => 'Gagal menghitung statistik keuangan: ' . $e->getMessage()], 500);
        }
    }
}

// Inisialisasi controller dan eksekusi
$statsController = new StatsController($pdo);
$statsController->getAnalyticsSummary();
