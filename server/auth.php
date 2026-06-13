<?php
/**
 * Heavoria Restaurant System - Authentication Endpoints
 * File: server/auth.php
 */

require_once 'config.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'session':
        $user = getCurrentUser();
        if ($user) {
            sendJsonResponse([
                'success' => true,
                'user' => [
                    'username' => $user['username'],
                    'role' => $user['role']
                ]
            ]);
        } else {
            sendJsonResponse([
                'success' => false,
                'message' => 'No active session'
            ]);
        }
        break;

    case 'login':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            sendJsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $input = getJsonInput();
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        if (empty($username) || empty($password)) {
            sendJsonResponse(['success' => false, 'message' => 'Username and password are required'], 400);
        }

        // Search user in database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(?)");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Note: Password checks should be password_verify() in real apps.
        // To maintain compatibility with existing database seeds/passwords, we support plain text match as well as hash verify.
        if ($user) {
            $passwordMatched = false;
            if ($password === $user['password']) {
                $passwordMatched = true;
            } elseif (password_verify($password, $user['password'])) {
                $passwordMatched = true;
            }

            if ($passwordMatched) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role']
                ];
                sendJsonResponse([
                    'success' => true,
                    'user' => [
                        'username' => $user['username'],
                        'role' => $user['role']
                    ]
                ]);
            }
        }

        sendJsonResponse(['success' => false, 'message' => 'Username atau password salah.'], 401);
        break;

    case 'register':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            sendJsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $input = getJsonInput();
        $username = trim($input['username'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $password = $input['password'] ?? '';

        if (empty($username) || empty($phone) || empty($password)) {
            sendJsonResponse(['success' => false, 'message' => 'All fields are required'], 400);
        }

        // Check if username exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE LOWER(username) = LOWER(?)");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            sendJsonResponse(['success' => false, 'message' => 'Username sudah terdaftar.'], 400);
        }

        // Insert new user
        // Using password_hash for secure storage
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, phone, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->execute([$username, $phone, $hashedPassword]);
            
            // Auto login after registration
            $userId = $pdo->lastInsertId();
            $_SESSION['user'] = [
                'id' => $userId,
                'username' => $username,
                'role' => 'user'
            ];

            sendJsonResponse([
                'success' => true,
                'user' => [
                    'username' => $username,
                    'role' => 'user'
                ]
            ]);
        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()], 500);
        }
        break;

    case 'logout':
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        sendJsonResponse(['success' => true, 'message' => 'Logged out successfully']);
        break;

    case 'reset_password':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            sendJsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $input = getJsonInput();
        $username = trim($input['username'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $newPassword = $input['newPassword'] ?? '';
        $confirmPassword = $input['confirmPassword'] ?? '';

        if (empty($username) || empty($phone) || empty($newPassword)) {
            sendJsonResponse(['success' => false, 'message' => 'All fields are required'], 400);
        }

        if ($newPassword !== $confirmPassword) {
            sendJsonResponse(['success' => false, 'message' => 'Password baru dan konfirmasi tidak cocok.'], 400);
        }

        // Verify username + phone
        $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(?) AND phone = ?");
        $stmt->execute([$username, $phone]);
        $user = $stmt->fetch();

        if (!$user) {
            sendJsonResponse(['success' => false, 'message' => 'Username atau nomor telepon tidak ditemukan.'], 404);
        }

        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $user['id']]);
            sendJsonResponse(['success' => true, 'message' => 'Password berhasil direset!']);
        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'message' => 'Failed to update password: ' . $e->getMessage()], 500);
        }
        break;

    default:
        sendJsonResponse(['success' => false, 'message' => 'Action not found'], 404);
        break;
}
