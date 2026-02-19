<?php
require_once '../php/config.php';
requireAdmin();

$db = getDB();
$action = $_GET['action'] ?? '';
$gameFilter = (int)($_GET['game_id'] ?? 0);

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';

    if ($postAction === 'add' || $postAction === 'edit') {
        $data = [
            sanitize($_POST['name'] ?? ''),
            (int)$_POST['game_id'],
            (int)$_POST['amount'],
            sanitize($_POST['currency_name'] ?? ''),
            (float)$_POST['price'],
            (int)($_POST['bonus'] ?? 0),
            isset($_POST['is_popular']) ? 1 : 0,
            isset($_POST['is_active']) ? 1 : 0,
        ];

        if ($postAction === 'add') {
            $stmt = $db->prepare("INSERT INTO packages (name, game_id, amount, currency_name, price, bonus, is_popular, is_active) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute($data);
            setFlash('success', 'Paket berhasil ditambahkan!');
        } else {
            $data[] = (int)$_POST['edit_id'];
            $stmt = $db->prepare("UPDATE packages SET name=?, game_id=?, amount=?, currency_name=?, price=?, bonus=?, is_popular=?, is_active=? WHERE id=?");
            $stmt->execute($data);
            setFlash('success', 'Paket berhasil diupdate!');
        }
        header('Location: packages.php' . ($gameFilter ? "?game_id=$gameFilter" : ''));
        exit;
    }

    if ($postAction === 'delete') {
        $stmt = $db->prepare("DELETE FROM packages WHERE id = ?");
        $stmt->execute([$_POST['package_id']]);
        setFlash('success', 'Paket dihapus.');
        header('Location: packages.php' . ($gameFilter ? "?game_id=$gameFilter" : ''));
        exit;
    }
}

// Get all games for filter/dropdown
$stmt = $db->query("SELECT id, name FROM games ORDER BY name");
$allGames = $stmt->fetchAll();

// Get packages
$where = $gameFilter ? "WHERE p.game_id = $gameFilter" : "";
$stmt = $db->query("SELECT p.*, g.name as game_name FROM packages p JOIN games g ON p.game_id = g.id $where ORDER BY g.name, p.price");
$packages = $stmt->fetchAll();

$editPkg = null;
if ($action === 'edit') {
    $editId = (int)($_GET['id'] ?? 0);
    $stmt = $db->prepare("SELECT * FROM packages WHERE id = ?");
    $stmt->execute([$editId]);
    $editPkg = $stmt->fetch();
}

$page = 'packages';
$pageTitle = 'Paket Top Up';
$pageSubtitle = 'Kelola paket untuk setiap game';
$headerAction = '<a href="packages.php?action=add" class="btn btn-primary">+ Tambah Paket</a>';
include 'layout.php';
?>

