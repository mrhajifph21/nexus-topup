<?php
require_once 'config.php';

// =========================================
// TRANSACTION HANDLER
// =========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_order') {
        requireLogin();
        createOrder();
    } elseif ($action === 'update_status') {
        requireAdmin();
        updateTransactionStatus();
    }
}

function createOrder() {
    $gameId = (int)($_POST['game_id'] ?? 0);
    $packageId = (int)($_POST['package_id'] ?? 0);
    $gameUserId = sanitize($_POST['game_user_id'] ?? '');
    $gameServerId = sanitize($_POST['game_server_id'] ?? '');
    $nickname = sanitize($_POST['nickname'] ?? '');
    $paymentMethodId = (int)($_POST['payment_method_id'] ?? 0);

    if (!$gameId || !$packageId || !$gameUserId || !$paymentMethodId) {
        setFlash('error', 'Data tidak lengkap.');
        header('Location: ../index.php?page=checkout');
        exit;
    }

    $db = getDB();

    // Get package info
    $stmt = $db->prepare("SELECT p.*, g.name as game_name FROM packages p JOIN games g ON p.game_id = g.id WHERE p.id = ? AND p.game_id = ? AND p.is_active = 1");
    $stmt->execute([$packageId, $gameId]);
    $package = $stmt->fetch();

    if (!$package) {
        setFlash('error', 'Paket tidak ditemukan.');
        header('Location: ../index.php');
        exit;
    }

    // Get payment method fee
    $stmt = $db->prepare("SELECT * FROM payment_methods WHERE id = ? AND is_active = 1");
    $stmt->execute([$paymentMethodId]);
    $payment = $stmt->fetch();

    $fee = 0;
    if ($payment) {
        if ($payment['fee_type'] === 'fixed') {
            $fee = $payment['fee_value'];
        } else {
            $fee = $package['price'] * ($payment['fee_value'] / 100);
        }
    }

    $totalPrice = $package['price'] + $fee;
    $orderId = generateOrderId();

    $stmt = $db->prepare("INSERT INTO transactions (order_id, user_id, game_id, package_id, game_user_id, game_server_id, nickname, total_price, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([
        $orderId,
        $_SESSION['user_id'],
        $gameId,
        $packageId,
        $gameUserId,
        $gameServerId,
        $nickname,
        $totalPrice,
        $payment['name'] ?? 'Unknown'
    ]);

    // Redirect to payment page
    header('Location: ../index.php?page=payment&order=' . $orderId);
    exit;
}

function updateTransactionStatus() {
    $transactionId = (int)($_POST['transaction_id'] ?? 0);
    $status = sanitize($_POST['status'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');

    $validStatuses = ['pending', 'processing', 'success', 'failed', 'refunded'];
    if (!in_array($status, $validStatuses)) {
        echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
        exit;
    }

    $db = getDB();
    $stmt = $db->prepare("UPDATE transactions SET status = ?, notes = ? WHERE id = ?");
    $stmt->execute([$status, $notes, $transactionId]);

    echo json_encode(['success' => true, 'message' => 'Status berhasil diupdate']);
    exit;
}

// API: Get transactions for admin
function getTransactions($filters = []) {
    $db = getDB();
    $where = [];
    $params = [];

    if (!empty($filters['status'])) {
        $where[] = 't.status = ?';
        $params[] = $filters['status'];
    }
    if (!empty($filters['search'])) {
        $where[] = '(t.order_id LIKE ? OR u.username LIKE ?)';
        $params[] = '%' . $filters['search'] . '%';
        $params[] = '%' . $filters['search'] . '%';
    }

    $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $stmt = $db->prepare("
        SELECT t.*, u.username, g.name as game_name, p.name as package_name
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        JOIN games g ON t.game_id = g.id
        JOIN packages p ON t.package_id = p.id
        $whereSQL
        ORDER BY t.created_at DESC
        LIMIT 100
    ");
    $stmt->execute($params);
    return $stmt->fetchAll();
}
?>
