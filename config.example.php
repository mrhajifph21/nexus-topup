<?php
// =========================================
// NEXUS TOP UP - KONFIGURASI
// Salin file ini jadi config.php dan isi sesuai setting lokal kamu
// JANGAN push config.php ke GitHub!
// =========================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // ← Ganti dengan username MySQL kamu
define('DB_PASS', '');            // ← Ganti dengan password MySQL kamu
define('DB_NAME', 'nexus_topup');

define('SITE_NAME', 'Nexus Top Up');
define('SITE_URL', 'http://localhost/topup-game'); // ← Sesuaikan dengan URL lokal kamu
define('ADMIN_EMAIL', 'admin@nexus.com');

// Session
session_start();

// Database Connection
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            die("<div style='font-family:sans-serif;padding:40px;text-align:center;'>
                <h2>❌ Koneksi Database Gagal</h2>
                <p>Pastikan MySQL sudah berjalan dan setting di <code>php/config.php</code> benar.</p>
                <code>" . $e->getMessage() . "</code>
            </div>");
        }
    }
    return $pdo;
}

function isLoggedIn() { return isset($_SESSION['user_id']); }
function isAdmin() { return isset($_SESSION['role']) && $_SESSION['role'] === 'admin'; }

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/index.php?page=login');
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/index.php');
        exit;
    }
}

function generateOrderId() { return 'NXS-' . strtoupper(substr(md5(uniqid()), 0, 8)) . '-' . time(); }
function formatRupiah($amount) { return 'Rp ' . number_format($amount, 0, ',', '.'); }
function sanitize($input) { return htmlspecialchars(strip_tags(trim($input))); }
function setFlash($type, $message) { $_SESSION['flash'] = ['type' => $type, 'message' => $message]; }
function getFlash() {
    if (isset($_SESSION['flash'])) { $flash = $_SESSION['flash']; unset($_SESSION['flash']); return $flash; }
    return null;
}
?>
