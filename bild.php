<?php
// Liefert Bilder nur aus, wenn der Tag freigeschaltet ist (oder Admin eingeloggt).
require __DIR__ . '/inc/config.php';
require __DIR__ . '/inc/functions.php';
session_start();

$tag   = (int)($_GET['tag'] ?? 0);
$datei = basename((string)($_GET['datei'] ?? ''));

if ($tag < 1 || $tag > ANZAHL_TAGE || $datei === '' || !ist_frei($tag)) {
    http_response_code(403);
    exit('Gesperrt.');
}

$pfad = bilder_dir($tag) . '/' . $datei;
$ext  = strtolower(pathinfo($datei, PATHINFO_EXTENSION));
$mime = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
         'gif' => 'image/gif', 'webp' => 'image/webp'][$ext] ?? null;

if ($mime === null || !is_file($pfad)) {
    http_response_code(404);
    exit('Nicht gefunden.');
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($pfad));
header('Cache-Control: public, max-age=3600');
readfile($pfad);
