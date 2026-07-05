<?php
// ===== Sola 2026 Story – Konfiguration =====

// WICHTIG: Passwort vor dem Hochladen ändern!
define('ADMIN_PASSWORT', 'Sola.2026');

// Lagerdaten
define('LAGER_START', '2026-07-05');   // erster Lagertag
define('ANZAHL_TAGE', 7);              // 5.7. bis 11.7.2026

// Zeitzone (für die Freischaltung um 21:00 Uhr)
date_default_timezone_set('Europe/Zurich');

// Pfade
define('DATA_DIR', __DIR__ . '/../data');

// Erlaubte Bildformate
define('BILD_FORMATE', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
