<?php
$stmt = $db->prepare("SELECT * FROM games WHERE is_active = 1 ORDER BY name");
$stmt->execute();
$games = $stmt->fetchAll();

$gameEmojis = ['⚔️', '🔥', '🎯', '✨', '🎮', '🏆', '🌟', '💥'];
?>

<div class="container section">
  <div class="section-header">
    <div class="section-tag">🎮 Semua Game</div>
    <h1 class="section-title">Pilih Game untuk Top Up</h1>
    <p class="section-desc">Klik game untuk melihat pilihan paket top up yang tersedia.</p>
  </div>

  <div class="games-grid" style="grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));">
    <?php foreach ($games as $i => $game): ?>
      <a href="index.php?page=topup&game=<?= htmlspecialchars($game['slug']) ?>" class="game-card" style="text-decoration:none; display:block;">
        <div class="game-card-thumb" style="background:linear-gradient(135deg,var(--blue-950),var(--blue-900)); height:140px;">
          <?php if ($game['thumbnail']): ?>
            <img src="<?= htmlspecialchars($game['thumbnail']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
          <?php else: ?>
            <div style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;font-size:3.5rem;">
              <?= $gameEmojis[$i % count($gameEmojis)] ?>
            </div>
          <?php endif; ?>
          <div class="game-card-overlay">
            <span style="color:white;font-size:0.9rem;font-weight:600;">Top Up Sekarang →</span>
          </div>
        </div>
        <div class="game-card-body">
          <div class="game-card-category"><?= htmlspecialchars($game['category']) ?></div>
          <div class="game-card-name" style="font-size:1.05rem; margin-bottom:6px;"><?= htmlspecialchars($game['name']) ?></div>
          <div class="game-card-dev" style="margin-bottom:10px;"><?= htmlspecialchars($game['developer']) ?></div>
          <div style="font-size:0.8rem; color:var(--text-muted);"><?= htmlspecialchars(substr($game['description'], 0, 80)) ?>...</div>
        </div>
      </a>
    <?php endforeach; ?>

    <?php if (empty($games)): ?>
      <div class="card" style="grid-column:1/-1; text-align:center; padding:60px;">
        <div style="font-size:3rem; margin-bottom:16px;">🎮</div>
        <p style="color:var(--text-muted);">Belum ada game tersedia.</p>
      </div>
    <?php endif; ?>
  </div>
</div>
