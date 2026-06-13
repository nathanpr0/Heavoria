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
