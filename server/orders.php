<?php
/**
 * Heavoria Restaurant System - Orders API Endpoints
 * File: server/orders.php
 */

require_once 'config.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'checkout':
        requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            sendJsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $input = getJsonInput();
        $user = getCurrentUser();
        
        $table = (int)($input['table'] ?? 0);
        $items = $input['items'] ?? [];
        $method = trim($input['method'] ?? 'QRIS');

        if ($table <= 0 || empty($items)) {
            sendJsonResponse(['success' => false, 'message' => 'Nomor meja dan daftar pesanan tidak boleh kosong.'], 400);
        }

        // Validate values
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += (int)$item['price'] * (int)$item['qty'];
        }
        $tax = (int)($subtotal * 0.1);
        $service = (int)($subtotal * 0.05);
        $total = $subtotal + $tax + $service;

        $orderId = "ORD-" . substr(time(), -6) . rand(10, 99);

        try {
            $pdo->beginTransaction();

            // Insert into orders table
            $stmt = $pdo->prepare("INSERT INTO orders (id, username, table_number, subtotal, tax, service_charge, total, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
            $stmt->execute([$orderId, $user['username'], $table, $subtotal, $tax, $service, $total, $method]);

            // Insert into order_items table
            $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, menu_id, qty, notes, price_at_order) VALUES (?, ?, ?, ?, ?)");
            foreach ($items as $item) {
                $notes = isset($item['notes']) ? trim($item['notes']) : null;
                $stmtItem->execute([$orderId, $item['id'], (int)$item['qty'], $notes, (int)$item['price']]);
            }

            $pdo->commit();

            // Fetch the completed order payload to show the receipt
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
                    'total' => $total,
                    'method' => $method,
                    'status' => 'Pending',
                    'date' => date('c')
                ]
            ]);

        } catch (PDOException $e) {
            $pdo->rollBack();
            sendJsonResponse(['success' => false, 'message' => 'Checkout failed: ' . $e->getMessage()], 500);
        }
        break;

    case 'get':
        requireLogin();
        $user = getCurrentUser();
        
        try {
            if ($user['role'] === 'admin') {
                // Return active orders (non-completed)
                $stmt = $pdo->prepare("
                    SELECT o.*, oi.menu_id, oi.qty, oi.notes, oi.price_at_order, m.name as menu_name, m.image_url as menu_image
                    FROM orders o
                    LEFT JOIN order_items oi ON o.id = oi.order_id
                    LEFT JOIN menu m ON oi.menu_id = m.id
                    WHERE o.status != 'Selesai'
                    ORDER BY o.created_at ASC
                ");
                $stmt->execute();
            } else {
                // Return customer's orders
                $stmt = $pdo->prepare("
                    SELECT o.*, oi.menu_id, oi.qty, oi.notes, oi.price_at_order, m.name as menu_name, m.image_url as menu_image
                    FROM orders o
                    LEFT JOIN order_items oi ON o.id = oi.order_id
                    LEFT JOIN menu m ON oi.menu_id = m.id
                    WHERE o.username = ?
                    ORDER BY o.created_at DESC
                ");
                $stmt->execute([$user['username']]);
            }

            $orders = [];
            while ($row = $stmt->fetch()) {
                $orderId = $row['id'];
                if (!isset($orders[$orderId])) {
                    $orders[$orderId] = [
                        'id' => $row['id'],
                        'username' => $row['username'],
                        'table' => $row['table_number'],
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

            // Return as indexed array instead of associative array keys
            sendJsonResponse(array_values($orders));

        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'message' => 'Failed to fetch orders: ' . $e->getMessage()], 500);
        }
        break;

    case 'get_all':
        requireAdmin();
        try {
            // Fetch all orders for the sales history
            $stmt = $pdo->query("
                SELECT o.*, oi.menu_id, oi.qty, oi.notes, oi.price_at_order, m.name as menu_name, m.image_url as menu_image
                FROM orders o
                LEFT JOIN order_items oi ON o.id = oi.order_id
                LEFT JOIN menu m ON oi.menu_id = m.id
                ORDER BY o.created_at DESC
            ");

            $orders = [];
            while ($row = $stmt->fetch()) {
                $orderId = $row['id'];
                if (!isset($orders[$orderId])) {
                    $orders[$orderId] = [
                        'id' => $row['id'],
                        'username' => $row['username'],
                        'table' => $row['table_number'],
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
            sendJsonResponse(array_values($orders));
        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'message' => 'Failed to fetch transaction log: ' . $e->getMessage()], 500);
        }
        break;

    case 'update_status':
        requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            sendJsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $input = getJsonInput();
        $orderId = trim($input['id'] ?? '');

        if (empty($orderId)) {
            sendJsonResponse(['success' => false, 'message' => 'ID pesanan tidak boleh kosong.'], 400);
        }

        try {
            // Get current status
            $stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ?");
            $stmt->execute([$orderId]);
            $currentStatus = $stmt->fetchColumn();

            if (!$currentStatus) {
                sendJsonResponse(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
            }

            $newStatus = $currentStatus;
            if ($currentStatus === 'Pending') {
                $newStatus = 'Diproses';
            } elseif ($currentStatus === 'Diproses') {
                $newStatus = 'Selesai';
            }

            $stmtUpdate = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmtUpdate->execute([$newStatus, $orderId]);

            sendJsonResponse([
                'success' => true,
                'message' => 'Status pesanan berhasil diperbarui.',
                'newStatus' => $newStatus
            ]);

        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'message' => 'Failed to update order status: ' . $e->getMessage()], 500);
        }
        break;

    default:
        sendJsonResponse(['success' => false, 'message' => 'Action not found'], 404);
        break;
}
