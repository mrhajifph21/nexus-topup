<?php
require_once '../php/config.php';
requireAdmin();

$db = getDB();
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';

    if ($postAction === 'add' || $postAction === 'edit') {
        $data = [
            sanitize($_POST['name'] ?? ''),
            sanitize($_POST['type'] ?? ''),
            sanitize($_POST['fee_type'] ?? 'fixed'),
            (float)($_POST['fee_value'] ?? 0),
            isset($_POST['is_active']) ? 1 : 0,
        ];

        if ($postAction === 'add') {
            $stmt = $db->prepare("INSERT INTO payment_methods (name, type, fee_type, fee_value, is_active) VALUES (?,?,?,?,?)");
            $stmt->execute($data);
            setFlash('success', 'Metode pembayaran ditambahkan!');
        } else {
            $data[] = (int)$_POST['edit_id'];
            $stmt = $db->prepare("UPDATE payment_methods SET name=?, type=?, fee_type=?, fee_value=?, is_active=? WHERE id=?");
            $stmt->execute($data);
            setFlash('success', 'Metode pembayaran diupdate!');
        }
        header('Location: payments.php');
        exit;
    }

    if ($postAction === 'delete') {
        $stmt = $db->prepare("DELETE FROM payment_methods WHERE id = ?");
        $stmt->execute([$_POST['payment_id']]);
        setFlash('success', 'Metode dihapus.');
        header('Location: payments.php');
        exit;
    }
}

$editPm = null;
if ($action === 'edit') {
    $stmt = $db->prepare("SELECT * FROM payment_methods WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $editPm = $stmt->fetch();
}

$stmt = $db->query("SELECT * FROM payment_methods ORDER BY type, name");
$payments = $stmt->fetchAll();

$page = 'payments';
$pageTitle = 'Metode Pembayaran';
$pageSubtitle = 'Kelola metode pembayaran yang tersedia';
$headerAction = '<a href="payments.php?action=add" class="btn btn-primary">+ Tambah Metode</a>';
include 'layout.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="form-panel" style="max-width:560px;margin-bottom:32px;">
  <div class="form-panel-title"><?= $action === 'add' ? '➕ Tambah Metode Pembayaran' : '✏️ Edit Metode' ?></div>
  <form method="POST">
    <input type="hidden" name="action" value="<?= $action === 'add' ? 'add' : 'edit' ?>">
    <?php if ($editPm): ?><input type="hidden" name="edit_id" value="<?= $editPm['id'] ?>"><?php endif; ?>
    <div class="form-grid-2">
      <div class="form-group">
        <label class="form-label">Nama *</label>
        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($editPm['name'] ?? '') ?>" placeholder="BCA, GoPay, QRIS...">
      </div>
      <div class="form-group">
        <label class="form-label">Tipe *</label>
        <select name="type" class="form-control" required>
          <?php foreach (['bank' => 'Transfer Bank', 'ewallet' => 'E-Wallet', 'qris' => 'QRIS', 'retail' => 'Retail'] as $v => $l): ?>
            <option value="<?= $v ?>" <?= ($editPm['type'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Tipe Biaya</label>
        <select name="fee_type" class="form-control">
          <option value="fixed" <?= ($editPm['fee_type'] ?? 'fixed') === 'fixed' ? 'selected' : '' ?>>Fixed (Rp)</option>
          <option value="percent" <?= ($editPm['fee_type'] ?? '') === 'percent' ? 'selected' : '' ?>>Persentase (%)</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Nilai Biaya</label>
        <input type="number" name="fee_value" class="form-control" step="0.01" value="<?= $editPm['fee_value'] ?? 0 ?>" placeholder="0">
      </div>
    </div>
    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:20px;">
      <input type="checkbox" name="is_active" style="width:16px;height:16px;" <?= ($editPm['is_active'] ?? 1) ? 'checked' : '' ?>>
      Aktif
    </label>
    <div class="flex gap-3">
      <button type="submit" class="btn btn-primary">💾 Simpan</button>
      <a href="payments.php" class="btn btn-ghost">Batal</a>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="admin-table-wrapper">
  <div class="admin-table-header">
    <div class="admin-table-title">💳 Metode Pembayaran</div>
  </div>
  <div class="table-wrapper" style="border:none;border-radius:0;">
    <table>
      <thead>
        <tr><th>Nama</th><th>Tipe</th><th>Biaya</th><th>Status</th><th>Aksi</th></tr>
      </thead>
      <tbody>
        <?php foreach ($payments as $pm): ?>
          <tr>
            <td style="font-weight:600;"><?= htmlspecialchars($pm['name']) ?></td>
            <td><?= ucfirst($pm['type']) ?></td>
            <td>
              <?php if ($pm['fee_value'] > 0): ?>
                <?= $pm['fee_type'] === 'percent' ? $pm['fee_value'] . '%' : 'Rp ' . number_format($pm['fee_value'], 0, ',', '.') ?>
              <?php else: ?>
                <span style="color:var(--text-muted);">Gratis</span>
              <?php endif; ?>
            </td>
            <td><span class="badge" style="<?= $pm['is_active'] ? 'background:rgba(74,222,128,0.15);color:#4ade80;' : 'background:rgba(239,68,68,0.15);color:#f87171;' ?>"><?= $pm['is_active'] ? 'Aktif' : 'Non-aktif' ?></span></td>
            <td>
              <div class="flex gap-2">
                <a href="payments.php?action=edit&id=<?= $pm['id'] ?>" class="btn btn-outline btn-sm">✏️</a>
                <form method="POST" onsubmit="return confirm('Hapus metode ini?')">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="payment_id" value="<?= $pm['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'layout_end.php'; ?>
