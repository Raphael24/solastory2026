# Sola 2026 Story-Website – Anleitung

## Was ist das?

Eine Website mit einem Kapitel pro Lagertag (5.7.–11.7.2026). Jedes Kapitel
wird automatisch um 21:00 Uhr freigeschaltet — serverseitig, also nicht
austricksbar. Inhalte pflegst du über das Admin-Dashboard direkt im Browser.

## Vor dem Hochladen: Passwort ändern!

Öffne `inc/config.php` und ändere die Zeile:

    define('ADMIN_PASSWORT', 'sola2026-bitte-aendern');

Dort kannst du auch Lagerstart und Anzahl Tage anpassen.

## Auf Hostinger installieren

1. In hPanel einloggen → **Dateimanager** (oder FTP)
2. Den gesamten Inhalt des Ordners `website/` nach `public_html/` hochladen
   (oder in einen Unterordner, z. B. `public_html/story/`)
3. Fertig. Es braucht keine Datenbank — alles wird in `data/` gespeichert.

Voraussetzung: PHP 8 (bei Hostinger Standard). Prüfen unter
hPanel → Websites → PHP-Konfiguration.

## Benutzung

- **Website:** `deine-domain.ch/` (bzw. `/story/`)
- **Admin:** `deine-domain.ch/admin/` → Passwort eingeben
- Pro Tag: Titel + Text eingeben, Bilder hochladen, Speichern.
  Leerzeile im Text = neuer Absatz.
- **Freischalt-Zeitpunkt** im Dashboard wählbar:
  - *Am Tag selbst* (21:00): Kapitel von Tag 1 erscheint am 5.7. um 21:00 Uhr.
  - *Am Tag danach* (21:00): Kapitel von Tag 1 erscheint am 6.7. um 21:00 Uhr.
- Als eingeloggter Admin siehst du immer alle Kapitel (praktisch für die Vorschau).

## Sicherheit

- Kapiteltexte und Bilder liegen in `data/` — dieser Ordner ist per
  `.htaccess` gesperrt, Bilder laufen durch `bild.php`, das die
  Freischaltung prüft. Niemand kann vorab spicken.
- Uploads werden auf echte Bildformate (JPG, PNG, GIF, WebP) geprüft.

## Lokal testen (optional)

Mit installiertem PHP im Ordner `website/` ausführen:

    php -S localhost:8000

Dann http://localhost:8000 öffnen.
