<?php
$stmt = $db->prepare("
    SELECT t.*, g.name as game_name, p.name as package_name, p.amount, p.currency_name
    FROM transactions t
    JOIN games g ON t.game_id = g.id
    JOIN packages p ON t.package_id = p.id
    WHERE t.user_id = ?
    ORDER BY t.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$transactions = $stmt->fetchAll();
?>

<div class="container section">
  <div class="section-header">
    <div class="section-tag">📋 Riwayat</div>
    <h1 class="section-title">Riwayat Transaksi</h1>
    <p class="section-desc">Semua transaksi top up yang pernah kamu lakukan.</p>
  </div>

  <?php if (empty($transactions)): ?>
    <div class="card" style="text-align:center;padding:60px;">
      <div style="font-size:3.5rem;margin-bottom:16px;">📭</div>
      <h3 style="margin-bottom:8px;">Belum Ada Transaksi</h3>
      <p style="color:var(--text-muted);margin-bottom:24px;">Kamu belum pernah melakukan top up. Mulai sekarang!</p>
      <a href="index.php?page=games" class="btn btn-primary">🎮 Mulai Top Up</a>
    </div>
  <?php else: ?>
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Game</th>
            <th>Paket</th>
            <th>Total</th>
            <th>Metode</th>
            <th>Status</th>
            <th>Tanggal</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($transactions as $t): ?>
            <tr>
              <td style="font-family:monospace;font-size:0.8rem;"><?= htmlspecialchars($t['order_id']) ?></td>
              <td style="font-weight:500;"><?= htmlspecialchars($t['game_name']) ?></td>
              <td>
                <div style="font-size:0.9rem;"><?= htmlspecialchars($t['package_name']) ?></div>
                <div style="font-size:0.77rem;color:var(--text-muted);"><?= number_format($t['amount'], 0, ',', '.') ?> <?= htmlspecialchars($t['currency_name']) ?></div>
              </td>
              <td style="font-weight:700;color:var(--blue-400);">Rp <?= number_format($t['total_price'], 0, ',', '.') ?></td>
              <td style="font-size:0.88rem;"><?= htmlspecialchars($t['payment_method']) ?></td>
              <td><span class="badge badge-<?= $t['status'] ?>"><?= ucfirst($t['status']) ?></span></td>
              <td style="font-size:0.82rem;color:var(--text-muted);"><?= date('d M Y', strtotime($t['created_at'])) ?></td>
              <td>
                <a href="index.php?page=payment&order=<?= htmlspecialchars($t['order_id']) ?>" class="btn btn-ghost btn-sm">Detail →</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
