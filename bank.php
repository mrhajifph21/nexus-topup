<?php
require_once '../php/config.php';
requireAdmin();

$db = getDB();

// Pastikan tabel ada
$db->exec("CREATE TABLE IF NOT EXISTS bank_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bank_name VARCHAR(100) NOT NULL,
    account_number VARCHAR(50) NOT NULL,
    account_name VARCHAR(100) NOT NULL,
    logo VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$action = $_GET['action'] ?? '';

// ---- Handle POST ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';

    if ($postAction === 'add' || $postAction === 'edit') {
        $bankName    = sanitize($_POST['bank_name'] ?? '');
        $accNumber   = sanitize($_POST['account_number'] ?? '');
        $accName     = sanitize($_POST['account_name'] ?? '');
        $sortOrder   = (int)($_POST['sort_order'] ?? 0);
        $isActive    = isset($_POST['is_active']) ? 1 : 0;

        // Handle logo upload
        $logoPath = sanitize($_POST['existing_logo'] ?? '');
        if (!empty($_FILES['logo']['name'])) {
            $file     = $_FILES['logo'];
            $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (!in_array($ext, $allowed)) {
                setFlash('error', 'Format gambar tidak valid. Gunakan JPG, PNG, atau WEBP.');
                header('Location: bank.php');
                exit;
            }
            if ($file['size'] > 2 * 1024 * 1024) {
                setFlash('error', 'Ukuran gambar maksimal 2MB.');
                header('Location: bank.php');
                exit;
            }

            $uploadDir = '../assets/img/banks/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $filename = 'bank_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                // Hapus logo lama kalau ada
                if ($logoPath && file_exists($uploadDir . basename($logoPath))) {
                    @unlink($uploadDir . basename($logoPath));
                }
                $logoPath = 'assets/img/banks/' . $filename;
            }
        }

        if ($postAction === 'add') {
            $stmt = $db->prepare("INSERT INTO bank_accounts (bank_name, account_number, account_name, logo, is_active, sort_order) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$bankName, $accNumber, $accName, $logoPath, $isActive, $sortOrder]);
            setFlash('success', 'Rekening berhasil ditambahkan!');
        } else {
            $stmt = $db->prepare("UPDATE bank_accounts SET bank_name=?, account_number=?, account_name=?, logo=?, is_active=?, sort_order=? WHERE id=?");
            $stmt->execute([$bankName, $accNumber, $accName, $logoPath, $isActive, $sortOrder, (int)$_POST['edit_id']]);
            setFlash('success', 'Rekening berhasil diupdate!');
        }
        header('Location: bank.php');
        exit;
    }

    if ($postAction === 'delete') {
        $id = (int)$_POST['bank_id'];
        // Hapus logo file juga
        $stmt = $db->prepare("SELECT logo FROM bank_accounts WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row && $row['logo'] && file_exists('../' . $row['logo'])) {
            @unlink('../' . $row['logo']);
        }
        $stmt = $db->prepare("DELETE FROM bank_accounts WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'Rekening dihapus.');
        header('Location: bank.php');
        exit;
    }
}

$editBank = null;
if ($action === 'edit') {
    $stmt = $db->prepare("SELECT * FROM bank_accounts WHERE id = ?");
    $stmt->execute([(int)($_GET['id'] ?? 0)]);
    $editBank = $stmt->fetch();
}

$stmt = $db->query("SELECT * FROM bank_accounts ORDER BY sort_order, bank_name");
$banks = $stmt->fetchAll();

$page            = 'bank';
$pageTitle       = 'Info Rekening';
$pageSubtitle    = 'Kelola nomor rekening untuk pembayaran';
$headerAction    = '<a href="bank.php?action=add" class="btn btn-primary">+ Tambah Rekening</a>';
include 'layout.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="form-panel" style="max-width:600px; margin-bottom:32px;">
  <div class="form-panel-title"><?= $action === 'add' ? '➕ Tambah Rekening' : '✏️ Edit Rekening' ?></div>

  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="<?= $action === 'add' ? 'add' : 'edit' ?>">
    <?php if ($editBank): ?>
      <input type="hidden" name="edit_id" value="<?= $editBank['id'] ?>">
      <input type="hidden" name="existing_logo" value="<?= htmlspecialchars($editBank['logo'] ?? '') ?>">
    <?php endif; ?>

    <div class="form-grid-2">
      <div class="form-group">
        <label class="form-label">Nama Bank / E-Wallet *</label>
        <input type="text" name="bank_name" class="form-control" required
          value="<?= htmlspecialchars($editBank['bank_name'] ?? '') ?>"
          placeholder="BCA, GoPay, Dana, OVO...">
      </div>
      <div class="form-group">
        <label class="form-label">Nomor Rekening / No. HP *</label>
        <input type="text" name="account_number" class="form-control" required
          value="<?= htmlspecialchars($editBank['account_number'] ?? '') ?>"
          placeholder="1234567890">
      </div>
    </div>

    <div class="form-grid-2">
      <div class="form-group">
        <label class="form-label">Nama Pemilik *</label>
        <input type="text" name="account_name" class="form-control" required
          value="<?= htmlspecialchars($editBank['account_name'] ?? '') ?>"
          placeholder="Nama sesuai rekening">
      </div>
      <div class="form-group">
        <label class="form-label">Urutan Tampil</label>
        <input type="number" name="sort_order" class="form-control"
          value="<?= $editBank['sort_order'] ?? 0 ?>"
          placeholder="0 = tampil pertama">
      </div>
    </div>

    <!-- Logo Upload -->
    <div class="form-group">
      <label class="form-label">Logo Bank (opsional)</label>

      <?php if (!empty($editBank['logo'])): ?>
        <div style="margin-bottom:12px; display:flex; align-items:center; gap:12px;">
          <img src="../<?= htmlspecialchars($editBank['logo']) ?>"
               alt="Logo" id="logo-preview"
               style="height:48px; object-fit:contain; border-radius:8px; background:rgba(255,255,255,0.05); padding:6px;">
          <span style="font-size:0.82rem; color:var(--text-muted);">Logo saat ini. Upload baru untuk mengganti.</span>
        </div>
      <?php else: ?>
        <div style="margin-bottom:12px;">
          <img src="" alt="" id="logo-preview"
               style="height:48px; object-fit:contain; border-radius:8px; background:rgba(255,255,255,0.05); padding:6px; display:none;">
        </div>
      <?php endif; ?>

      <label style="
        display:flex; align-items:center; gap:12px;
        padding:14px 16px;
        border:1.5px dashed var(--dark-border);
        border-radius:var(--radius-md);
        cursor:pointer;
        transition:var(--transition);
        color:var(--text-muted);
        font-size:0.88rem;
      " onmouseover="this.style.borderColor='var(--blue-500)'" onmouseout="this.style.borderColor='var(--dark-border)'">
        <span style="font-size:1.5rem;">🖼️</span>
        <span id="upload-label">Klik untuk upload logo (JPG/PNG/WEBP, maks 2MB)</span>
        <input type="file" name="logo" accept="image/*" style="display:none"
               onchange="previewLogo(this)">
      </label>
    </div>

    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin-bottom:20px;">
      <input type="checkbox" name="is_active" style="width:16px;height:16px;"
             <?= ($editBank['is_active'] ?? 1) ? 'checked' : '' ?>>
      Tampilkan di halaman pembayaran
    </label>

    <div class="flex gap-3">
      <button type="submit" class="btn btn-primary">💾 Simpan Rekening</button>
      <a href="bank.php" class="btn btn-ghost">Batal</a>
    </div>
  </form>
</div>
<?php endif; ?>

<!-- Bank Accounts Table -->
<div class="admin-table-wrapper">
  <div class="admin-table-header">
    <div class="admin-table-title">🏦 Daftar Rekening (<?= count($banks) ?>)</div>
  </div>

  <?php if (empty($banks)): ?>
    <div style="text-align:center; padding:48px; color:var(--text-muted);">
      <div style="font-size:3rem; margin-bottom:12px;">🏦</div>
      <p>Belum ada rekening. <a href="bank.php?action=add" style="color:var(--blue-400);">Tambah sekarang →</a></p>
    </div>
  <?php else: ?>
  <div class="table-wrapper" style="border:none;border-radius:0;">
    <table>
      <thead>
        <tr>
          <th>Logo</th>
          <th>Bank / E-Wallet</th>
          <th>No. Rekening</th>
          <th>Atas Nama</th>
          <th>Urutan</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($banks as $b): ?>
          <tr>
            <td>
              <?php if ($b['logo']): ?>
                <img src="../<?= htmlspecialchars($b['logo']) ?>" alt=""
                     style="height:36px; object-fit:contain; border-radius:6px; background:rgba(255,255,255,0.05); padding:4px;">
              <?php else: ?>
                <div style="width:36px;height:36px;background:rgba(37,99,235,0.1);border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;">🏦</div>
              <?php endif; ?>
            </td>
            <td style="font-weight:700;"><?= htmlspecialchars($b['bank_name']) ?></td>
            <td>
              <div style="font-family:monospace; font-size:1rem; font-weight:600; color:var(--blue-400);">
                <?= htmlspecialchars($b['account_number']) ?>
              </div>
            </td>
            <td><?= htmlspecialchars($b['account_name']) ?></td>
            <td style="color:var(--text-muted);"><?= $b['sort_order'] ?></td>
            <td>
              <span class="badge" style="<?= $b['is_active'] ? 'background:rgba(74,222,128,0.15);color:#4ade80;' : 'background:rgba(239,68,68,0.15);color:#f87171;' ?>">
                <?= $b['is_active'] ? 'Aktif' : 'Non-aktif' ?>
              </span>
            </td>
            <td>
              <div class="flex gap-2">
                <a href="bank.php?action=edit&id=<?= $b['id'] ?>" class="btn btn-outline btn-sm">✏️ Edit</a>
                <form method="POST" onsubmit="return confirm('Hapus rekening ini?')">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="bank_id" value="<?= $b['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<script>
function previewLogo(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      const preview = document.getElementById('logo-preview');
      preview.src = e.target.result;
      preview.style.display = 'block';
      document.getElementById('upload-label').textContent = '✓ ' + input.files[0].name;
    };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>

<?php include 'layout_end.php'; ?>
