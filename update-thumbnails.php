<?php
require_once 'php/config.php';

// =========================================
// UPDATE THUMBNAIL GAME
// Buka: http://localhost/topup-game/update-thumbnails.php
// Hapus file ini setelah selesai!
// =========================================

$db = getDB();

// Pakai URL gambar yang stabil dari CDN / Wikipedia / official
$games = [
    'mobile-legends' => [
        'thumbnail' => 'https://upload.wikimedia.org/wikipedia/en/thumb/8/80/Mobile_Legends_Bang_Bang.png/220px-Mobile_Legends_Bang_Bang.png',
        'banner'    => 'https://upload.wikimedia.org/wikipedia/en/thumb/8/80/Mobile_Legends_Bang_Bang.png/220px-Mobile_Legends_Bang_Bang.png',
    ],
    'free-fire' => [
        'thumbnail' => 'https://upload.wikimedia.org/wikipedia/en/thumb/b/b8/Garena_Free_Fire_logo.png/220px-Garena_Free_Fire_logo.png',
        'banner'    => 'https://upload.wikimedia.org/wikipedia/en/thumb/b/b8/Garena_Free_Fire_logo.png/220px-Garena_Free_Fire_logo.png',
    ],
    'pubg-mobile' => [
        'thumbnail' => 'https://upload.wikimedia.org/wikipedia/en/thumb/9/97/PUBG_Mobile.png/220px-PUBG_Mobile.png',
        'banner'    => 'https://upload.wikimedia.org/wikipedia/en/thumb/9/97/PUBG_Mobile.png/220px-PUBG_Mobile.png',
    ],
    'genshin-impact' => [
        'thumbnail' => 'https://upload.wikimedia.org/wikipedia/en/thumb/4/44/Genshin_Impact_cover_art.jpg/220px-Genshin_Impact_cover_art.jpg',
        'banner'    => 'https://upload.wikimedia.org/wikipedia/en/thumb/4/44/Genshin_Impact_cover_art.jpg/220px-Genshin_Impact_cover_art.jpg',
    ],
    'valorant' => [
        'thumbnail' => 'https://upload.wikimedia.org/wikipedia/en/thumb/4/44/Valorant_cover.jpg/220px-Valorant_cover.jpg',
        'banner'    => 'https://upload.wikimedia.org/wikipedia/en/thumb/4/44/Valorant_cover.jpg/220px-Valorant_cover.jpg',
    ],
];

$updated = 0;
foreach ($games as $slug => $imgs) {
    $stmt = $db->prepare("UPDATE games SET thumbnail = ?, banner = ? WHERE slug = ?");
    $stmt->execute([$imgs['thumbnail'], $imgs['banner'], $slug]);
    $updated += $stmt->rowCount();
}

// Ambil hasil
$stmt = $db->query("SELECT id, name, slug, thumbnail FROM games");
$result = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: sans-serif; padding: 40px; background: #0f172a; color: #f1f5f9; }
    h2 { color: #60a5fa; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { padding: 12px 16px; border: 1px solid #1e3a5f; text-align: left; }
    th { background: #1e3a5f; }
    img { width: 80px; height: 60px; object-fit: cover; border-radius: 6px; }
    .success { color: #4ade80; }
    .warning { color: #fbbf24; background: #1c1a00; padding: 12px 16px; border-radius: 8px; margin-top: 20px; border: 1px solid #713f12; }
    a { color: #60a5fa; }
  </style>
</head>
<body>
  <h2>✅ Update Thumbnail Game</h2>
  <p class="success">Berhasil update <strong><?= $updated ?></strong> game!</p>

  <table>
    <thead>
      <tr><th>ID</th><th>Nama Game</th><th>Slug</th><th>Preview</th><th>URL</th></tr>
    </thead>
    <tbody>
      <?php foreach ($result as $g): ?>
        <tr>
          <td><?= $g['id'] ?></td>
          <td><?= htmlspecialchars($g['name']) ?></td>
          <td><?= htmlspecialchars($g['slug']) ?></td>
          <td>
            <?php if ($g['thumbnail']): ?>
              <img src="<?= htmlspecialchars($g['thumbnail']) ?>" alt="" onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
              <span style="display:none; color:#f87171;">❌ Gambar gagal load</span>
            <?php else: ?>
              <span style="color:#94a3b8;">-</span>
            <?php endif; ?>
          </td>
          <td style="font-size:0.78rem; word-break:break-all; max-width:300px; color:#94a3b8;">
            <?= htmlspecialchars($g['thumbnail'] ?? '-') ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="warning">
    ⚠️ <strong>Penting:</strong> Hapus file ini setelah selesai!<br>
    Atau kamu bisa langsung <a href="index.php">balik ke website</a> dan cek hasilnya.
  </div>

  <div style="margin-top: 20px;">
    <a href="index.php" style="background:#2563eb; color:white; padding:10px 20px; border-radius:8px; text-decoration:none;">→ Lihat Website</a>
    &nbsp;
    <a href="index.php?page=games" style="background:#1e3a5f; color:white; padding:10px 20px; border-radius:8px; text-decoration:none;">→ Halaman Games</a>
  </div>
</body>
</html>
