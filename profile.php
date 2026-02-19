<?php
require_once 'config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?page=profile');
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'update_profile') {
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');

    if (empty($username) || empty($email)) {
        setFlash('error', 'Username dan email harus diisi.');
        header('Location: ../index.php?page=profile');
        exit;
    }

    $db = getDB();
    
    // Check duplicate excluding current user
    $stmt = $db->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?");
    $stmt->execute([$email, $username, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        setFlash('error', 'Email atau username sudah digunakan.');
        header('Location: ../index.php?page=profile');
        exit;
    }

    $stmt = $db->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
    $stmt->execute([$username, $email, $_SESSION['user_id']]);

    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;

    setFlash('success', 'Profil berhasil diupdate.');
    header('Location: ../index.php?page=profile');
    exit;
}

if ($action === 'change_password') {
    $oldPw = $_POST['old_password'] ?? '';
    $newPw = $_POST['new_password'] ?? '';
    $confirmPw = $_POST['confirm_password'] ?? '';

    $db = getDB();
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!password_verify($oldPw, $user['password'])) {
        setFlash('error', 'Password lama tidak benar.');
        header('Location: ../index.php?page=profile');
        exit;
    }

    if ($newPw !== $confirmPw) {
        setFlash('error', 'Konfirmasi password tidak cocok.');
        header('Location: ../index.php?page=profile');
        exit;
    }

    if (strlen($newPw) < 8) {
        setFlash('error', 'Password minimal 8 karakter.');
        header('Location: ../index.php?page=profile');
        exit;
    }

    $hashed = password_hash($newPw, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed, $_SESSION['user_id']]);

    setFlash('success', 'Password berhasil diganti.');
    header('Location: ../index.php?page=profile');
    exit;
}
?>
