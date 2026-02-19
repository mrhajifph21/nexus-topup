<?php
$orderId = sanitize($_GET['order'] ?? '');

if (!$orderId) {
    header('Location: index.php');
    exit;
}

$stmt = $db->prepare("
    SELECT t.*, g.name as game_name, p.name as package_name, p.amount, p.currency_name, p.bonus
    FROM transactions t
    JOIN games g ON t.game_id = g.id
    JOIN packages p ON t.package_id = p.id
    WHERE t.order_id = ? AND t.user_id = ?
");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$transaction = $stmt->fetch();

if (!$transaction) {
    header('Location: index.php');
    exit;
}

$statusColors = [
    'pending' => '#fbbf24',
    'processing' => '#60a5fa',
    'success' => '#4ade80',
    'failed' => '#f87171',
    'refunded' => '#c084fc',
];
$color = $statusColors[$transaction['status']] ?? '#60a5fa';
?>

<div class="container section">
  <div style="max-width: 600px; margin: 0 auto;">
    
    <!-- Status Card -->
    <div style="
      background: var(--gradient-card);
      border: 1px solid var(--dark-border);
      border-radius: var(--radius-xl);
      padding: 40px 32px;
      text-align: center;
      margin-bottom: 24px;
    ">
      <?php if ($transaction['status'] === 'success'): ?>
        <div class="success-icon">✓</div>
        <h2 style="font-size:1.6rem;margin-bottom:8px;">Pembayaran Berhasil!</h2>
        <p style="color:var(--text-muted);">Top up kamu sudah diproses dan berhasil.</p>
      <?php elseif ($transaction['status'] === 'failed'): ?>
        <div style="width:80px;height:80px;background:rgba(239,68,68,0.1);border:2px solid rgba(239,68,68,0.3);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2rem;margin:0 auto 24px;">✕</div>
        <h2 style="font-size:1.6rem;margin-bottom:8px;">Pembayaran Gagal</h2>
        <p style="color:var(--text-muted);">Transaksi tidak berhasil diproses.</p>
      <?php else: ?>
        <div style="width:80px;height:80px;background:rgba(251,191,36,0.1);border:2px solid rgba(251,191,36,0.3);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2rem;margin:0 auto 24px;">⏳</div>
        <h2 style="font-size:1.6rem;margin-bottom:8px;">Menunggu Pembayaran</h2>
        <p style="color:var(--text-muted);">Selesaikan pembayaran kamu untuk proses top up.</p>
      <?php endif; ?>
    </div>

    <!-- Order Detail -->
    <div class="card" style="margin-bottom:24px;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid var(--dark-border);">
        <h3 style="font-size:1.05rem;font-weight:700;">Detail Pesanan</h3>
        <span class="badge badge-<?= $transaction['status'] ?>"><?= ucfirst($transaction['status']) ?></span>
      </div>

      <div style="display:flex;flex-direction:column;gap:14px;">
        <?php
        $details = [
          ['Order ID', $transaction['order_id'], true],
          ['Game', $transaction['game_name'], false],
          ['Paket', $transaction['package_name'] . ' (' . number_format($transaction['amount'], 0, ',', '.') . ' ' . $transaction['currency_name'] . ($transaction['bonus'] > 0 ? ' + ' . $transaction['bonus'] . ' bonus' : '') . ')', false],
          ['User ID', $transaction['game_user_id'], false],
          ['Metode Bayar', $transaction['payment_method'], false],
          ['Tanggal', date('d M Y, H:i', strtotime($transaction['created_at'])) . ' WIB', false],
          ['Total', 'Rp ' . number_format($transaction['total_price'], 0, ',', '.'), false],
        ];
        if ($transaction['game_server_id']) {
          array_splice($details, 4, 0, [['Server ID', $transaction['game_server_id'], false]]);
        }
        ?>
        <?php foreach ($details as [$label, $value, $copyable]): ?>
          <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;">
            <span style="font-size:0.85rem;color:var(--text-muted);white-space:nowrap;"><?= $label ?></span>
            <div style="display:flex;align-items:center;gap:8px;">
              <span style="font-size:0.9rem;font-weight:<?= $label === 'Total' ? '700' : '500' ?>;color:<?= $label === 'Total' ? 'var(--blue-400)' : 'var(--text-primary)' ?>;text-align:right;word-break:break-all;"><?= htmlspecialchars($value) ?></span>
              <?php if ($copyable): ?>
                <button onclick="copyText('<?= htmlspecialchars($value) ?>')" style="background:rgba(59,130,246,0.1);border:none;color:var(--blue-400);padding:4px 10px;border-radius:6px;font-size:0.72rem;cursor:pointer;white-space:nowrap;">Salin</button>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if ($transaction['status'] === 'pending'): ?>
    <!-- Payment Instructions + Bank Accounts -->
    <?php
    // Ambil info rekening dari database
    try {
        $bankStmt = $db->query("SELECT * FROM bank_accounts WHERE is_active = 1 ORDER BY sort_order, bank_name");
        $bankAccounts = $bankStmt->fetchAll();
    } catch (Exception $e) {
        $bankAccounts = [];
    }
    ?>

    <!-- Rekening Tujuan -->
    <?php if (!empty($bankAccounts)): ?>
    <div style="margin-bottom:20px;">
      <h4 style="font-size:0.95rem;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
        🏦 <span>Transfer ke Rekening Berikut</span>
      </h4>
      <div style="display:flex;flex-direction:column;gap:10px;">
        <?php foreach ($bankAccounts as $bank): ?>
          <div style="
            background:rgba(37,99,235,0.06);
            border:1px solid rgba(37,99,235,0.15);
            border-radius:var(--radius-md);
            padding:14px 18px;
            display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;
          ">
            <div style="display:flex;align-items:center;gap:14px;">
              <?php if ($bank['logo']): ?>
                <img src="<?= htmlspecialchars($bank['logo']) ?>" alt=""
                     style="height:36px;object-fit:contain;border-radius:6px;background:rgba(255,255,255,0.05);padding:4px;">
              <?php else: ?>
                <div style="width:36px;height:36px;background:rgba(37,99,235,0.15);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;">🏦</div>
              <?php endif; ?>
              <div>
                <div style="font-size:0.78rem;color:var(--text-muted);margin-bottom:2px;"><?= htmlspecialchars($bank['bank_name']) ?></div>
                <div style="font-family:monospace;font-size:1.1rem;font-weight:800;color:var(--blue-400);letter-spacing:0.05em;">
                  <?= htmlspecialchars($bank['account_number']) ?>
                </div>
                <div style="font-size:0.8rem;color:var(--text-secondary);">a.n. <?= htmlspecialchars($bank['account_name']) ?></div>
              </div>
            </div>
            <button onclick="copyText('<?= htmlspecialchars($bank['account_number']) ?>')"
                    style="background:rgba(37,99,235,0.15);border:1px solid rgba(37,99,235,0.3);color:var(--blue-400);padding:8px 14px;border-radius:8px;font-size:0.8rem;cursor:pointer;font-weight:600;white-space:nowrap;">
              📋 Salin
            </button>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Total yang harus ditransfer -->
    <div style="
      background:rgba(251,191,36,0.06);
      border:1px solid rgba(251,191,36,0.2);
      border-radius:var(--radius-md);
      padding:16px 20px;
      margin-bottom:16px;
      display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;
    ">
      <div>
        <div style="font-size:0.78rem;color:#fbbf24;margin-bottom:2px;">⚡ Nominal Transfer</div>
        <div style="font-family:var(--font-display);font-size:1.6rem;font-weight:800;color:#fbbf24;">
          Rp <?= number_format($transaction['total_price'], 0, ',', '.') ?>
        </div>
        <div style="font-size:0.75rem;color:var(--text-muted);">Transfer TEPAT sesuai nominal di atas</div>
      </div>
      <button onclick="copyText('<?= $transaction['total_price'] ?>')"
              style="background:rgba(251,191,36,0.15);border:1px solid rgba(251,191,36,0.3);color:#fbbf24;padding:8px 14px;border-radius:8px;font-size:0.8rem;cursor:pointer;font-weight:600;">
        📋 Salin Nominal
      </button>
    </div>

    <!-- Steps -->
    <div style="background:rgba(251,191,36,0.05);border:1px solid rgba(251,191,36,0.15);border-radius:var(--radius-md);padding:16px 20px;margin-bottom:24px;">
      <h4 style="color:#fbbf24;margin-bottom:10px;font-size:0.88rem;">📋 Langkah Pembayaran</h4>
      <ol style="color:var(--text-secondary);font-size:0.85rem;line-height:2;padding-left:20px;margin:0;">
        <li>Transfer nominal <strong style="color:#fbbf24;">TEPAT</strong> sesuai angka di atas ke salah satu rekening</li>
        <li>Simpan bukti transfer (screenshot)</li>
        <li>Kirim bukti transfer ke admin via WhatsApp / kontak yang tersedia</li>
        <li>Diamond/UC akan masuk dalam <strong>1–15 menit</strong> setelah konfirmasi</li>
      </ol>
    </div>
    <?php endif; ?>

    <?php if ($transaction['notes']): ?>
    <div style="background:rgba(37,99,235,0.08);border:1px solid rgba(37,99,235,0.2);border-radius:var(--radius-md);padding:16px;margin-bottom:24px;">
      <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:4px;">Catatan Admin:</div>
      <div style="font-size:0.9rem;"><?= htmlspecialchars($transaction['notes']) ?></div>
    </div>
    <?php endif; ?>

    <div class="flex gap-3" style="flex-wrap:wrap;">
      <a href="index.php?page=history" class="btn btn-primary">📋 Riwayat Transaksi</a>
      <a href="index.php?page=games" class="btn btn-outline">🎮 Top Up Lagi</a>
    </div>
  </div>
</div>
