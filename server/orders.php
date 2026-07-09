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
        
        $items = $input['items'] ?? [];
        $method = trim($input['method'] ?? 'QRIS');
        $fulfillmentType = trim($input['fulfillmentType'] ?? 'pickup');
        $distanceKm = max(0, (float)($input['distanceKm'] ?? 0));
        $addressNote = trim($input['addressNote'] ?? '');

        if (empty($items)) {
            sendJsonResponse(['success' => false, 'message' => 'Daftar pesanan tidak boleh kosong.'], 400);
        }

        // Validate values
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += (int)$item['price'] * (int)$item['qty'];
        }
        $tax = 0;
        $service = 0;
        $deliveryFee = $fulfillmentType === 'delivery' ? (int)ceil($distanceKm * 4000) : 0;
        $total = $subtotal + $deliveryFee;

        $orderId = "ORD-" . substr(time(), -6) . rand(10, 99);

        try {
            $pdo->beginTransaction();

            // Insert into orders table
            $stmt = $pdo->prepare("
                INSERT INTO orders
                    (id, username, table_number, subtotal, tax, service_charge, delivery_fee, distance_km, fulfillment_type, address_note, total, payment_method, status)
                VALUES
                    (?, ?, 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Menunggu Verifikasi')
            ");
            $stmt->execute([$orderId, $user['username'], $subtotal, $tax, $service, $deliveryFee, $distanceKm, $fulfillmentType, $addressNote, $total, $method]);

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
                    'table' => 0,
                    'fulfillmentType' => $fulfillmentType,
                    'distanceKm' => $distanceKm,
                    'deliveryFee' => $deliveryFee,
                    'addressNote' => $addressNote,
                    'pickupDate' => null,
                    'items' => $items,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'service' => $service,
                    'total' => $total,
                    'method' => $method,
                    'status' => 'Menunggu Verifikasi',
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
                    WHERE o.status NOT IN ('Selesai', 'Ditolak')
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
            $requestedStatus = trim($input['status'] ?? '');
            $reason = trim($input['reason'] ?? '');

            // Get current status
            $stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ?");
            $stmt->execute([$orderId]);
            $currentStatus = $stmt->fetchColumn();

            if (!$currentStatus) {
                sendJsonResponse(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
            }

            $newStatus = $currentStatus;
            if ($requestedStatus !== '') {
                $allowed = ['Dikonfirmasi', 'Ditolak', 'Siap Diambil', 'Selesai'];
                if (!in_array($requestedStatus, $allowed, true)) {
                    sendJsonResponse(['success' => false, 'message' => 'Status tujuan tidak valid.'], 400);
                }
                $newStatus = $requestedStatus;
            } elseif ($currentStatus === 'Menunggu Verifikasi') {
                $newStatus = 'Dikonfirmasi';
            } elseif ($currentStatus === 'Dikonfirmasi') {
                $newStatus = 'Siap Diambil';
            } elseif ($currentStatus === 'Siap Diambil') {
                $newStatus = 'Selesai';
            }

            $pickupDate = null;
            if ($newStatus === 'Siap Diambil') {
                $today = new DateTime('today');
                $pickupDate = clone $today;
                if ($pickupDate->format('N') !== '1') {
                    $pickupDate->modify('next monday');
                }
            }

            $stmtUpdate = $pdo->prepare("
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
            sendJsonResponse(['success' => false, 'message' => 'Failed to update order status: ' . $e->getMessage()], 500);
        }
        break;

    default:
        sendJsonResponse(['success' => false, 'message' => 'Action not found'], 404);
        break;
}
