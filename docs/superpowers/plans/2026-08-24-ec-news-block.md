# EC News-Block Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Einen neuen Gutenberg-Block `ec/news` bauen, der WordPress-Beiträge dynamisch als Karten-Raster zeigt (Bild, Datum, Kategorie, Titel, automatisch gekürzter Textanfang), mit pro Einsatzort einstellbarer Anzahl und Kategorie, frei auf jeder Seite einsetzbar.

**Architektur:** Serverseitig gerenderter Block nach dem Muster von `ec/hero`/`ec/card` (`block.json` + `render.php`, Registrierung in `functions.php`). Die Editor-Vorschau nutzt **kein** `ServerSideRender` (dokumentierter Absturzgrund in der bestehenden Codebasis), sondern lädt echte Beiträge über `wp.data`/`useSelect` und baut die Vorschau synchron in JavaScript nach, analog zu den bestehenden Blöcken.

**Tech-Stack:** PHP 8 (WordPress Block API, `WP_Query`), reines JavaScript ohne Build-Schritt (`wp.element`, `wp.blocks`, `wp.blockEditor`, `wp.components`, `wp.data`), CSS im bestehenden Namensschema (`style.css`).

## Global Constraints

- Block-Name `ec/news`, Block-Kategorie „EC Nordheide" (`ec-nordheide`), Attribute `postsPerPage` (number, Standard `3`) und `categoryId` (number, Standard `0` = „Alle Kategorien").
- Kein `ServerSideRender` im Editor – Grund siehe `assets/js/blocks-editor.js:8` ("Cannot read properties of null, reading 'addEventListener'"). Editor-Vorschau läuft über `wp.data`.
- Textanfang wird **immer** automatisch aus `post_content` geschnitten (~22 Wörter, WordPress-Ellipse `…`), nie das manuelle Auszug-Feld.
- Beitrag ohne Beitragsbild → kein Bildbereich, Karte bekommt zusätzlich die Klasse `ec-newsfeed-card--no-image`.
- Die ganze Karte ist ein `<a>`-Link zum Beitrag (nicht nur der Titel).
- Frontend-Wahrheit ist ausschließlich `render.php`; die Editor-Vorschau ist eine Annäherung mit echten Daten, keine exakte Kopie.
- Design-Tokens aus `style.css` verwenden: `--ec-surface`, `--ec-ink`, `--ec-ink-soft`, `--ec-accent`, `--ec-radius-lg`, `--ec-shadow-sm`, `--ec-shadow`.
- **Korrektur nach Task-2-Review:** `style.css` enthält bereits eine ältere `.ec-news-card`/`.ec-news__list`-Familie, die aktiv von `index.php`, `archive.php` und `search.php` für die normale Blog-Beitragsliste genutzt wird. Das war beim ursprünglichen Entwurf nicht geprüft worden und hätte zu kollidierenden CSS-Regeln geführt. Deshalb nutzt der `ec/news`-Block einen eigenen Namensraum: `ec-newsfeed-grid`/`ec-newsfeed-card` (statt `ec-news-grid`/`ec-news-card`). Die Blog-Vorlagen (`index.php`, `archive.php`, `search.php`) bleiben unverändert.
- **Kein lokales WordPress vorhanden** in dieser Umgebung (nur XAMPP ohne installierte Seite, kein `wp-env`). Die Browser-/Editor-Prüfungen in Task 3 und Task 5 müssen manuell auf einer WordPress-Instanz mit aktivem Theme durchgeführt werden (lokal installieren oder die Test-Seite `www.test.ec-nordheide.de`), sobald eine verfügbar ist. Alles andere (PHP-Syntax, Dateiinhalt) lässt sich ohne WordPress prüfen.

---

### Task 1: Block-Grundgerüst und Frontend-Rendering

**Files:**
- Create: `blocks/news/block.json`
- Create: `blocks/news/render.php`
- Modify: `functions.php` (Block-Registrierung neben `ec/hero`/`ec/card`)

