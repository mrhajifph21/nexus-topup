<?php
require_once 'config.php';

// =========================================
// AUTH HANDLER
// =========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        handleLogin();
    } elseif ($action === 'register') {
        handleRegister();
    } elseif ($action === 'logout') {
        handleLogout();
    }
}

function handleLogin() {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        setFlash('error', 'Email dan password harus diisi.');
        header('Location: ../index.php?page=login');
        exit;
    }

    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header('Location: ../admin/index.php');
        } else {
            header('Location: ../index.php');
        }
        exit;
    } else {
        setFlash('error', 'Email atau password salah.');
        header('Location: ../index.php?page=login');
        exit;
    }
}

function handleRegister() {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        setFlash('error', 'Semua field harus diisi.');
        header('Location: ../index.php?page=register');
        exit;
    }

    if ($password !== $confirmPass) {
        setFlash('error', 'Password tidak cocok.');
        header('Location: ../index.php?page=register');
        exit;
    }

    if (strlen($password) < 8) {
        setFlash('error', 'Password minimal 8 karakter.');
        header('Location: ../index.php?page=register');
        exit;
    }

    $db = getDB();

    // Check duplicate
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    if ($stmt->fetch()) {
        setFlash('error', 'Email atau username sudah digunakan.');
        header('Location: ../index.php?page=register');
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $hashedPassword]);

    setFlash('success', 'Akun berhasil dibuat! Silakan login.');
    header('Location: ../index.php?page=login');
    exit;
}

function handleLogout() {
    session_destroy();
    header('Location: ../index.php');
    exit;
}
?>
