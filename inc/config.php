<?php
// ===== Sola 2026 Story – Konfiguration =====

// WICHTIG: Passwort vor dem Hochladen ändern!
define('ADMIN_PASSWORT', 'sola2026-bitte-aendern');

// Lagerdaten
define('LAGER_START', '2026-07-05');   // erster Lagertag
define('ANZAHL_TAGE', 7);              // 5.7. bis 11.7.2026

// Zeitzone (für die Mitternacht-Freischaltung)
date_default_timezone_set('Europe/Zurich');

// Pfade
define('DATA_DIR', __DIR__ . '/../data');

// Erlaubte Bildformate
define('BILD_FORMATE', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