**Interfaces:**
- Produces: Block `ec/news` mit Attributen `postsPerPage` (number, Default `3`) und `categoryId` (number, Default `0`). Frontend-Markup: `div.ec-newsfeed-grid > a.ec-newsfeed-card(.ec-newsfeed-card--no-image) > div.ec-newsfeed-card__media > img` (optional) `+ div.ec-newsfeed-card__body > div.ec-newsfeed-card__meta > span×(1-2), h3.ec-newsfeed-card__title, p.ec-newsfeed-card__excerpt`.

- [ ] **Step 1: `blocks/news/block.json` anlegen**

```json
{
	"$schema": "https://schemas.wp.org/trunk/block.json",
	"apiVersion": 3,
	"name": "ec/news",
	"title": "EC News",
	"category": "ec-nordheide",
	"icon": "grid-view",
	"description": "Zeigt aktuelle WordPress-Beiträge als Karten-Raster - Bild, Titel, Datum, Kategorie, Textanfang.",
	"keywords": [ "news", "beiträge", "artikel", "blog" ],
	"supports": {
		"html": false
	},
	"attributes": {
		"postsPerPage": { "type": "number", "default": 3 },
		"categoryId": { "type": "number", "default": 0 }
	},
	"textdomain": "ec-nordheide-v2",
	"editorScript": "ec-nordheide-v2-blocks-editor",
	"render": "file:./render.php"
}
```

- [ ] **Step 2: JSON-Syntax von `block.json` prüfen**

Run: `php -r "var_dump(json_decode(file_get_contents('blocks/news/block.json')) !== null);"` (im Ordner `ec-nordheide-theme-v2` ausführen)
Expected: `bool(true)`

- [ ] **Step 3: `blocks/news/render.php` anlegen**

```php
<?php
/**
 * Server-Render für den ec/news Block.
 * $attributes, $content, $block stehen automatisch zur Verfügung.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Textanfang für die News-Karte: immer automatisch aus post_content
 * geschnitten, unabhängig von einem manuell gesetzten Auszug-Feld.
 */
function ec_nordheide_v2_news_excerpt( $post_id, $words = 22 ) {
	$content = get_post_field( 'post_content', $post_id );
	$content = strip_shortcodes( $content );
	$content = wp_strip_all_tags( $content );
	return wp_trim_words( $content, $words, '…' );
}

$posts_per_page = ! empty( $attributes['postsPerPage'] ) ? max( 1, (int) $attributes['postsPerPage'] ) : 3;
$category_id    = ! empty( $attributes['categoryId'] ) ? (int) $attributes['categoryId'] : 0;

$query_args = array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => $posts_per_page,
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
);
if ( $category_id ) {
	$query_args['cat'] = $category_id;
}

$ec_news_query = new WP_Query( $query_args );

if ( ! $ec_news_query->have_posts() ) {
	return;
}

$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => 'ec-newsfeed-grid' ) );
?>
<div <?php echo $wrapper_attributes; /* phpcs:ignore, von WP escaped */ ?>>
	<?php
	while ( $ec_news_query->have_posts() ) :
		$ec_news_query->the_post();
		$ec_news_post_id = get_the_ID();
		$ec_news_cats     = get_the_category( $ec_news_post_id );
		$ec_news_cat_name = ! empty( $ec_news_cats ) ? $ec_news_cats[0]->name : '';
		$ec_news_has_img  = has_post_thumbnail( $ec_news_post_id );
		$ec_news_card_cls = 'ec-newsfeed-card' . ( $ec_news_has_img ? '' : ' ec-newsfeed-card--no-image' );
		?>
		<a class="<?php echo esc_attr( $ec_news_card_cls ); ?>" href="<?php echo esc_url( get_permalink( $ec_news_post_id ) ); ?>">
			<?php if ( $ec_news_has_img ) : ?>
				<div class="ec-newsfeed-card__media">
					<?php echo get_the_post_thumbnail( $ec_news_post_id, 'medium_large' ); ?>
				</div>
			<?php endif; ?>
			<div class="ec-newsfeed-card__body">
				<div class="ec-newsfeed-card__meta">
					<span><?php echo esc_html( get_the_date( '', $ec_news_post_id ) ); ?></span>
					<?php if ( $ec_news_cat_name ) : ?><span><?php echo esc_html( $ec_news_cat_name ); ?></span><?php endif; ?>
				</div>
				<h3 class="ec-newsfeed-card__title"><?php echo esc_html( get_the_title( $ec_news_post_id ) ); ?></h3>
				<p class="ec-newsfeed-card__excerpt"><?php echo esc_html( ec_nordheide_v2_news_excerpt( $ec_news_post_id ) ); ?></p>
			</div>
		</a>
		<?php
	endwhile;
	wp_reset_postdata();
	?>
</div>
```

