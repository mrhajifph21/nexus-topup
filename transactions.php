<?php
require_once '../php/config.php';
requireAdmin();

$db = getDB();
$statusFilter = sanitize($_GET['status'] ?? '');
$search = sanitize($_GET['search'] ?? '');

$where = [];
$params = [];
if ($statusFilter) { $where[] = 't.status = ?'; $params[] = $statusFilter; }
if ($search) { $where[] = '(t.order_id LIKE ? OR u.username LIKE ? OR g.name LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $db->prepare("
    SELECT t.*, u.username, g.name as game_name, p.name as package_name, p.amount, p.currency_name
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    JOIN games g ON t.game_id = g.id
    JOIN packages p ON t.package_id = p.id
    $whereSQL
    ORDER BY t.created_at DESC LIMIT 200
");
$stmt->execute($params);
$transactions = $stmt->fetchAll();

$page = 'transactions';
$pageTitle = 'Transaksi';
$pageSubtitle = 'Monitor & kelola semua transaksi';
include 'layout.php';
?>

<!-- Filters -->
<div class="filters-bar">
  <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;width:100%;">
    <div class="search-input-wrapper">
      <span class="search-icon">🔍</span>
      <input type="text" name="search" class="form-control" placeholder="Cari order ID, user..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <select name="status" class="form-control">
      <option value="">Semua Status</option>
      <?php foreach (['pending','processing','success','failed','refunded'] as $s): ?>
        <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary">Filter</button>
    <?php if ($statusFilter || $search): ?>
      <a href="transactions.php" class="btn btn-ghost">Reset</a>
    <?php endif; ?>
  </form>
</div>

<div class="admin-table-wrapper">
  <div class="admin-table-header">
    <div class="admin-table-title">📋 Semua Transaksi <span style="font-size:0.8rem;color:var(--text-muted);font-weight:400;">(<?= count($transactions) ?> records)</span></div>
  </div>
  <div class="table-wrapper" style="border:none;border-radius:0;">
    <table>
      <thead>
        <tr>
          <th>Order ID</th>
          <th>User</th>
          <th>Game</th>
          <th>Paket</th>
          <th>User Game ID</th>
          <th>Total</th>
          <th>Metode</th>
          <th>Status</th>
          <th>Tanggal</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($transactions)): ?>
          <tr><td colspan="10" style="text-align:center;color:var(--text-muted);padding:40px;">Tidak ada data</td></tr>
        <?php endif; ?>
        <?php foreach ($transactions as $t): ?>
          <tr>
            <td style="font-family:monospace;font-size:0.75rem;max-width:140px;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($t['order_id']) ?></td>
            <td><?= htmlspecialchars($t['username']) ?></td>
            <td><?= htmlspecialchars($t['game_name']) ?></td>
            <td>
              <div style="font-size:0.88rem;"><?= htmlspecialchars($t['package_name']) ?></div>
              <div style="font-size:0.75rem;color:var(--text-muted);"><?= number_format($t['amount'], 0, ',', '.') ?> <?= htmlspecialchars($t['currency_name']) ?></div>
            </td>
            <td style="font-family:monospace;font-size:0.82rem;"><?= htmlspecialchars($t['game_user_id']) ?><?= $t['game_server_id'] ? '<br><span style="color:var(--text-muted);">(' . htmlspecialchars($t['game_server_id']) . ')</span>' : '' ?></td>
            <td style="font-weight:700;color:var(--blue-400);">Rp <?= number_format($t['total_price'], 0, ',', '.') ?></td>
            <td style="font-size:0.85rem;"><?= htmlspecialchars($t['payment_method']) ?></td>
            <td><span class="badge badge-<?= $t['status'] ?>"><?= ucfirst($t['status']) ?></span></td>
            <td style="font-size:0.78rem;color:var(--text-muted);white-space:nowrap;"><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></td>
            <td>
              <button onclick="updateStatus(<?= $t['id'] ?>, '<?= $t['status'] ?>')" class="btn btn-primary btn-sm">Update</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'layout_end.php'; ?>
