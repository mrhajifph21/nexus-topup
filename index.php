<?php
require_once '../php/config.php';
requireAdmin();

$db = getDB();

// Stats
$stats = [];
$stmt = $db->query("SELECT COUNT(*) as c FROM transactions");
$stats['total_orders'] = $stmt->fetch()['c'];

$stmt = $db->query("SELECT SUM(total_price) as total FROM transactions WHERE status = 'success'");
$stats['revenue'] = $stmt->fetch()['total'] ?? 0;

$stmt = $db->query("SELECT COUNT(*) as c FROM users WHERE role = 'user'");
$stats['users'] = $stmt->fetch()['c'];

$stmt = $db->query("SELECT COUNT(*) as c FROM transactions WHERE status = 'pending'");
$stats['pending'] = $stmt->fetch()['c'];

// Recent transactions
$stmt = $db->query("
    SELECT t.*, u.username, g.name as game_name, p.name as package_name
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    JOIN games g ON t.game_id = g.id
    JOIN packages p ON t.package_id = p.id
    ORDER BY t.created_at DESC LIMIT 8
");
$recentTx = $stmt->fetchAll();

// Monthly revenue (last 7 days)
$stmt = $db->query("
    SELECT DATE(created_at) as d, SUM(total_price) as revenue
    FROM transactions WHERE status = 'success' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at) ORDER BY d
");
$chartData = $stmt->fetchAll();

$page = 'dashboard';
$pageTitle = 'Dashboard';
include 'layout.php';
?>

<!-- STATS -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-card-label">Total Pesanan</div>
    <div class="stat-card-value"><?= number_format($stats['total_orders'], 0, ',', '.') ?></div>
    <div class="stat-card-sub">Semua waktu</div>
    <div class="stat-card-icon">📦</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-label">Total Pendapatan</div>
    <div class="stat-card-value">Rp <?= number_format($stats['revenue'], 0, ',', '.') ?></div>
    <div class="stat-card-sub">Transaksi sukses</div>
    <div class="stat-card-icon">💰</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-label">Total Pengguna</div>
    <div class="stat-card-value"><?= number_format($stats['users'], 0, ',', '.') ?></div>
    <div class="stat-card-sub">Pengguna terdaftar</div>
    <div class="stat-card-icon">👥</div>
  </div>
  <div class="stat-card">
    <div class="stat-card-label">Menunggu Proses</div>
    <div class="stat-card-value" style="color:#fbbf24;"><?= $stats['pending'] ?></div>
    <div class="stat-card-sub">Perlu tindakan</div>
    <div class="stat-card-icon">⏳</div>
  </div>
</div>

<!-- CHART + RECENT -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">
  <!-- Revenue chart -->
  <div class="card">
    <div style="font-family:var(--font-display);font-size:1rem;font-weight:700;margin-bottom:20px;">📈 Pendapatan 7 Hari Terakhir</div>
    <?php if (!empty($chartData)): ?>
      <?php
      $maxRev = max(array_column($chartData, 'revenue'));
      ?>
      <div style="display:flex; align-items:flex-end; gap:8px; height:120px; padding:0 4px;">
        <?php foreach ($chartData as $d): ?>
          <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;">
            <div style="
              width:100%;
              height:<?= $maxRev > 0 ? round(($d['revenue']/$maxRev)*100) : 4 ?>%;
              background:linear-gradient(to top,#1d4ed8,#60a5fa);
              border-radius:4px 4px 0 0;
              min-height:4px;
            "></div>
            <span style="font-size:0.62rem;color:var(--text-muted);"><?= date('d/m', strtotime($d['d'])) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div style="text-align:center;color:var(--text-muted);padding:40px 0;font-size:0.9rem;">Belum ada data</div>
    <?php endif; ?>
  </div>

  <!-- Top Games -->
  <div class="card">
    <div style="font-family:var(--font-display);font-size:1rem;font-weight:700;margin-bottom:20px;">🎮 Game Terpopuler</div>
    <?php
    $stmt = $db->query("
        SELECT g.name, COUNT(t.id) as total
        FROM transactions t JOIN games g ON t.game_id = g.id
        GROUP BY g.id ORDER BY total DESC LIMIT 5
    ");
    $topGames = $stmt->fetchAll();
    $maxTotal = !empty($topGames) ? $topGames[0]['total'] : 1;
    ?>
    <div style="display:flex;flex-direction:column;gap:12px;">
      <?php foreach ($topGames as $g): ?>
        <div>
          <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
            <span style="font-size:0.85rem;"><?= htmlspecialchars($g['name']) ?></span>
            <span style="font-size:0.82rem;color:var(--text-muted);"><?= $g['total'] ?>x</span>
          </div>
          <div style="height:6px;background:rgba(255,255,255,0.06);border-radius:3px;overflow:hidden;">
            <div style="height:100%;width:<?= round(($g['total']/$maxTotal)*100) ?>%;background:var(--gradient-blue);border-radius:3px;"></div>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($topGames)): ?>
        <div style="color:var(--text-muted);font-size:0.88rem;">Belum ada data</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- RECENT TRANSACTIONS -->
<div class="admin-table-wrapper">
  <div class="admin-table-header">
    <div class="admin-table-title">🕐 Transaksi Terbaru</div>
    <a href="transactions.php" class="btn btn-outline btn-sm">Lihat Semua</a>
  </div>
  <div class="table-wrapper" style="border:none;border-radius:0;">
    <table>
      <thead>
        <tr>
          <th>Order ID</th>
          <th>User</th>
          <th>Game</th>
          <th>Paket</th>
          <th>Total</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentTx as $t): ?>
          <tr>
            <td style="font-family:monospace;font-size:0.78rem;"><?= htmlspecialchars($t['order_id']) ?></td>
            <td><?= htmlspecialchars($t['username']) ?></td>
            <td><?= htmlspecialchars($t['game_name']) ?></td>
            <td><?= htmlspecialchars($t['package_name']) ?></td>
            <td style="font-weight:700;">Rp <?= number_format($t['total_price'], 0, ',', '.') ?></td>
            <td><span class="badge badge-<?= $t['status'] ?>"><?= ucfirst($t['status']) ?></span></td>
            <td>
              <button onclick="updateStatus(<?= $t['id'] ?>, '<?= $t['status'] ?>')" class="btn btn-ghost btn-sm">Update</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'layout_end.php'; ?>
