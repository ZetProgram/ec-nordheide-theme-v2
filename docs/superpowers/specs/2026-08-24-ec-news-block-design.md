# EC News-Block – Design

## Kontext

Das Theme `ec-nordheide-theme-v2` hat bereits zwei eigene Blöcke, `ec/hero` und `ec/card` (siehe `blocks/hero`, `blocks/card`), beide serverseitig über `render.php` ausgegeben und in `functions.php` registriert. Für den Bereich „Neues aus der Nordheide" im Startseiten-Muster (`functions.php`) steht bisher nur ein Hinweistext, der Redakteur:innen bittet, manuell einen WordPress-„Abfrage-Loop"-Block einzufügen.

Dieses Dokument beschreibt einen neuen, dritten Block `ec/news`, der WordPress-Beiträge automatisch als Karten-Raster anzeigt (Bild, Titel, Datum, Kategorie, Textanfang) und überall im Editor frei einsetzbar ist.

## Ziel

- Ein Block, der Beiträge dynamisch aus WordPress zieht (keine manuell gepflegten Karten).
- Pro Einsatzort einstellbar: Anzahl der Beiträge (Standard 3) und Kategorie-Filter (Standard „Alle").
- Frei auf jeder Seite einsetzbar, nicht nur auf der Startseite.
- Editor-Vorschau zeigt echte, aktuelle Beiträge passend zu den gewählten Einstellungen – kein statischer Platzhalter.

## Nicht-Ziel (bewusst ausgeklammert)

- Keine Paginierung/„Mehr laden" innerhalb des Blocks.
- Kein eingebauter „Alle Beiträge ansehen"-Button (bleibt ein separates Element daneben, wie im bestehenden Muster).
- Keine anderen Taxonomien als die Standard-„Kategorie".
- Kein manuelles Auszug-Feld – der Textanfang wird immer automatisch aus dem Beitragsinhalt geschnitten (siehe Klärung im Gespräch).

## Architektur-Überblick

Wie bei `ec/hero` und `ec/card`:

- `blocks/news/block.json` + `blocks/news/render.php` für die Frontend-Ausgabe (serverseitig, per `register_block_type`).
- Registrierung in `functions.php` neben den beiden bestehenden Blöcken.
- Editor-UI in `assets/js/blocks-editor.js` ergänzt.

**Wichtige Abweichung von der ursprünglichen Annahme:** `blocks-editor.js` vermerkt ausdrücklich, dass `ServerSideRender` bewusst *nicht* verwendet wird, weil es beim Einfügen zu einem Absturz führte („Cannot read properties of null, reading 'addEventListener'"). Hero und Karte bauen ihre Vorschau deshalb synchron in reinem JS nach.

Für `ec/news` reicht eine rein statische Nachbildung nicht, weil die Vorschau die tatsächlich zur Auswahl passenden Beiträge zeigen soll. Lösung: Die Editor-Vorschau lädt die passenden Beiträge direkt über die WordPress-Datenschicht (`wp.data`, `select('core').getEntityRecords(...)`), genau die Art von API, die auch der eingebaute Abfrage-Loop-Block intern nutzt – kein `ServerSideRender`, kein DOM-Ersetzungsmechanismus, also nicht dasselbe Absturzrisiko. Frontend bleibt weiterhin ausschließlich die PHP-Version aus `render.php`, die Vorschau ist eine Annäherung mit echten Daten, keine Pixel-für-Pixel-Referenz.

## Block-Definition (`blocks/news/block.json`)

```json
{
	"apiVersion": 3,
	"name": "ec/news",
	"title": "EC News",
	"category": "ec-nordheide",
	"icon": "grid-view",
	"description": "Zeigt aktuelle WordPress-Beiträge als Karten-Raster - Bild, Titel, Datum, Kategorie, Textanfang.",
	"keywords": [ "news", "beiträge", "artikel", "blog" ],
	"supports": { "html": false },
	"attributes": {
		"postsPerPage": { "type": "number", "default": 3 },
		"categoryId": { "type": "number", "default": 0 }
	},
	"textdomain": "ec-nordheide-v2",
	"editorScript": "ec-nordheide-v2-blocks-editor",
	"render": "file:./render.php"
}
```

`categoryId: 0` bedeutet „Alle Kategorien".

## Rendering (`blocks/news/render.php`)

- `WP_Query` mit `post_type => post`, `post_status => publish`, `posts_per_page` aus dem Attribut, `ignore_sticky_posts => true`, und `cat => $categoryId` nur wenn `$categoryId > 0`.
- Pro Treffer eine Karte, komplett als `<a>` verlinkt zum Beitrag (`get_permalink()`).
- Bild: `has_post_thumbnail()` ? `get_the_post_thumbnail( $id, 'medium_large' )` in einem `.ec-news-card__media`-Wrapper : kein Bild-Element, Karte bekommt die Modifier-Klasse `ec-news-card--no-image`.
- Kopfzeile in der Karte: Datum (`get_the_date()`) und Name der ersten zugeordneten Kategorie (`get_the_category()[0]->name`, falls vorhanden).
- Titel: `get_the_title()`.
- Textanfang: immer automatisch aus `post_content` geschnitten, unabhängig von einem manuell gesetzten Auszug-Feld:
  ```php
  function ec_nordheide_v2_news_excerpt( $post_id, $words = 22 ) {
      $content = get_post_field( 'post_content', $post_id );
      $content = strip_shortcodes( $content );
      $content = wp_strip_all_tags( $content );
      return wp_trim_words( $content, $words, '…' );
  }
  ```
- Kein Treffer (z. B. leere Kategorie) → Block gibt nichts oder einen dezenten Hinweistext im Editor-Kontext aus; im Frontend einfach leer, damit keine kaputte Optik entsteht.
- Escaping durchgehend wie in den bestehenden Blöcken (`esc_html`, `esc_url`, `get_block_wrapper_attributes`).

## Editor-Erfahrung (`assets/js/blocks-editor.js`)

- `InspectorControls` mit:
  - `RangeControl` „Anzahl der Beiträge" (1–9, Standard 3).
  - `SelectControl` „Kategorie", Optionen aus `select('core').getEntityRecords('taxonomy', 'category', { per_page: -1 })`, erster Eintrag immer „Alle Kategorien" (Wert 0).
- Live-Vorschau im `edit()`:
  - `useSelect` lädt `select('core').getEntityRecords('postType', 'post', { per_page: postsPerPage, categories: categoryId || undefined, status: 'publish', _embed: true })`.
  - Solange die Abfrage noch lädt (`records === null`): dezenter Ladehinweis („Beiträge werden geladen …").
  - Keine Treffer: Hinweistext „Keine Beiträge gefunden" statt leerer Fläche.
  - Pro geladenem Beitrag eine Kartenvorschau, die dieselben CSS-Klassen nutzt wie `render.php` (Bild aus `_embedded['wp:featuredmedia']`, Titel aus `title.rendered`, Datum/Kategorie aus `_embedded['wp:term']`, Textanfang client-seitig aus `content.rendered` grob nachgeschnitten). Das ist eine Annäherung an die PHP-Ausgabe, keine exakte Kopie – die tatsächliche Wortgrenze/HTML-Bereinigung kann minimal abweichen, was hier akzeptabel ist, da das Frontend allein von `render.php` bestimmt wird.

## Styling (`style.css`)

Neue Klassen im bestehenden Namensschema, mit vorhandenen Design-Tokens (`--ec-surface`, `--ec-ink`, `--ec-ink-soft`, `--ec-accent`, `--ec-radius-lg`, `--ec-shadow-sm`, `--ec-shadow`):

```css
.ec-news-grid {
	display: grid;
	grid-template-columns: repeat( auto-fit, minmax( 260px, 1fr ) );
	gap: 1.5rem;
}
.ec-news-card {
	display: flex;
	flex-direction: column;
	background: var(--ec-surface);
	border-radius: var(--ec-radius-lg);
	overflow: hidden;
	box-shadow: var(--ec-shadow-sm);
	color: inherit;
	text-decoration: none;
	transition: transform .2s ease, box-shadow .2s ease;
}
.ec-news-card:hover, .ec-news-card:focus-visible {
	transform: translateY(-.3rem);
	box-shadow: var(--ec-shadow);
}
.ec-news-card__media { aspect-ratio: 16 / 10; overflow: hidden; }
.ec-news-card__media img { width: 100%; height: 100%; object-fit: cover; display: block; }
.ec-news-card__body { padding: 1.5rem; display: flex; flex-direction: column; gap: .5rem; }
.ec-news-card__meta {
	font-size: .78rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase;
	color: var(--ec-accent); display: flex; gap: .6rem; flex-wrap: wrap;
}
.ec-news-card__title { margin: 0; font-size: 1.2rem; font-weight: 800; color: var(--ec-ink); }
.ec-news-card__excerpt { margin: 0; font-size: .95rem; color: var(--ec-ink-soft); }
.ec-news-card--no-image .ec-news-card__body { padding-top: 1.75rem; }
```

## Einbindung ins bestehende Muster

Im Startseiten-Muster (`functions.php`, Bereich „Neues aus der Nordheide") ersetzt der neue Block den bisherigen Hinweistext:

```
<!-- wp:ec/news {"postsPerPage":3,"categoryId":0} /-->
```

Der Block bleibt zusätzlich frei aus dem Blockinserter auf jeder Seite einsetzbar (Kategorie „EC Nordheide").

## Testplan

- PHP-Syntaxcheck (`php -l`) für `render.php` und die geänderte `functions.php`.
- Block im Editor einfügen: kein Absturz (bestätigt, dass das ursprüngliche `ServerSideRender`-Problem nicht reproduziert wird).
- Anzahl-Regler ändern → Vorschau und Frontend zeigen jeweils passende Anzahl.
- Kategorie wählen → nur passende Beiträge; zurück auf „Alle" → ungefiltert.
- Beitrag ohne Beitragsbild → Karte ohne Bildbereich, keine optische Lücke.
- Sehr kurzer/sehr langer Beitragstext → Textanfang wird sauber ohne HTML-Reste geschnitten.
- Ganze Karte anklicken → führt zum Beitrag.
- Schmaler Viewport → Raster bricht responsiv um.
- Kategorie mit null Treffern → sauberer Hinweistext statt leerer/kaputter Fläche.
