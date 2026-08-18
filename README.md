# EC Nordheide Theme v2

Erste eigenständige Theme-Grundlage für den neuen EC-Nordheide-Auftritt.

## Ziel

Das Theme übernimmt Präsentation, Layout und Designsystem. Inhalte und eigenständige Funktionen bleiben in WordPress bzw. den vorhandenen Plugins.

## Aktueller Stand

- schwebende Header-Navigation, selbst gehostetes Montserrat, WCAG-AA-Kontrast
- Startseite wird als ganz normale WordPress-Seite mit Blöcken gebaut, kein Extra-Einstellungsformular dafür
- zwei eigene Blöcke: **EC Hero** (Titel, Buttons, Bild oder Marken-Platzhalter) und **EC Karte** (Zielgruppe/Altersgruppe/Team in einem Block)
- fertiges Muster „EC Nordheide: Startseite“ zum Einfügen (Seiten → Neu → Muster einfügen)
- Formatvorlagen für Gruppe/Spalten/Absatz/Button (EC Papier/Dunkel/Akzent, EC Kicker, EC Karten-Streifen, EC Bild-Text-Split, EC Ghost-Button)
- News-Loop aus WordPress-Beiträgen, responsive Navigation
- kleines Backend-Menü „EC Nordheide“ nur noch für site-weite Dinge: Logo, Footer/Links, Farben

## Nächste Schritte

1. Seite „Startseite“ anlegen, Muster „EC Nordheide: Startseite“ einfügen, Inhalte anpassen
2. unter Einstellungen → Lesen die neue Seite als statische Startseite einstellen
3. bestehende Menüs, Mitarbeiter, Slider und Gebetswand anbinden
4. Templates für Orte und Veranstaltungen ergänzen (aktuell Platzhalter im Muster)
5. auf `www.test.ec-nordheide.de` aktivieren und visuell verfeinern

## GitHub-Workflow

Der Entwicklungsbranch ist `development`. Ein Push auf `production` erzeugt automatisch:

1. einen PHP-Syntaxcheck,
2. ein Git-Tag entsprechend der Version in `style.css`,
3. eine installierbare ZIP-Datei,
4. ein GitHub-Release.

WordPress erkennt das Release über den eingebauten Update-Checker. Für ein privates Repository muss auf dem WordPress-Server zusätzlich ein Token in `wp-config.php` hinterlegt werden:

```php
define( 'EC_NORDHEIDE_GITHUB_TOKEN', 'dein-read-only-token' );
```
