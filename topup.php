<?php
$gameSlug = sanitize($_GET['game'] ?? '');

if (!$gameSlug) {
    header('Location: index.php?page=games');
    exit;
}

$stmt = $db->prepare("SELECT * FROM games WHERE slug = ? AND is_active = 1");
$stmt->execute([$gameSlug]);
$game = $stmt->fetch();

if (!$game) {
    header('Location: index.php?page=games');
    exit;
}

$stmt = $db->prepare("SELECT * FROM packages WHERE game_id = ? AND is_active = 1 ORDER BY price ASC");
$stmt->execute([$game['id']]);
$packages = $stmt->fetchAll();

$stmt = $db->prepare("SELECT * FROM payment_methods WHERE is_active = 1 ORDER BY type, name");
$stmt->execute();
$payments = $stmt->fetchAll();

$paymentGroups = [];
foreach ($payments as $pm) {
    $paymentGroups[$pm['type']][] = $pm;
}
$typeLabels = ['bank' => '🏦 Transfer Bank', 'ewallet' => '📱 E-Wallet', 'qris' => '📷 QRIS', 'retail' => '🏪 Retail'];

$gameEmoji = ['mobile-legends' => '⚔️', 'free-fire' => '🔥', 'pubg-mobile' => '🎯', 'genshin-impact' => '✨', 'valorant' => '🎮'];
?>

<div class="container section">
  <!-- Game Header -->
  <div style="
    background: linear-gradient(135deg, var(--dark-card) 0%, rgba(37,99,235,0.08) 100%);
    border: 1px solid var(--dark-border);
    border-radius: var(--radius-xl);
    padding: 32px;
    margin-bottom: 36px;
    display: flex;
    align-items: center;
    gap: 24px;
    position: relative;
    overflow: hidden;
  ">
    <div style="position:absolute;top:0;right:0;width:300px;height:100%;background:linear-gradient(135deg, transparent, rgba(37,99,235,0.05));pointer-events:none;"></div>
    
    <div style="width:80px;height:80px;background:linear-gradient(135deg,var(--blue-900),var(--blue-700));border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;font-size:2.5rem;flex-shrink:0;">
      <?= $gameEmoji[$game['slug']] ?? '🎮' ?>
    </div>
    <div>
      <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--blue-400);margin-bottom:4px;"><?= htmlspecialchars($game['category']) ?> · <?= htmlspecialchars($game['developer']) ?></div>
      <h1 style="font-size:1.8rem;font-weight:800;margin-bottom:6px;"><?= htmlspecialchars($game['name']) ?></h1>
      <p style="font-size:0.9rem;color:var(--text-muted);"><?= htmlspecialchars($game['description']) ?></p>
    </div>
  </div>

  <?php if (!isLoggedIn()): ?>
    <div style="background:rgba(37,99,235,0.1);border:1px solid rgba(37,99,235,0.3);border-radius:var(--radius-md);padding:16px 20px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
      <p style="font-size:0.9rem;color:var(--blue-300);">⚡ Login untuk menyimpan riwayat transaksi kamu</p>
      <a href="index.php?page=login" class="btn btn-primary btn-sm">Login</a>
    </div>
  <?php endif; ?>

  <form action="php/transaction.php" method="POST" id="checkout-form">
    <input type="hidden" name="action" value="create_order">
    <input type="hidden" name="game_id" value="<?= $game['id'] ?>">
    <input type="hidden" name="package_id" id="selected_package_id" value="">
    <input type="hidden" name="payment_method_id" id="selected_payment_id" value="">

    <div style="display:grid;grid-template-columns:1fr 340px;gap:28px;align-items:start;">
      <!-- LEFT COLUMN -->
      <div>
        <!-- Step 1: User ID -->
        <div class="card" style="margin-bottom:24px;">
          <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
            <div style="width:28px;height:28px;background:var(--gradient-blue);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:800;flex-shrink:0;">1</div>
            <h3 style="font-size:1.05rem;font-weight:700;">Data Akun Game</h3>
          </div>

          <div class="form-group">
            <label class="form-label">User ID / Player ID</label>
            <input type="text" name="game_user_id" id="game_user_id" class="form-control" placeholder="Masukkan User ID kamu" required>
            <div style="font-size:0.78rem;color:var(--text-muted);margin-top:6px;">Temukan User ID di profil game atau settings</div>
          </div>

          <?php if (in_array($gameSlug, ['mobile-legends', 'free-fire', 'pubg-mobile'])): ?>
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Server ID <span style="color:var(--text-muted);font-weight:400;">(opsional)</span></label>
            <input type="text" name="game_server_id" class="form-control" placeholder="Contoh: 1234">
          </div>
          <?php endif; ?>
        </div>

        <!-- Step 2: Package -->
        <div class="card" style="margin-bottom:24px;">
          <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
            <div style="width:28px;height:28px;background:var(--gradient-blue);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:800;flex-shrink:0;">2</div>
            <h3 style="font-size:1.05rem;font-weight:700;">Pilih Paket Top Up</h3>
          </div>

          <div class="packages-grid">
            <?php foreach ($packages as $pkg): ?>
              <div class="package-card"
                data-package-id="<?= $pkg['id'] ?>"
                data-price="<?= $pkg['price'] ?>"
                data-name="<?= htmlspecialchars($pkg['name']) ?> - <?= $pkg['amount'] ?> <?= htmlspecialchars($pkg['currency_name']) ?>"
                onclick="selectPackage(this)"
              >
                <?php if ($pkg['is_popular']): ?>
                  <div class="package-popular">🔥 Popular</div>
                <?php endif; ?>
                <div class="package-amount"><?= number_format($pkg['amount'], 0, ',', '.') ?></div>
                <div class="package-currency"><?= htmlspecialchars($pkg['currency_name']) ?></div>
                <?php if ($pkg['bonus'] > 0): ?>
                  <div class="package-bonus">+<?= $pkg['bonus'] ?> Bonus</div>
                <?php else: ?>
                  <div style="height:18px;margin-bottom:12px;"></div>
                <?php endif; ?>
                <div class="package-price">Rp <?= number_format($pkg['price'], 0, ',', '.') ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Step 3: Payment -->
        <div class="card">
          <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
            <div style="width:28px;height:28px;background:var(--gradient-blue);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:800;flex-shrink:0;">3</div>
            <h3 style="font-size:1.05rem;font-weight:700;">Metode Pembayaran</h3>
          </div>

          <?php foreach ($paymentGroups as $type => $methods): ?>
            <div style="margin-bottom:20px;">
              <div style="font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--text-muted);margin-bottom:10px;"><?= $typeLabels[$type] ?? $type ?></div>
              <div class="payment-grid">
                <?php foreach ($methods as $pm): ?>
                  <div class="payment-card"
                    data-payment-id="<?= $pm['id'] ?>"
                    data-fee="<?= $pm['fee_value'] ?>"
                    data-fee-type="<?= $pm['fee_type'] ?>"
                    onclick="selectPayment(this)"
                  >
                    <div style="font-size:1.5rem;"><?= $type === 'bank' ? '🏦' : ($type === 'ewallet' ? '📱' : ($type === 'qris' ? '📷' : '🏪')) ?></div>
                    <div class="payment-name"><?= htmlspecialchars($pm['name']) ?></div>
                    <?php if ($pm['fee_value'] > 0): ?>
                      <div class="payment-type-label">
                        +<?= $pm['fee_type'] === 'percent' ? $pm['fee_value'] . '%' : 'Rp ' . number_format($pm['fee_value'], 0, ',', '.') ?>
                      </div>
                    <?php else: ?>
                      <div class="payment-type-label">Gratis</div>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- RIGHT COLUMN: ORDER SUMMARY -->
      <div style="position:sticky;top:90px;">
        <div class="card" style="background:linear-gradient(145deg, var(--dark-card2), var(--dark-card));">
          <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid var(--dark-border);">📋 Ringkasan Pesanan</h3>

          <div style="margin-bottom:16px;">
            <div style="font-size:0.78rem;color:var(--text-muted);margin-bottom:4px;">Game</div>
            <div style="font-weight:600;"><?= htmlspecialchars($game['name']) ?></div>
          </div>

          <div style="margin-bottom:16px;">
            <div style="font-size:0.78rem;color:var(--text-muted);margin-bottom:4px;">Paket Dipilih</div>
            <div id="summary-package" style="font-weight:600;color:var(--text-secondary);">Belum dipilih</div>
          </div>

          <div class="divider"></div>

          <div style="display:flex;justify-content:space-between;margin-bottom:10px;font-size:0.9rem;">
            <span style="color:var(--text-muted);">Subtotal</span>
            <span id="summary-subtotal">Rp 0</span>
          </div>
          <div style="display:flex;justify-content:space-between;margin-bottom:16px;font-size:0.9rem;">
            <span style="color:var(--text-muted);">Biaya Layanan</span>
            <span id="summary-fee">Rp 0</span>
          </div>

          <div style="display:flex;justify-content:space-between;margin-bottom:24px;font-size:1.1rem;font-weight:700;">
            <span>Total</span>
            <span id="summary-total" style="color:var(--blue-400);">Rp 0</span>
          </div>

          <button type="submit" class="btn btn-primary btn-full btn-lg">
            ⚡ Beli Sekarang
          </button>

          <p style="font-size:0.75rem;color:var(--text-muted);text-align:center;margin-top:12px;">
            🔒 Transaksi aman & terenkripsi
          </p>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