- [ ] **Step 4: PHP-Syntax von `render.php` prüfen**

Run: `php -l blocks/news/render.php` (im Ordner `ec-nordheide-theme-v2` ausführen)
Expected: `No syntax errors detected in blocks/news/render.php`

- [ ] **Step 5: Block in `functions.php` registrieren**

In `functions.php` im bestehenden `init`-Hook, der `ec/hero` und `ec/card` registriert, direkt nach der Zeile `register_block_type( get_theme_file_path( 'blocks/card' ) );` ergänzen:

```php
			register_block_type( get_theme_file_path( 'blocks/hero' ) );
			register_block_type( get_theme_file_path( 'blocks/card' ) );
			register_block_type( get_theme_file_path( 'blocks/news' ) );
```

- [ ] **Step 6: PHP-Syntax von `functions.php` prüfen**

Run: `php -l functions.php` (im Ordner `ec-nordheide-theme-v2` ausführen)
Expected: `No syntax errors detected in functions.php`

- [ ] **Step 7: Commit**

```bash
git add blocks/news/block.json blocks/news/render.php functions.php
git commit -m "Add ec/news block scaffold and frontend rendering"
```

---

### Task 2: Kartenraster-Styling

**Files:**
- Modify: `style.css` (neue Regeln direkt nach den bestehenden `.ec-person-card`-Regeln)

**Interfaces:**
- Consumes: CSS-Klassen aus Task 1 (`ec-newsfeed-grid`, `ec-newsfeed-card`, `ec-newsfeed-card--no-image`, `ec-newsfeed-card__media`, `ec-newsfeed-card__body`, `ec-newsfeed-card__meta`, `ec-newsfeed-card__title`, `ec-newsfeed-card__excerpt`).
- Produces: fertiges Kartenraster-Aussehen, das Task 3 (Editor-Vorschau) 1:1 wiederverwendet.

- [ ] **Step 1: CSS-Regeln ergänzen**

In `style.css` nach der Zeile

```css
.ec-person-card a.ec-text-link { display: inline-block; margin-top: .5rem; font-size: .92rem; }
```

folgenden Block einfügen:

```css

/* ---------------------------------------------------------------
   EC News: Karten-Raster für den ec/news Block
--------------------------------------------------------------- */
.ec-newsfeed-grid {
	display: grid;
	grid-template-columns: repeat( auto-fit, minmax( 260px, 1fr ) );
	gap: 1.5rem;
}
.ec-newsfeed-card {
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
.ec-newsfeed-card:hover, .ec-newsfeed-card:focus-visible {
	transform: translateY(-.3rem);
	box-shadow: var(--ec-shadow);
}
.ec-newsfeed-card__media { aspect-ratio: 16 / 10; overflow: hidden; }
.ec-newsfeed-card__media img { width: 100%; height: 100%; object-fit: cover; display: block; }
.ec-newsfeed-card__body { padding: 1.5rem; display: flex; flex-direction: column; gap: .5rem; }
.ec-newsfeed-card__meta {
	font-size: .78rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase;
	color: var(--ec-accent); display: flex; gap: .6rem; flex-wrap: wrap;
}
.ec-newsfeed-card__title { margin: 0; font-size: 1.2rem; font-weight: 800; color: var(--ec-ink); }
.ec-newsfeed-card__excerpt { margin: 0; font-size: .95rem; color: var(--ec-ink-soft); }
.ec-newsfeed-card--no-image .ec-newsfeed-card__body { padding-top: 1.75rem; }
```

- [ ] **Step 2: Geschweifte Klammern zählen (einfache Syntaxprüfung ohne CSS-Linter im Projekt)**

