<?php
require_once '../php/config.php';
requireAdmin();

$db = getDB();

// Handle toggle active
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'toggle_role') {
    $userId = (int)$_POST['user_id'];
    $newRole = sanitize($_POST['new_role']);
    if (in_array($newRole, ['user', 'admin']) && $userId !== $_SESSION['user_id']) {
        $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$newRole, $userId]);
        setFlash('success', 'Role user diupdate.');
    }
    header('Location: users.php');
    exit;
}

$search = sanitize($_GET['search'] ?? '');
$where = $search ? "WHERE username LIKE ? OR email LIKE ?" : "";
$params = $search ? ["%$search%", "%$search%"] : [];

$stmt = $db->prepare("SELECT u.*, (SELECT COUNT(*) FROM transactions WHERE user_id = u.id) as tx_count FROM users u $where ORDER BY u.created_at DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();

$page = 'users';
$pageTitle = 'Pengguna';
$pageSubtitle = 'Kelola akun pengguna';
include 'layout.php';
?>

<div class="filters-bar" style="margin-bottom:24px;">
  <form method="GET" style="display:flex;gap:12px;">
    <div class="search-input-wrapper">
      <span class="search-icon">🔍</span>
      <input type="text" name="search" class="form-control" placeholder="Cari username atau email..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <button type="submit" class="btn btn-primary">Cari</button>
    <?php if ($search): ?><a href="users.php" class="btn btn-ghost">Reset</a><?php endif; ?>
  </form>
</div>

<div class="admin-table-wrapper">
  <div class="admin-table-header">
    <div class="admin-table-title">👥 Semua Pengguna (<?= count($users) ?>)</div>
  </div>
  <div class="table-wrapper" style="border:none;border-radius:0;">
    <table>
      <thead>
        <tr>
          <th>Username</th>
          <th>Email</th>
          <th>Role</th>
          <th>Transaksi</th>
          <th>Bergabung</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td style="font-weight:600;">
              <?= htmlspecialchars($u['username']) ?>
              <?php if ($u['id'] === $_SESSION['user_id']): ?>
                <span style="font-size:0.72rem;color:var(--blue-400);"> (You)</span>
              <?php endif; ?>
            </td>
            <td style="font-size:0.88rem;color:var(--text-secondary);"><?= htmlspecialchars($u['email']) ?></td>
            <td>
              <span class="badge" style="<?= $u['role'] === 'admin' ? 'background:rgba(37,99,235,0.15);color:var(--blue-400);' : 'background:rgba(100,116,139,0.15);color:var(--text-muted);' ?>">
                <?= ucfirst($u['role']) ?>
              </span>
            </td>
            <td><?= $u['tx_count'] ?>x</td>
            <td style="font-size:0.82rem;color:var(--text-muted);"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
            <td>
              <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                <form method="POST">
                  <input type="hidden" name="action" value="toggle_role">
                  <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                  <input type="hidden" name="new_role" value="<?= $u['role'] === 'admin' ? 'user' : 'admin' ?>">
                  <button type="submit" class="btn btn-outline btn-sm" onclick="return confirm('Ubah role user ini?')">
                    <?= $u['role'] === 'admin' ? 'Set User' : 'Set Admin' ?>
                  </button>
                </form>
              <?php else: ?>
                <span style="font-size:0.8rem;color:var(--text-muted);">-</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'layout_end.php'; ?>
