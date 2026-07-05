<?php
// ===== Sola 2026 Story – Hilfsfunktionen =====

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// ---------- Einstellungen ----------
// freischalt_modus: 0 = Kapitel öffnet um 00:00 am Tag selbst
//                   1 = Kapitel öffnet um Mitternacht NACH dem Tag
function einstellungen_laden() {
    $std = ['freischalt_modus' => 0];
    $f = DATA_DIR . '/einstellungen.json';
    if (is_file($f)) {
        $j = json_decode(file_get_contents($f), true);
        if (is_array($j)) return array_merge($std, $j);
    }
    return $std;
}

function einstellungen_speichern($e) {
    if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0775, true);
    file_put_contents(DATA_DIR . '/einstellungen.json', json_encode($e, JSON_PRETTY_PRINT));
}

// ---------- Datum & Freischaltung ----------
function tag_datum($tag) {
    return (new DateTime(LAGER_START . ' 00:00:00'))->modify('+' . ($tag - 1) . ' days');
}

function freischalt_zeit($tag) {
    $e = einstellungen_laden();
    $offset = ($tag - 1) + (int)$e['freischalt_modus'];
    return (new DateTime(LAGER_START . ' 00:00:00'))->modify('+' . $offset . ' days')->getTimestamp();
}

function ist_admin() {
    return !empty($_SESSION['sola_admin']);
}

function ist_frei($tag) {
    return ist_admin() || time() >= freischalt_zeit($tag);
}

// ---------- Kapitel ----------
function kapitel_laden($tag) {
    $std = ['titel' => '', 'text' => '', 'bilder' => []];
    $f = DATA_DIR . '/kapitel/tag-' . (int)$tag . '.json';
    if (is_file($f)) {
        $j = json_decode(file_get_contents($f), true);
        if (is_array($j)) return array_merge($std, $j);
    }
    return $std;
}

function kapitel_speichern($tag, $k) {
    $dir = DATA_DIR . '/kapitel';
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    file_put_contents($dir . '/tag-' . (int)$tag . '.json', json_encode($k, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function hat_inhalt($k) {
    return trim($k['titel']) !== '' || trim($k['text']) !== '' || !empty($k['bilder']);
}

function bilder_dir($tag) {
    return DATA_DIR . '/bilder/tag-' . (int)$tag;
}

// Text als Absätze ausgeben. Zeilen mit "## " am Anfang werden Zwischentitel.
function text_als_html($text) {
    $out = '';
    foreach (preg_split('/\R{2,}/u', trim($text)) as $absatz) {
        $absatz = trim($absatz);
        if ($absatz === '') continue;
        if (str_starts_with($absatz, '## ')) {
            $out .= '<h3>' . e(substr($absatz, 3)) . '</h3>' . "\n";
        } else {
            $out .= '<p>' . nl2br(e($absatz)) . '</p>' . "\n";
        }
    }
    return $out;
}

// ---------- CSRF ----------
function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}

function csrf_pruefen() {
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
        http_response_code(400);
        exit('Ungültige Anfrage (CSRF). Bitte Seite neu laden.');
    }
}