Run (im Ordner `ec-nordheide-theme-v2`): `grep -o "{" style.css | wc -l && grep -o "}" style.css | wc -l`
Expected: beide Zahlen sind identisch (vorher schon gleich, durch den neuen Block bleibt es so, da jede geöffnete Regel geschlossen wurde).

- [ ] **Step 3: Commit**

```bash
git add style.css
git commit -m "Add ec-newsfeed-card grid styles"
```

---

### Task 3: Editor-UI mit Live-Vorschau

**Files:**
- Modify: `functions.php` (Skript-Abhängigkeit `wp-data` ergänzen)
- Modify: `assets/js/blocks-editor.js` (Hilfsfunktionen + `registerBlockType( 'ec/news', ... )`)

**Interfaces:**
- Consumes: Attribute `postsPerPage`/`categoryId` (Task 1), CSS-Klassen (Task 2).
- Produces: funktionierendes `edit()` für `ec/news` mit Inspector-Feldern „Anzahl der Beiträge" (Regler 1–9) und „Kategorie" (Dropdown inkl. „Alle Kategorien"), Live-Vorschau mit echten Beitragsdaten über `wp.data`.

- [ ] **Step 1: `wp-data` als Skript-Abhängigkeit ergänzen**

In `functions.php` bei `wp_register_script( 'ec-nordheide-v2-blocks-editor', ... )` das Abhängigkeits-Array erweitern:

Vorher:
```php
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
```

Nachher:
```php
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-data' ),
```

- [ ] **Step 2: PHP-Syntax von `functions.php` prüfen**

Run: `php -l functions.php`
Expected: `No syntax errors detected in functions.php`

- [ ] **Step 3: Neue Top-Level-Variablen in `blocks-editor.js` ergänzen**

Direkt nach der bestehenden Zeile `var SelectControl = wp.components.SelectControl;` ergänzen:

```js
	var RangeControl = wp.components.RangeControl;
	var useSelect = wp.data.useSelect;
```

- [ ] **Step 4: Hilfsfunktionen für die News-Vorschau ergänzen**

Direkt nach der bestehenden Funktion `imageField(...)` (vor `registerBlockType( 'ec/hero', ...)`) ergänzen:

```js
	function formatNewsDate( dateString ) {
		if ( ! dateString ) {
			return '';
		}
		try {
			return new Date( dateString ).toLocaleDateString();
		} catch ( e ) {
			return dateString;
		}
	}

	function stripHtml( html ) {
		var div = document.createElement( 'div' );
		div.innerHTML = html || '';
		return div.textContent || div.innerText || '';
	}

	function trimWords( text, count ) {
		var words = text.trim().split( /\s+/ ).filter( Boolean );
		if ( words.length <= count ) {
			return words.join( ' ' );
		}
		return words.slice( 0, count ).join( ' ' ) + '…';
	}
```

- [ ] **Step 5: `ec/news`-Block registrieren**

Direkt nach dem Ende der bestehenden `registerBlockType( 'ec/card', { ... } );`-Anweisung (vor dem Kommentar „EC Sektion") ergänzen:

```js
	registerBlockType( 'ec/news', {
		edit: function ( props ) {
			var a = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps( { className: 'ec-newsfeed-grid-editor-wrap' } );

			var categories = useSelect( function ( select ) {
				return select( 'core' ).getEntityRecords( 'taxonomy', 'category', { per_page: -1 } );
			}, [] );

			var posts = useSelect(
				function ( select ) {
					var query = {
						per_page: a.postsPerPage || 3,
						status: 'publish',
						_embed: true,
					};
					if ( a.categoryId ) {
						query.categories = a.categoryId;
					}
					return select( 'core' ).getEntityRecords( 'postType', 'post', query );
				},
				[ a.postsPerPage, a.categoryId ]
			);

			var categoryOptions = [ { label: __( 'Alle Kategorien', 'ec-nordheide-v2' ), value: 0 } ];
			if ( categories ) {
				categories.forEach( function ( term ) {
					categoryOptions.push( { label: term.name, value: term.id } );
				} );
			}

			var body;
			if ( null === posts ) {
				body = el( 'p', {}, __( 'Beiträge werden geladen …', 'ec-nordheide-v2' ) );
			} else if ( 0 === posts.length ) {
				body = el( 'p', {}, __( 'Keine Beiträge gefunden.', 'ec-nordheide-v2' ) );
			} else {
				body = el(
					'div',
					{ className: 'ec-newsfeed-grid' },
					posts.map( function ( post ) {
						var media = post._embedded && post._embedded[ 'wp:featuredmedia' ] && post._embedded[ 'wp:featuredmedia' ][ 0 ];
						var imageUrl = media && media.source_url ? media.source_url : '';
						var terms = post._embedded && post._embedded[ 'wp:term' ] ? post._embedded[ 'wp:term' ][ 0 ] : [];
						var catName = terms && terms[ 0 ] ? terms[ 0 ].name : '';
						var excerpt = trimWords( stripHtml( post.content && post.content.rendered ), 22 );
						return el(
							'div',
							{
								className: 'ec-newsfeed-card' + ( imageUrl ? '' : ' ec-newsfeed-card--no-image' ),
								key: post.id,
							},
							imageUrl
								? el( 'div', { className: 'ec-newsfeed-card__media' }, el( 'img', { src: imageUrl, alt: '' } ) )
								: null,
							el(
								'div',
								{ className: 'ec-newsfeed-card__body' },
								el(
									'div',
									{ className: 'ec-newsfeed-card__meta' },
									el( 'span', {}, formatNewsDate( post.date ) ),
									catName ? el( 'span', {}, catName ) : null
								),
								el( 'h3', { className: 'ec-newsfeed-card__title' }, stripHtml( post.title && post.title.rendered ) ),
								el( 'p', { className: 'ec-newsfeed-card__excerpt' }, excerpt )
							)
						);
					} )
				);
			}

			return el(
				Fragment,
				{},
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __( 'Einstellungen', 'ec-nordheide-v2' ), initialOpen: true },
						el( RangeControl, {
							label: __( 'Anzahl der Beiträge', 'ec-nordheide-v2' ),
							value: a.postsPerPage,
							onChange: setter( setAttributes, 'postsPerPage' ),
							min: 1,
							max: 9,
						} ),
						el( SelectControl, {
							label: __( 'Kategorie', 'ec-nordheide-v2' ),
							value: a.categoryId,
							options: categoryOptions,
							onChange: function ( value ) {
								setAttributes( { categoryId: parseInt( value, 10 ) || 0 } );
							},
						} )
					)
				),
				el( 'div', blockProps, body )
			);
		},
		save: function () {
			return null;
		},
	} );

```

- [ ] **Step 6: Manuelle Prüfung im Block-Editor**

Voraussetzung: eine WordPress-Instanz mit diesem Theme aktiv (siehe Hinweis unter „Global Constraints").
1. Neue Seite anlegen, „EC News" über den Blockinserter einfügen.
2. Erwartet: kein Absturz, kurzer Ladehinweis, danach 3 Kartenvorschauen mit echten Beiträgen.
3. In der Seitenleiste die Kategorie wechseln → Vorschau aktualisiert sich auf passende Beiträge.
4. Regler „Anzahl der Beiträge" auf 5 stellen → 5 Karten in der Vorschau.

- [ ] **Step 7: Commit**

```bash
git add functions.php assets/js/blocks-editor.js
git commit -m "Add ec/news editor UI with live wp.data preview"
```

---

### Task 4: Muster-Integration

**Files:**
- Modify: `functions.php` (Startseiten-Muster: Platzhaltertext ersetzen; veralteten Hinweis-Kommentar aktualisieren)

**Interfaces:**
- Consumes: Block `ec/news` aus Task 1 (Attribute `postsPerPage`, `categoryId`).

- [ ] **Step 1: Platzhaltertext im Muster ersetzen**

Im Muster „EC Nordheide: Startseite" im Bereich „Neues aus der Nordheide" folgenden Block:

```
<!-- wp:paragraph {"style":{"typography":{"fontStyle":"italic"}}} -->
<p style="font-style:italic">Tipp: Füge hier über das Block-Menü (+) einen "Abfrage-Loop"-Block ein und stelle ihn auf 3 Beiträge, um eure neuesten Artikel automatisch zu zeigen.</p>
<!-- /wp:paragraph -->
```

ersetzen durch:

```
<!-- wp:ec/news {"postsPerPage":3,"categoryId":0} /-->
```

- [ ] **Step 2: Veralteten Hinweis-Kommentar aktualisieren**

Den Docblock-Kommentar über der `register_block_pattern`-Registrierung, der aktuell mit

```
 * Für "Neues aus der Nordheide" bitte zusätzlich einen normalen
 * Abfrage-Loop-Block einfügen (WordPress bringt dafür eigene,
 * geprüfte Muster mit) - das hier nachzubauen wäre fehleranfälliger
 * als das eingebaute WordPress-Muster zu verwenden.
```

endet, ersetzen durch:

```
 * "Neues aus der Nordheide" nutzt den eigenen ec/news-Block (siehe
 * blocks/news) - zeigt automatisch die neuesten Beiträge, Anzahl und
 * Kategorie sind direkt am Block einstellbar.
```

- [ ] **Step 3: PHP-Syntax prüfen**

Run: `php -l functions.php`
Expected: `No syntax errors detected in functions.php`

- [ ] **Step 4: Commit**

```bash
git add functions.php
git commit -m "Wire ec/news into the homepage pattern, drop stale query-loop hint"
```

---

### Task 5: Manuelle End-to-End-Prüfung

**Files:** keine Code-Änderungen (reine Verifikation entlang des Testplans aus der Spezifikation).

Voraussetzung: WordPress-Instanz mit diesem Theme aktiv, ein paar Testbeiträge mit unterschiedlichen Kategorien, mindestens einer davon ohne Beitragsbild.

- [ ] **Step 1:** Neue Seite anlegen, „EC News"-Block direkt einfügen (ohne Muster). Erwartet: 3 aktuelle Beiträge, alle Kategorien, Karten mit Bild/Datum/Kategorie/Titel/Textanfang.
- [ ] **Step 2:** Regler „Anzahl der Beiträge" auf 5 stellen, Seite veröffentlichen/aktualisieren. Erwartet: sowohl Vorschau als auch veröffentlichte Seite zeigen 5 Beiträge.
- [ ] **Step 3:** Kategorie wählen, die nur wenige Beiträge hat. Erwartet: nur passende Beiträge erscheinen. Zurück auf „Alle Kategorien" → wieder ungefiltert.
- [ ] **Step 4:** Testbeitrag ohne Beitragsbild in die Auswahl bringen. Erwartet: Karte ohne Bildbereich, kein leerer Platz, Body-Bereich rutscht sauber nach oben.
- [ ] **Step 4b:** Je einen Testbeitrag mit sehr kurzem Text (unter 22 Wörtern) und mit sehr langem Text (mehrere Absätze, inkl. z. B. einem Shortcode oder HTML-Link) in die Auswahl bringen. Erwartet: kurzer Text erscheint vollständig ohne „…", langer Text wird sauber nach ca. 22 Wörtern mit „…" abgeschnitten, keine HTML-Tags oder Shortcode-Reste im Text sichtbar.
- [ ] **Step 5:** Kategorie ohne jeglichen Beitrag wählen. Erwartet: Editor zeigt „Keine Beiträge gefunden.", Frontend zeigt schlicht nichts (kein Fehler, kein leerer Rahmen).
- [ ] **Step 6:** Auf der veröffentlichten Seite eine ganze Karte anklicken (nicht nur den Titel). Erwartet: Navigation zum jeweiligen Beitrag.
- [ ] **Step 7:** Browserfenster auf Handy-Breite verkleinern. Erwartet: Raster bricht einspaltig um, keine überlaufenden Inhalte.
- [ ] **Step 8:** Muster „EC Nordheide: Startseite" auf einer neuen Seite einfügen. Erwartet: Bereich „Neues aus der Nordheide" zeigt direkt den fertig konfigurierten `ec/news`-Block, kein Platzhaltertext mehr.
- [ ] **Step 9:** Falls bei einem der Schritte 1–8 ein Fehler auffällt: entsprechende Datei korrigieren, betroffenen Schritt wiederholen, dann committen. Wenn alle Schritte ohne Korrektur bestehen, ist kein weiterer Commit nötig.
