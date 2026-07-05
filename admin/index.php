<?php
require __DIR__ . '/../inc/config.php';
require __DIR__ . '/../inc/functions.php';
session_start();

$meldung = '';
$fehler  = '';

// ---------- Aktionen ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aktion = $_POST['aktion'] ?? '';

    if ($aktion === 'login') {
        if (hash_equals(ADMIN_PASSWORT, (string)($_POST['passwort'] ?? ''))) {
            session_regenerate_id(true);
            $_SESSION['sola_admin'] = true;
        } else {
            sleep(1); // einfache Bremse gegen Durchprobieren
            $fehler = 'Falsches Passwort.';
        }
    } elseif (!ist_admin()) {
        $fehler = 'Nicht eingeloggt.';
    } else {
        csrf_pruefen();
        $tag = (int)($_POST['tag'] ?? 0);

        if ($aktion === 'logout') {
            session_destroy();
            header('Location: index.php'); exit;

        } elseif ($aktion === 'speichern' && $tag >= 1 && $tag <= ANZAHL_TAGE) {
            $k = kapitel_laden($tag);
            $k['titel'] = trim((string)($_POST['titel'] ?? ''));
            $k['text']  = str_replace("\r\n", "\n", trim((string)($_POST['text'] ?? '')));

            // Bilder hochladen
            if (!empty($_FILES['bilder']['name'][0])) {
                $dir = bilder_dir($tag);
                if (!is_dir($dir)) mkdir($dir, 0775, true);
                foreach ($_FILES['bilder']['name'] as $i => $name) {
                    if ($_FILES['bilder']['error'][$i] !== UPLOAD_ERR_OK) continue;
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    if (!in_array($ext, BILD_FORMATE, true)) { $fehler = 'Format nicht erlaubt: ' . e($name); continue; }
                    if (@getimagesize($_FILES['bilder']['tmp_name'][$i]) === false) { $fehler = 'Keine gültige Bilddatei: ' . e($name); continue; }
                    $neu = date('Ymd-His') . '-' . $i . '-' . preg_replace('/[^a-z0-9_-]/', '', strtolower(pathinfo($name, PATHINFO_FILENAME)));
                    $neu = substr($neu, 0, 80) . '.' . $ext;
                    if (move_uploaded_file($_FILES['bilder']['tmp_name'][$i], $dir . '/' . $neu)) {
                        $k['bilder'][] = $neu;
                    }
                }
            }
            kapitel_speichern($tag, $k);
            $meldung = 'Tag ' . $tag . ' gespeichert.';

        } elseif ($aktion === 'bild_loeschen' && $tag >= 1 && $tag <= ANZAHL_TAGE) {
            $datei = basename((string)($_POST['datei'] ?? ''));
            $k = kapitel_laden($tag);
            $k['bilder'] = array_values(array_filter($k['bilder'], fn($b) => $b !== $datei));
            kapitel_speichern($tag, $k);
            @unlink(bilder_dir($tag) . '/' . $datei);
            $meldung = 'Bild gelöscht.';

        } elseif ($aktion === 'modus') {
            $e = einstellungen_laden();
            $e['freischalt_modus'] = (int)($_POST['freischalt_modus'] ?? 0) === 1 ? 1 : 0;
            einstellungen_speichern($e);
            $meldung = 'Freischalt-Modus gespeichert.';
        }
    }
}

