<?php
require __DIR__ . '/inc/config.php';
require __DIR__ . '/inc/functions.php';
session_start();

$tag = (int)($_GET['tag'] ?? 0);
if ($tag < 1 || $tag > ANZAHL_TAGE) { header('Location: index.php'); exit; }

$datum = tag_datum($tag)->format('j.n.Y');
$frei  = ist_frei($tag);
$k     = $frei ? kapitel_laden($tag) : null;
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $frei && $k ? e($k['titel'] ?: 'Tag ' . $tag) : 'Noch verschlossen' ?> | Sola 2026 Story</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="topbar"><a href="index.php"><span><b>SOLA</b> 2026 · STORY</span></a><span>Cevi Weinfelden</span></div>

<?php if (!$frei): ?>
<section class="gesperrt">
  <div class="schloss">🔒</div>
  <h1>Dieses Kapitel ist noch verschlossen</h1>
  <p>Tag <?= $tag ?> (<?= $datum ?>) wird um Mitternacht freigeschaltet.</p>
  <div class="next"><span class="dot"></span> Freischaltung in <span id="countdown" data-ziel="<?= freischalt_zeit($tag) * 1000 ?>">…</span></div>
  <p style="margin-top:30px"><a href="index.php" style="color:var(--blau);border-bottom:1.5px solid var(--blau)">← Zurück zur Übersicht</a></p>
</section>

<?php elseif (!hat_inhalt($k)): ?>
<section class="gesperrt">
  <div class="schloss">✎</div>
  <h1>Dieses Kapitel folgt in Kürze</h1>
  <p>Die Geschichte von Tag <?= $tag ?> (<?= $datum ?>) wird gerade geschrieben. Schau später nochmals vorbei!</p>
  <p style="margin-top:30px"><a href="index.php" style="color:var(--blau);border-bottom:1.5px solid var(--blau)">← Zurück zur Übersicht</a></p>
</section>

<?php else: ?>
<article class="artikel">
  <div class="kicker">Tag <?= $tag ?> · <?= $datum ?></div>
  <h1><?= e($k['titel'] ?: 'Kapitel ' . $tag) ?></h1>
  <div class="inhalt"><?= text_als_html($k['text']) ?></div>
  <?php if (!empty($k['bilder'])): ?>
  <div class="galerie">
    <?php foreach ($k['bilder'] as $b): $url = 'bild.php?tag=' . $tag . '&datei=' . rawurlencode($b); ?>
    <a href="<?= e($url) ?>" target="_blank"><img src="<?= e($url) ?>" alt="Foto Tag <?= $tag ?>" loading="lazy"></a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</article>

<nav class="kapitel-nav">
  <span><?php if ($tag > 1 && ist_frei($tag - 1)): ?><a href="kapitel.php?tag=<?= $tag - 1 ?>">← Tag <?= $tag - 1 ?></a><?php endif; ?></span>
  <a href="index.php">Übersicht</a>
  <span><?php if ($tag < ANZAHL_TAGE && ist_frei($tag + 1)): ?><a href="kapitel.php?tag=<?= $tag + 1 ?>">Tag <?= $tag + 1 ?> →</a><?php endif; ?></span>
</nav>
<?php endif; ?>

<footer>CEVI WEINFELDEN — SOLA 2026</footer>

<script>
(function () {
  var el = document.getElementById('countdown');
  if (!el) return;
  var ziel = parseInt(el.dataset.ziel, 10);
  function tick() {
    var diff = ziel - Date.now();
    if (diff <= 0) { location.reload(); return; }
    var s = Math.floor(diff / 1000);
    var t = Math.floor(s / 86400), h = Math.floor(s % 86400 / 3600),
        m = Math.floor(s % 3600 / 60), sec = s % 60;
    function p(n) { return String(n).padStart(2, '0'); }
    el.textContent = (t > 0 ? t + ' Tag' + (t > 1 ? 'en ' : ' ') : '') + p(h) + ':' + p(m) + ':' + p(sec);
    setTimeout(tick, 1000);
  }
  tick();
})();
</script>
</body>
</html>
