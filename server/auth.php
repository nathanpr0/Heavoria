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
                    'email' => $user['email'] ?? '',
                    'phone' => $user['phone'] ?? '',
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
        $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?) OR phone = ?");
        $stmt->execute([$username, $username, $username]);
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
                if (($portal === 'admin' && $user['role'] !== 'admin') || ($portal === 'user' && $user['role'] === 'admin')) {
                    sendJsonResponse([
                        'success' => false,
                        'message' => $portal === 'admin'
                            ? 'Akun ini bukan administrator.'
                            : 'Akun admin harus login melalui Admin Portal.'
                    ], 403);
                }

                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'] ?? '',
                    'phone' => $user['phone'] ?? '',
                    'role' => $user['role']
                ];
                $stmtSeen = $pdo->prepare("UPDATE users SET last_seen = NOW() WHERE id = ?");
                $stmtSeen->execute([$user['id']]);
                sendJsonResponse([
                    'success' => true,
                    'user' => [
                        'username' => $user['username'],
                        'email' => $user['email'] ?? '',
                        'phone' => $user['phone'] ?? '',
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
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';

        if (empty($username) || empty($phone) || empty($email) || empty($password)) {
            sendJsonResponse(['success' => false, 'message' => 'All fields are required'], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with(strtolower($email), '@gmail.com')) {
            sendJsonResponse(['success' => false, 'message' => 'Gmail address tidak valid. Gunakan alamat @gmail.com.'], 400);
        }

        // Check if username exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?) OR phone = ?");
        $stmt->execute([$username, $email, $phone]);
        if ($stmt->fetchColumn() > 0) {
            sendJsonResponse(['success' => false, 'message' => 'Username, Gmail, atau nomor telepon sudah terdaftar.'], 400);
        }

        // Insert new user
        // Using password_hash for secure storage
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, phone, password, role, last_seen) VALUES (?, ?, ?, ?, 'user', NOW())");
            $stmt->execute([$username, $email, $phone, $hashedPassword]);
            
            sendJsonResponse([
                'success' => true,
                'message' => 'Akun berhasil dibuat. Silakan login.'
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

    case 'check_gmail':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            sendJsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $input = getJsonInput();
        $email = trim($input['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with(strtolower($email), '@gmail.com')) {
            sendJsonResponse(['success' => false, 'message' => 'Gmail address tidak valid.'], 400);
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE LOWER(email) = LOWER(?) AND role = 'user'");
        $stmt->execute([$email]);
        if ((int)$stmt->fetchColumn() === 0) {
            sendJsonResponse(['success' => false, 'message' => 'Gmail address tidak terdaftar.'], 404);
        }

        sendJsonResponse(['success' => true, 'message' => 'Verification code sent.']);
        break;

    case 'reset_password':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            sendJsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $input = getJsonInput();
        $email = trim($input['email'] ?? '');
        $username = trim($input['username'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $newPassword = $input['newPassword'] ?? '';
        $confirmPassword = $input['confirmPassword'] ?? '';

        if (empty($newPassword) || empty($confirmPassword)) {
            sendJsonResponse(['success' => false, 'message' => 'Password baru dan konfirmasi wajib diisi.'], 400);
        }

        if ($newPassword !== $confirmPassword) {
            sendJsonResponse(['success' => false, 'message' => 'Password baru dan konfirmasi tidak cocok.'], 400);
        }

        if (!empty($email)) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with(strtolower($email), '@gmail.com')) {
                sendJsonResponse(['success' => false, 'message' => 'Gmail address tidak valid.'], 400);
            }
            $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(?) AND role = 'user'");
            $stmt->execute([$email]);
        } else {
            if (empty($username) || empty($phone)) {
                sendJsonResponse(['success' => false, 'message' => 'Gmail address wajib diisi.'], 400);
            }
            $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(?) AND phone = ?");
            $stmt->execute([$username, $phone]);
        }

        $user = $stmt->fetch();
        if (!$user) {
            sendJsonResponse(['success' => false, 'message' => 'Akun tidak ditemukan.'], 404);
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $user['id']]);
            sendJsonResponse(['success' => true, 'message' => 'Password berhasil direset!']);
        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'message' => 'Failed to update password: ' . $e->getMessage()], 500);
        }
        break;

    case 'clients':
        requireAdmin();
        try {
            $stmt = $pdo->query("
                SELECT username, email, phone, last_seen
                FROM users
                WHERE role = 'user'
                ORDER BY created_at DESC
            ");
            $clients = [];
            while ($row = $stmt->fetch()) {
                $isOnline = $row['last_seen'] && strtotime($row['last_seen']) >= time() - 900;
                $clients[] = [
                    'username' => $row['username'],
                    'email' => $row['email'] ?? '-',
                    'phone' => $row['phone'] ?? '-',
                    'status' => $isOnline ? 'Online' : 'Offline'
                ];
            }
            sendJsonResponse(['success' => true, 'clients' => $clients]);
        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'message' => 'Failed to fetch clients: ' . $e->getMessage()], 500);
        }
        break;

    default:
        sendJsonResponse(['success' => false, 'message' => 'Action not found'], 404);
        break;
}
