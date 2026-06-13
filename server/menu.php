<?php
/**
 * Heavoria Restaurant System - Menu API Endpoints
 * File: server/menu.php
 */

require_once 'config.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get':
        try {
            $stmt = $pdo->query("SELECT * FROM menu ORDER BY created_at DESC");
            $menuItems = [];
            while ($row = $stmt->fetch()) {
                $menuItems[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'category' => $row['category'],
                    'price' => (int)$row['price'],
                    // Calculate a mock original price to show discount badges, or set to null
                    'originalPrice' => (int)($row['price'] * 1.33),
                    'desc' => $row['description'],
                    'image' => $row['image_url']
                ];
            }
            sendJsonResponse($menuItems);
        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'message' => 'Failed to fetch menu: ' . $e->getMessage()], 500);
        }
        break;

    case 'save':
        requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            sendJsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $input = getJsonInput();
        $id = trim($input['id'] ?? '');
        $name = trim($input['name'] ?? '');
        $category = trim($input['category'] ?? '');
        $price = (int)($input['price'] ?? 0);
        $desc = trim($input['desc'] ?? '');
        $image = trim($input['image'] ?? '');

        if (empty($name) || empty($category) || $price <= 0 || empty($desc) || empty($image)) {
            sendJsonResponse(['success' => false, 'message' => 'Semua kolom data menu wajib diisi.'], 400);
        }

        try {
            if (!empty($id)) {
                // Update Mode
                $stmt = $pdo->prepare("UPDATE menu SET name = ?, category = ?, price = ?, description = ?, image_url = ? WHERE id = ?");
                $stmt->execute([$name, $category, $price, $desc, $image, $id]);
                sendJsonResponse(['success' => true, 'message' => 'Menu berhasil diperbarui.']);
            } else {
                // Create Mode
                $newId = 'menu-' . time() . '-' . rand(100, 999);
                $stmt = $pdo->prepare("INSERT INTO menu (id, name, category, price, description, image_url) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$newId, $name, $category, $price, $desc, $image]);
                sendJsonResponse(['success' => true, 'message' => 'Menu baru berhasil ditambahkan.', 'id' => $newId]);
            }
        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'message' => 'Gagal menyimpan menu: ' . $e->getMessage()], 500);
        }
        break;

    case 'delete':
        requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            sendJsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $input = getJsonInput();
        $id = trim($input['id'] ?? '');

        if (empty($id)) {
            sendJsonResponse(['success' => false, 'message' => 'ID menu tidak boleh kosong.'], 400);
        }

        try {
            // Check if there are active orders referencing this menu first?
            // The DB schema says: order_items FOREIGN KEY menu_id REFERENCES menu(id) ON DELETE RESTRICT.
            // If RESTRICT is active, we cannot delete a menu item that is part of a past/current order.
            // Let's handle this constraint gracefully.
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE menu_id = ?");
            $stmtCheck->execute([$id]);
            if ($stmtCheck->fetchColumn() > 0) {
                // Instead of hard deleting, we can either return a warning or fail
                sendJsonResponse([
                    'success' => false, 
                    'message' => 'Menu ini tidak dapat dihapus karena sudah pernah dipesan dalam transaksi riwayat.'
                ], 400);
            }

            $stmt = $pdo->prepare("DELETE FROM menu WHERE id = ?");
            $stmt->execute([$id]);
            sendJsonResponse(['success' => true, 'message' => 'Menu berhasil dihapus.']);
        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'message' => 'Gagal menghapus menu: ' . $e->getMessage()], 500);
        }
        break;

    default:
        sendJsonResponse(['success' => false, 'message' => 'Action not found'], 404);
        break;
}
