<?php
/**
 * Heavoria Restaurant System - Authentication Endpoints (OOP Version)
 * File: server/auth.php
 * 
 * File ini menangani proses otentikasi pengguna (customer & admin) seperti
 * pendaftaran (register), login, logout, verifikasi email, dan atur ulang kata sandi (reset password).
 * Menggunakan pendekatan OOP (Object-Oriented Programming) dengan dokumentasi lengkap dalam Bahasa Indonesia.
 */

require_once 'config.php';

class AuthController {
    private $pdo;

    /**
     * Constructor untuk menerima koneksi database PDO
     * @param PDO $pdo Koneksi database aktif
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Memeriksa apakah session user aktif di server
     * Endpoint: action=session
     */
    public function checkSession() {
        $user = getCurrentUser(); // Fungsi helper dari config.php
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
                'message' => 'Tidak ada session aktif'
            ]);
        }
    }

    /**
     * Proses Login Pengguna
     * Endpoint: action=login (POST)
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            sendJsonResponse(['success' => false, 'message' => 'Metode request tidak valid'], 405);
        }

        $input = getJsonInput();
        $username = trim($input['username'] ?? ''); // Input bisa berupa Gmail, Username, atau No Telp
        $password = $input['password'] ?? '';

        if (empty($username) || empty($password)) {
            sendJsonResponse(['success' => false, 'message' => 'Username/Email/No Telepon dan Password wajib diisi'], 400);
        }

        // Mencari pengguna berdasarkan username, email, atau phone
        // LOWER digunakan untuk memastikan pencarian bersifat Case-Insensitive (tidak sensitif huruf besar/kecil)
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?) OR phone = ?");
        $stmt->execute([$username, $username, $username]);
        $user = $stmt->fetch();

        if ($user) {
            $passwordMatched = false;
            
            // Kompatibilitas ganda: Mendukung perbandingan teks biasa (untuk database seeder awal)
            // serta password_verify() berbasis bcrypt untuk registrasi modern/aman.
            if ($password === $user['password']) {
                $passwordMatched = true;
            } elseif (password_verify($password, $user['password'])) {
                $passwordMatched = true;
            }

            if ($passwordMatched) {
                // Menyimpan data kredensial penting ke session server
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'] ?? '',
                    'phone' => $user['phone'] ?? '',
                    'role' => $user['role']
                ];

                // Memperbarui kolom last_seen untuk mencatat aktivitas login terbaru
                $stmtSeen = $this->pdo->prepare("UPDATE users SET last_seen = NOW() WHERE id = ?");
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

        // Jika username atau password tidak cocok
        sendJsonResponse(['success' => false, 'message' => 'Username, Gmail, Nomor Telepon, atau Password salah.'], 401);
    }

    /**
     * Proses Pendaftaran (Sign In / Register) Pengguna Baru
     * Endpoint: action=register (POST)
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            sendJsonResponse(['success' => false, 'message' => 'Metode request tidak valid'], 405);
        }

        $input = getJsonInput();
        $username = trim($input['username'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';

        if (empty($username) || empty($phone) || empty($email) || empty($password)) {
            sendJsonResponse(['success' => false, 'message' => 'Semua kolom pendaftaran wajib diisi'], 400);
        }

        // Validasi format Gmail menggunakan Regular Expression sederhana
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with(strtolower($email), '@gmail.com')) {
            sendJsonResponse(['success' => false, 'message' => 'Gmail address tidak valid. Gunakan alamat @gmail.com yang benar.'], 400);
        }

        // Memeriksa duplikasi data pengguna (agar username, email, dan no telp bersifat unik)
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?) OR phone = ?");
        $stmt->execute([$username, $email, $phone]);
        if ($stmt->fetchColumn() > 0) {
            sendJsonResponse(['success' => false, 'message' => 'Username, Gmail, atau nomor telepon sudah terdaftar.'], 400);
        }

        // Mengamankan password menggunakan fungsi hash standard industri (Bcrypt)
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $this->pdo->prepare("INSERT INTO users (username, email, phone, password, role, last_seen) VALUES (?, ?, ?, ?, 'user', NOW())");
            $stmt->execute([$username, $email, $phone, $hashedPassword]);
            
            sendJsonResponse([
                'success' => true,
                'message' => 'Akun berhasil dibuat. Silakan login.'
            ]);
        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'message' => 'Gagal mendaftarkan akun: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Proses Keluar (Logout) Akun
     * Endpoint: action=logout
     */
    public function logout() {
        $_SESSION = []; // Mengosongkan data session di RAM server
        
        // Menghapus cookie session id di browser agar session benar-benar berakhir
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy(); // Menghancurkan file session di server
        sendJsonResponse(['success' => true, 'message' => 'Berhasil logout dan menghapus session']);
    }

    /**
     * Memeriksa ketersediaan Gmail untuk fitur Lupa Password
     * Endpoint: action=check_gmail (POST)
     */
    public function checkGmail() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            sendJsonResponse(['success' => false, 'message' => 'Metode request tidak valid'], 405);
        }

        $input = getJsonInput();
        $email = trim($input['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with(strtolower($email), '@gmail.com')) {
            sendJsonResponse(['success' => false, 'message' => 'Format Gmail address tidak valid.'], 400);
        }

        // Memastikan email terdaftar dan memiliki peran sebagai customer biasa ('user')
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE LOWER(email) = LOWER(?) AND role = 'user'");
        $stmt->execute([$email]);
        if ((int)$stmt->fetchColumn() === 0) {
            sendJsonResponse(['success' => false, 'message' => 'Gmail address tidak terdaftar di sistem.'], 404);
        }

        sendJsonResponse(['success' => true, 'message' => 'Email valid. Kode verifikasi OTP disimulasikan terkirim.']);
    }

    /**
     * Mengatur Ulang Password Akun Pengguna
     * Endpoint: action=reset_password (POST)
     */
    public function resetPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            sendJsonResponse(['success' => false, 'message' => 'Metode request tidak valid'], 405);
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

        // Skenario 1: Reset menggunakan email (fitur lupa password gmail)
        if (!empty($email)) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with(strtolower($email), '@gmail.com')) {
                sendJsonResponse(['success' => false, 'message' => 'Gmail address tidak valid.'], 400);
            }
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(?) AND role = 'user'");
            $stmt->execute([$email]);
        } 
        // Skenario 2: Reset menggunakan username dan no hp terdaftar
        else {
            if (empty($username) || empty($phone)) {
                sendJsonResponse(['success' => false, 'message' => 'Nama pengguna dan nomor hp wajib diisi jika email kosong.'], 400);
            }
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(?) AND phone = ?");
            $stmt->execute([$username, $phone]);
        }

        $user = $stmt->fetch();
        if (!$user) {
            sendJsonResponse(['success' => false, 'message' => 'Identitas akun tidak cocok atau tidak ditemukan.'], 404);
        }

        // Melakukan enkripsi password baru lalu menyimpannya ke database
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $user['id']]);
            sendJsonResponse(['success' => true, 'message' => 'Kata sandi berhasil diganti! Silakan login.']);
        } catch (PDOException $e) {
            sendJsonResponse(['success' => false, 'message' => 'Gagal memperbarui kata sandi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mendapatkan daftar customer (List Client) untuk panel administrator
     * Endpoint: action=clients (GET)
     */
    public function clients() {
        requireAdmin(); // Pengaman: Hanya admin yang boleh mengakses daftar pengguna

        try {
            // Mengambil semua user dengan role 'user' (customer)
            $stmt = $this->pdo->query("
                SELECT username, email, phone, last_seen
                FROM users
                WHERE role = 'user'
                ORDER BY created_at DESC
            ");
            
            $clients = [];
            while ($row = $stmt->fetch()) {
                // Menentukan status online/offline (jika aktivitas terakhir < 15 menit, dianggap online)
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
            sendJsonResponse(['success' => false, 'message' => 'Gagal mengambil data pelanggan: ' . $e->getMessage()], 500);
        }
    }
}

// Inisialisasi controller dan routing action
$authController = new AuthController($pdo);

// Mengambil parameter action dari URL
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'session':
        $authController->checkSession();
        break;
    case 'login':
        $authController->login();
        break;
    case 'register':
        $authController->register();
        break;
    case 'logout':
        $authController->logout();
        break;
    case 'check_gmail':
        $authController->checkGmail();
        break;
    case 'reset_password':
        $authController->resetPassword();
        break;
    case 'clients':
        $authController->clients();
        break;
    default:
        sendJsonResponse(['success' => false, 'message' => 'Aksi autentikasi tidak dikenal'], 404);
        break;
}
