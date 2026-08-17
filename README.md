# EC Nordheide Theme v2

Erste eigenständige Theme-Grundlage für den neuen EC-Nordheide-Auftritt.

## Ziel

Das Theme übernimmt Präsentation, Layout und Designsystem. Inhalte und eigenständige Funktionen bleiben in WordPress bzw. den vorhandenen Plugins.

## Aktueller Stand

- schwebende Header-Navigation
- Hero-Bereich mit konfigurierbarem Hintergrundbild-Token
- Off-White-/Dark-Sektionen
- orange CTA-Komponenten
- Hexagon-Grundform
- News-Loop aus WordPress-Beiträgen
- responsive Navigation
- eigenes Backend-Menü „EC Nordheide“ für Logo, Hero, CTA, Footer, Links und Farben

## Nächste Schritte

1. echtes EC-Logo im Header verwenden
2. Hero-Bild und Startseiteninhalte aus WordPress konfigurierbar machen
3. bestehende Menüs, Mitarbeiter, Slider und Gebetswand anbinden
4. Templates für Seiten, Orte, Veranstaltungen und Beiträge ergänzen
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
