<?php
/**
 * Heavoria Restaurant System - Analytics & Stats API
 * File: server/stats.php
 */

require_once 'config.php';

requireAdmin();

try {
    // 1. Calculate Total Revenue (Completed Orders only)
    $stmtRevenue = $pdo->query("SELECT SUM(total) FROM orders WHERE status = 'Selesai'");
    $totalRevenue = (int)$stmtRevenue->fetchColumn();

    // 2. Count Total Orders
    $stmtOrdersCount = $pdo->query("SELECT COUNT(*) FROM orders");
    $totalOrders = (int)$stmtOrdersCount->fetchColumn();

    // 3. Find Popular Menu Item
    $stmtPopular = $pdo->query("
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

    // 4. Calculate Category Performance
    $stmtCategory = $pdo->query("
        SELECT m.category, SUM(oi.price_at_order * oi.qty) as revenue
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        JOIN menu m ON oi.menu_id = m.id
        WHERE o.status = 'Selesai'
        GROUP BY m.category
    ");

    $categoryTotals = [
        'sushi' => 0,
        'cake' => 0,
        'drink' => 0
    ];

    while ($row = $stmtCategory->fetch()) {
        $cat = $row['category'];
        if (isset($categoryTotals[$cat])) {
            $categoryTotals[$cat] = (int)$row['revenue'];
        }
    }

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
    sendJsonResponse(['success' => false, 'message' => 'Failed to calculate statistics: ' . $e->getMessage()], 500);
}