$bearbeite = isset($_GET['tag']) ? (int)$_GET['tag'] : 0;
if ($bearbeite < 1 || $bearbeite > ANZAHL_TAGE) $bearbeite = 0;
$einstellungen = einstellungen_laden();
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin | Sola 2026 Story</title>
<style>
  :root { --rot:#d5263b; --blau:#10069F; --ink:#141619; --papier:#fafaf7; --linie:#e3e2dc; }
  * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',Helvetica,Arial,sans-serif; }
  body { background:var(--papier); color:var(--ink); }
  .topbar { display:flex; justify-content:space-between; align-items:center; padding:16px 28px; border-bottom:1px solid var(--linie); font-size:.85rem; letter-spacing:2px; text-transform:uppercase; }
  .topbar b { color:var(--rot); }
  main { max-width:820px; margin:0 auto; padding:40px 20px; }
  h1 { font-family:Georgia,serif; margin-bottom:24px; }
  .meldung { background:#e7f6e7; border:1px solid #b6dcb6; color:#256325; padding:10px 14px; border-radius:4px; margin-bottom:18px; }
  .fehler { background:#fdecec; border:1px solid #f2b8b8; color:#8f1f1f; padding:10px 14px; border-radius:4px; margin-bottom:18px; }
  .liste { border:1px solid var(--linie); border-radius:6px; background:#fff; }
  .zeile { display:grid; grid-template-columns:60px 1fr auto auto; gap:14px; align-items:center; padding:14px 18px; border-bottom:1px solid var(--linie); }
  .zeile:last-child { border-bottom:none; }
  .zeile .nr { font-family:Georgia,serif; font-size:1.5rem; color:var(--rot); }
  .zeile .meta { font-size:.78rem; color:#888; text-transform:uppercase; letter-spacing:1px; }
  .status { font-size:.75rem; padding:3px 10px; border-radius:20px; letter-spacing:.5px; }
  .status.offen { background:#e7f6e7; color:#256325; }
  .status.zu { background:#eee; color:#777; }
  .status.leer { background:#fff4e0; color:#9a6b12; }
  .btn { display:inline-block; background:var(--blau); color:#fff; border:none; padding:8px 16px; border-radius:4px; cursor:pointer; font-size:.85rem; text-decoration:none; }
  .btn:hover { opacity:.9; }
  .btn.rot { background:var(--rot); }
  .btn.grau { background:#888; }
  label { display:block; font-size:.8rem; text-transform:uppercase; letter-spacing:1px; color:#666; margin:18px 0 6px; }
  input[type=text], input[type=password], textarea { width:100%; padding:10px 12px; border:1px solid #ccc; border-radius:4px; font-size:1rem; font-family:inherit; background:#fff; }
  textarea { min-height:280px; font-family:Georgia,serif; line-height:1.6; }
  .bilder { display:grid; grid-template-columns:repeat(auto-fill,minmax(130px,1fr)); gap:12px; margin-top:10px; }
  .bild { position:relative; }
  .bild img { width:100%; height:100px; object-fit:cover; border-radius:4px; display:block; }
  .bild button { position:absolute; top:4px; right:4px; background:rgba(0,0,0,.65); color:#fff; border:none; border-radius:3px; padding:2px 7px; cursor:pointer; }
  .box { background:#fff; border:1px solid var(--linie); border-radius:6px; padding:22px 24px; margin-bottom:24px; }
  .login { max-width:360px; margin:80px auto; }
  .hinweis { font-size:.85rem; color:#777; margin-top:8px; }
  .kopf { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
</style>
</head>
<body>
<div class="topbar"><span><b>SOLA</b> 2026 · ADMIN</span><span><a href="../index.php" style="color:var(--blau)">Website ansehen →</a></span></div>
<main>

<?php if ($meldung): ?><div class="meldung"><?= e($meldung) ?></div><?php endif; ?>
<?php if ($fehler): ?><div class="fehler"><?= e($fehler) ?></div><?php endif; ?>

<?php if (!ist_admin()): ?>
  <div class="box login">
    <h1>Login</h1>
    <form method="post">
      <input type="hidden" name="aktion" value="login">
      <label>Passwort</label>
      <input type="password" name="passwort" autofocus>
      <p style="margin-top:16px"><button class="btn" type="submit">Einloggen</button></p>
    </form>
  </div>

<?php elseif ($bearbeite): ?>
  <?php $k = kapitel_laden($bearbeite); $datum = tag_datum($bearbeite)->format('j.n.Y'); ?>
  <div class="kopf">
    <h1>Tag <?= $bearbeite ?> · <?= $datum ?></h1>
    <a class="btn grau" href="index.php">← Übersicht</a>
  </div>
  <div class="box">
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="aktion" value="speichern">
      <input type="hidden" name="tag" value="<?= $bearbeite ?>">
      <label>Titel</label>
      <input type="text" name="titel" value="<?= e($k['titel']) ?>" placeholder="z. B. Der geheimnisvolle Brief">
      <label>Story-Text</label>
      <textarea name="text" placeholder="Die Geschichte dieses Tages … (Leerzeile = neuer Absatz, «## Titel» = Zwischentitel)"><?= e($k['text']) ?></textarea>
      <p class="hinweis">Leerzeile = neuer Absatz · Zeile mit <code>## </code> am Anfang = Zwischentitel</p>
      <label>Bilder hinzufügen (JPG, PNG, GIF, WebP – mehrere möglich)</label>
      <input type="file" name="bilder[]" multiple accept=".jpg,.jpeg,.png,.gif,.webp">
      <p style="margin-top:20px">
        <button class="btn" type="submit">Speichern</button>
        <a class="btn grau" href="../kapitel.php?tag=<?= $bearbeite ?>" target="_blank" style="margin-left:8px">Vorschau</a>
      </p>
    </form>

    <?php if (!empty($k['bilder'])): ?>
    <label>Hochgeladene Bilder</label>
    <div class="bilder">
      <?php foreach ($k['bilder'] as $b): ?>
      <div class="bild">
        <img src="../bild.php?tag=<?= $bearbeite ?>&datei=<?= rawurlencode($b) ?>" alt="">
        <form method="post" onsubmit="return confirm('Bild wirklich löschen?')">
          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
          <input type="hidden" name="aktion" value="bild_loeschen">
          <input type="hidden" name="tag" value="<?= $bearbeite ?>">
          <input type="hidden" name="datei" value="<?= e($b) ?>">
          <button type="submit">✕</button>
        </form>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

<?php else: ?>
  <div class="kopf">
    <h1>Story-Verwaltung</h1>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="aktion" value="logout">
      <button class="btn grau" type="submit">Logout</button>
    </form>
  </div>

  <div class="liste">
    <?php for ($i = 1; $i <= ANZAHL_TAGE; $i++):
        $k = kapitel_laden($i);
        $offen = time() >= freischalt_zeit($i); ?>
    <div class="zeile">
      <span class="nr"><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></span>
      <div>
        <strong><?= e($k['titel'] ?: '— ohne Titel —') ?></strong>
        <div class="meta"><?= tag_datum($i)->format('j.n.Y') ?> · <?= count($k['bilder']) ?> Bild(er)</div>
      </div>
      <span class="status <?= !hat_inhalt($k) ? 'leer' : ($offen ? 'offen' : 'zu') ?>">
        <?= !hat_inhalt($k) ? 'Leer' : ($offen ? 'Freigeschaltet' : 'Wartet auf Mitternacht') ?>
      </span>
      <a class="btn" href="?tag=<?= $i ?>">Bearbeiten</a>
    </div>
    <?php endfor; ?>
  </div>

  <div class="box" style="margin-top:24px">
    <h3 style="font-family:Georgia,serif">Freischalt-Zeitpunkt</h3>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <input type="hidden" name="aktion" value="modus">
      <p style="margin-top:12px"><label style="display:inline;text-transform:none;letter-spacing:0;font-size:.95rem;color:var(--ink)">
        <input type="radio" name="freischalt_modus" value="0" <?= $einstellungen['freischalt_modus'] == 0 ? 'checked' : '' ?>>
        Um 00:00 Uhr <strong>am Tag selbst</strong> (Tag 1 ist ab 5.7. 00:00 sichtbar)
      </label></p>
      <p style="margin-top:8px"><label style="display:inline;text-transform:none;letter-spacing:0;font-size:.95rem;color:var(--ink)">
        <input type="radio" name="freischalt_modus" value="1" <?= $einstellungen['freischalt_modus'] == 1 ? 'checked' : '' ?>>
        Um Mitternacht <strong>nach dem Tag</strong> (Tag 1 wird in der Nacht auf den 6.7. sichtbar)
      </label></p>
      <p style="margin-top:14px"><button class="btn" type="submit">Speichern</button></p>
    </form>
    <p class="hinweis">Als eingeloggter Admin siehst du auf der Website immer alle Kapitel – Besucher sehen nur die freigeschalteten.</p>
  </div>
<?php endif; ?>

</main>
</body>
</html>
