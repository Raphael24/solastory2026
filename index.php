<?php
require __DIR__ . '/inc/config.php';
require __DIR__ . '/inc/functions.php';
session_start();

$naechste = null;
$tage = [];
for ($i = 1; $i <= ANZAHL_TAGE; $i++) {
    $frei = time() >= freischalt_zeit($i); // Countdown öffentlich, unabhängig vom Admin-Login
    $tage[] = [
        'nr'      => $i,
        'datum'   => tag_datum($i),
        'frei'    => ist_frei($i),
        'kapitel' => kapitel_laden($i),
    ];
    if (!$frei && $naechste === null) $naechste = freischalt_zeit($i);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sola 2026 – Die Story | Cevi Weinfelden</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="topbar"><span><b>SOLA</b> 2026 · STORY</span><span>Cevi Weinfelden</span></div>

<section class="hero">
  <div class="kicker">Ein Kapitel pro Tag</div>
  <h1>Die Geschichte<br>des <em>Sola 2026</em></h1>
  <p>Jeden Tag um Mitternacht öffnet sich ein neues Kapitel der Lagergeschichte.</p>
  <?php if ($naechste !== null): ?>
  <div class="next"><span class="dot"></span> Nächstes Kapitel in <span id="countdown" data-ziel="<?= $naechste * 1000 ?>">…</span></div>
  <?php endif; ?>
</section>

<section class="chapters">
<?php foreach ($tage as $t):
    $nr2 = str_pad($t['nr'], 2, '0', STR_PAD_LEFT);
    $datum = $t['datum']->format('j.n.Y');
    if ($t['frei'] && hat_inhalt($t['kapitel'])): ?>
  <a class="row" href="kapitel.php?tag=<?= $t['nr'] ?>">
    <span class="num"><?= $nr2 ?></span>
    <div><h3><?= e($t['kapitel']['titel'] ?: 'Kapitel ' . $t['nr']) ?></h3>
    <div class="meta"><?= $datum ?> · Freigeschaltet</div></div>
    <span class="arrow">→</span>
  </a>
    <?php elseif ($t['frei']): ?>
  <div class="row locked">
    <span class="num"><?= $nr2 ?></span>
    <div><h3>Folgt in Kürze</h3>
    <div class="meta"><?= $datum ?> · Kapitel wird noch geschrieben …</div></div>
    <span class="arrow">✎</span>
  </div>
    <?php else: ?>
  <div class="row locked">
    <span class="num"><?= $nr2 ?></span>
    <div><h3>Noch verschlossen</h3>
    <div class="meta"><?= $datum ?> · Ab Mitternacht</div></div>
    <span class="arrow">🔒</span>
  </div>
    <?php endif;
endforeach; ?>
</section>

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