function selectPackage(el) {
  document.querySelectorAll('.package-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  document.getElementById('selected_package_id').value = el.dataset.packageId;
  updateSummary();
}

function selectPayment(el) {
  document.querySelectorAll('.payment-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  document.getElementById('selected_payment_id').value = el.dataset.paymentId;
  updateSummary();
}

function updateSummary() {
  const pkg = document.querySelector('.package-card.selected');
  const pay = document.querySelector('.payment-card.selected');
  
  if (!pkg) return;
  
  const price = parseFloat(pkg.dataset.price) || 0;
  const fee = pay ? parseFloat(pay.dataset.fee) || 0 : 0;
  const feeType = pay ? pay.dataset.feeType : 'fixed';
  const feeAmt = feeType === 'percent' ? price * (fee / 100) : fee;
  const total = price + feeAmt;
  
  const fmt = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
  
  document.getElementById('summary-package').textContent = pkg.dataset.name || '-';
  document.getElementById('summary-subtotal').textContent = fmt(price);
  document.getElementById('summary-fee').textContent = fmt(feeAmt);
  document.getElementById('summary-total').textContent = fmt(total);
}
</script>

<style>
@media (max-width: 900px) {
  form > div[style*="grid-template-columns"] {
    display: block !important;
  }
  form > div > div:last-child {
    position: static !important;
    margin-top: 24px;
  }
}
</style>