<!-- Filter by game -->
<div class="filters-bar" style="margin-bottom:24px;">
  <form method="GET" style="display:flex;gap:12px;">
    <select name="game_id" class="form-control">
      <option value="">Semua Game</option>
      <?php foreach ($allGames as $g): ?>
        <option value="<?= $g['id'] ?>" <?= $gameFilter === $g['id'] ? 'selected' : '' ?>><?= htmlspecialchars($g['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary">Filter</button>
    <?php if ($gameFilter): ?><a href="packages.php" class="btn btn-ghost">Reset</a><?php endif; ?>
  </form>
</div>

<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="form-panel" style="max-width:700px;margin-bottom:32px;">
  <div class="form-panel-title"><?= $action === 'add' ? '➕ Tambah Paket' : '✏️ Edit Paket' ?></div>
  <form method="POST">
    <input type="hidden" name="action" value="<?= $action === 'add' ? 'add' : 'edit' ?>">
    <?php if ($editPkg): ?><input type="hidden" name="edit_id" value="<?= $editPkg['id'] ?>"><?php endif; ?>
    <div class="form-grid-2">
      <div class="form-group">
        <label class="form-label">Game *</label>
        <select name="game_id" class="form-control" required>
          <?php foreach ($allGames as $g): ?>
            <option value="<?= $g['id'] ?>" <?= ($editPkg['game_id'] ?? $gameFilter) == $g['id'] ? 'selected' : '' ?>><?= htmlspecialchars($g['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Nama Paket *</label>
        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($editPkg['name'] ?? '') ?>" placeholder="Hemat, Value, Popular...">
      </div>
      <div class="form-group">
        <label class="form-label">Jumlah *</label>
        <input type="number" name="amount" class="form-control" required value="<?= $editPkg['amount'] ?? '' ?>" placeholder="86">
      </div>
      <div class="form-group">
        <label class="form-label">Mata Uang Game *</label>
        <input type="text" name="currency_name" class="form-control" required value="<?= htmlspecialchars($editPkg['currency_name'] ?? '') ?>" placeholder="Diamond, UC, Genesis Crystal...">
      </div>
      <div class="form-group">
        <label class="form-label">Harga (Rp) *</label>
        <input type="number" name="price" class="form-control" required value="<?= $editPkg['price'] ?? '' ?>" placeholder="19000">
      </div>
      <div class="form-group">
        <label class="form-label">Bonus</label>
        <input type="number" name="bonus" class="form-control" value="<?= $editPkg['bonus'] ?? 0 ?>" placeholder="0">
      </div>
    </div>
    <div style="display:flex;gap:24px;margin-bottom:20px;">
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
        <input type="checkbox" name="is_popular" style="width:16px;height:16px;" <?= ($editPkg['is_popular'] ?? 0) ? 'checked' : '' ?>>
        🔥 Tandai sebagai Popular
      </label>
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
        <input type="checkbox" name="is_active" style="width:16px;height:16px;" <?= ($editPkg['is_active'] ?? 1) ? 'checked' : '' ?>>
        Aktif
      </label>
    </div>
    <div class="flex gap-3">
      <button type="submit" class="btn btn-primary">💾 Simpan Paket</button>
      <a href="packages.php" class="btn btn-ghost">Batal</a>
    </div>
  </form>
</div>
<?php endif; ?>

<!-- Packages Table -->
<div class="admin-table-wrapper">
  <div class="admin-table-header">
    <div class="admin-table-title">💎 Daftar Paket (<?= count($packages) ?>)</div>
  </div>
  <div class="table-wrapper" style="border:none;border-radius:0;">
    <table>
      <thead>
        <tr>
          <th>Game</th>
          <th>Nama</th>
          <th>Jumlah</th>
          <th>Harga</th>
          <th>Bonus</th>
          <th>Popular</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($packages as $p): ?>
          <tr>
            <td style="font-size:0.88rem;"><?= htmlspecialchars($p['game_name']) ?></td>
            <td style="font-weight:600;"><?= htmlspecialchars($p['name']) ?></td>
            <td><?= number_format($p['amount'], 0, ',', '.') ?> <?= htmlspecialchars($p['currency_name']) ?></td>
            <td style="font-weight:700;">Rp <?= number_format($p['price'], 0, ',', '.') ?></td>
            <td><?= $p['bonus'] > 0 ? '<span style="color:#4ade80;">+' . $p['bonus'] . '</span>' : '-' ?></td>
            <td><?= $p['is_popular'] ? '🔥' : '-' ?></td>
            <td><span class="badge" style="<?= $p['is_active'] ? 'background:rgba(74,222,128,0.15);color:#4ade80;' : 'background:rgba(239,68,68,0.15);color:#f87171;' ?>"><?= $p['is_active'] ? 'Aktif' : 'Non-aktif' ?></span></td>
            <td>
              <div class="flex gap-2">
                <a href="packages.php?action=edit&id=<?= $p['id'] ?>" class="btn btn-outline btn-sm">✏️</a>
                <form method="POST" onsubmit="return confirm('Hapus paket ini?')">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="package_id" value="<?= $p['id'] ?>">
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
