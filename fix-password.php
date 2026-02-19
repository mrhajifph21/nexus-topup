<?php
require_once 'php/config.php';
$db = getDB();

$newPassword = "admin123"; // ← password yang mau kamu pakai

$hash = password_hash($newPassword, PASSWORD_BCRYPT);

$stmt = $db->prepare("UPDATE users SET password = ? WHERE email = 'admin@nexus.com'");
$stmt->execute([$hash]);

echo "Berhasil! Hash: " . $hash;
echo "<br>Sekarang login dengan password: " . $newPassword;
?>