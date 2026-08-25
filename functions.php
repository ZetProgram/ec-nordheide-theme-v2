<?php
/**
 * EC Nordheide Theme v2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EC_NORDHEIDE_V2_VERSION', '0.6.8' );

// GitHub-basierte Theme-Updates. Das Repository wird als Release-Quelle verwendet.
$ec_nordheide_update_checker = get_theme_file_path( 'lib/plugin-update-checker/plugin-update-checker.php' );
if ( file_exists( $ec_nordheide_update_checker ) ) {
	require_once $ec_nordheide_update_checker;
	if ( class_exists( '\YahnisElsts\PluginUpdateChecker\v5\PucFactory' ) ) {
		$ec_nordheide_updater = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
			'https://github.com/ZetProgram/ec-nordheide-theme-v2',
			get_theme_root() . '/ec-nordheide-theme-v2',
			'ec-nordheide-theme-v2'
		);
		// Stable-Versionen kommen aus den GitHub-Releases, nicht aus dem Quell-Branch.
		$ec_nordheide_updater->getVcsApi()->enableReleaseAssets( '/\.zip$/i' );
		if ( defined( 'EC_NORDHEIDE_GITHUB_TOKEN' ) && EC_NORDHEIDE_GITHUB_TOKEN ) {
			$ec_nordheide_updater->setAuthentication( EC_NORDHEIDE_GITHUB_TOKEN );
		}
	}
}

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support( 'custom-logo', array( 'height' => 80, 'width' => 240, 'flex-height' => true, 'flex-width' => true ) );
		// Damit der Hero-Block und Bereiche mit Hintergrundfarbe im
		// Block-Editor auf volle Breite gestellt werden können.
		add_theme_support( 'align-wide' );
		// Das Theme-Stylesheet auch im Block-Editor laden, damit Vorschau
		// und Frontend gleich aussehen (Schrift, Farben, Karten).
		add_theme_support( 'editor-styles' );
		add_editor_style( 'style.css' );

		register_nav_menus(
			array(
				'primary' => __( 'Hauptmenü', 'ec-nordheide-v2' ),
			)
		);
	}
);

/**
 * EC-Blöcke: Hero und Karte.
 * Beide werden serverseitig gerendert (PHP), damit Editor-Vorschau
 * (per ServerSideRender) und Frontend garantiert gleich aussehen.
 */
add_action(
	'init',
	function () {
		wp_register_script(
			'ec-nordheide-v2-blocks-editor',
			get_theme_file_uri( 'assets/js/blocks-editor.js' ),
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-data' ),
			EC_NORDHEIDE_V2_VERSION,
			true
		);
		wp_set_script_translations( 'ec-nordheide-v2-blocks-editor', 'ec-nordheide-v2' );

		register_block_type( get_theme_file_path( 'blocks/hero' ) );
		register_block_type( get_theme_file_path( 'blocks/card' ) );
		register_block_type( get_theme_file_path( 'blocks/news' ) );
	}
);

add_filter(
	'block_categories_all',
	function ( $categories ) {
		return array_merge(
			array(
				array(
					'slug'  => 'ec-nordheide',
					'title' => __( 'EC Nordheide', 'ec-nordheide-v2' ),
					'icon'  => null,
				),
			),
			$categories
		);
	}
);

/**
 * Fertige Formatvorlagen für Standard-Blöcke, damit Bereiche, Karten-Reihen
 * und Buttons mit dem EC-Design gebaut werden können, ohne eigene Blöcke
 * dafür zu brauchen. Erscheinen im Editor als Stil-Auswahl am jeweiligen
 * Block (Seitenleiste -> Stile). Die passenden CSS-Regeln stehen in style.css.
 */
add_action(
	'init',
	function () {
		register_block_style(
			'core/group',
			array(
				'name'  => 'ec-paper',
				'label' => __( 'EC Papier', 'ec-nordheide-v2' ),
			)
		);
		register_block_style(
			'core/group',
			array(
				'name'  => 'ec-dark',
				'label' => __( 'EC Dunkel', 'ec-nordheide-v2' ),
			)
		);
		register_block_style(
			'core/group',
			array(
				'name'  => 'ec-accent',
				'label' => __( 'EC Akzent', 'ec-nordheide-v2' ),
			)
		);
		register_block_style(
			'core/group',
			array(
				'name'  => 'ec-card-grid',
				'label' => __( 'EC Karten-Raster (gleich groß)', 'ec-nordheide-v2' ),
			)
		);
		register_block_style(
			'core/group',
			array(
				'name'  => 'ec-card-strip',
				'label' => __( 'EC Karten-Streifen (scrollbar)', 'ec-nordheide-v2' ),
			)
		);
		register_block_style(
			'core/columns',
			array(
				'name'  => 'ec-split',
				'label' => __( 'EC Bild-Text-Split', 'ec-nordheide-v2' ),
			)
		);
		register_block_style(
			'core/paragraph',
			array(
				'name'  => 'ec-kicker',
				'label' => __( 'EC Kicker', 'ec-nordheide-v2' ),
			)
		);
		register_block_style(
			'core/button',
			array(
				'name'  => 'ec-ghost',
				'label' => __( 'EC Ghost (transparent)', 'ec-nordheide-v2' ),
			)
		);
	}
);

/**
 * Fertige Startseite zum Einfügen: Seiten -> Neu -> Muster einfügen
 * -> "EC Nordheide: Startseite". Danach ganz normal im Editor anpassen.
 * "Neues aus der Nordheide" nutzt den eigenen ec/news-Block (siehe
 * blocks/news) - zeigt automatisch die neuesten Beiträge, Anzahl und
 * Kategorie sind direkt am Block einstellbar.
 */
add_action(
	'init',
	function () {
		$home = home_url();
		register_block_pattern(
			'ec-nordheide-v2/startseite',
			array(
				'title'       => __( 'EC Nordheide: Startseite', 'ec-nordheide-v2' ),
				'description' => __( 'Hero, Zielgruppen, Altersgruppen, Wer wir sind, Veranstaltungen, News-Platz, Team und Mitmachen - fertig zusammengesetzt, danach frei anpassbar.', 'ec-nordheide-v2' ),
				'categories'  => array( 'ec-nordheide' ),
				'content'     => <<<HTML
<!-- wp:ec/hero {"align":"full","kicker":"EC Nordheide","title":"Glaube, Gemeinschaft & Leben","subtitle":"entschieden für Christus","primaryLabel":"Finde deine Gruppe","primaryUrl":"{$home}/unsere-orte/","secondaryLabel":"Alle Veranstaltungen","secondaryUrl":"{$home}/veranstaltungen/"} /-->

<!-- wp:group {"align":"full","className":"is-style-ec-paper","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull is-style-ec-paper">
<!-- wp:paragraph {"className":"is-style-ec-kicker"} -->
<p class="is-style-ec-kicker">Dein Einstieg</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"ec-heading"} -->
<h2 class="wp-block-heading ec-heading">Was suchst du?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"ec-copy"} -->
<p class="ec-copy">Egal, ob du neu dabei bist, dein Kind begleiten möchtest oder selbst mitarbeiten willst: Hier findest du deinen nächsten Schritt.</p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"is-style-ec-card-grid","layout":{"type":"constrained"}} -->
<div class="wp-block-group is-style-ec-card-grid">
<!-- wp:ec/card {"cardType":"audience","variant":"leaf","title":"Für Jugendliche","text":"Finde deine Gruppe, Menschen in deinem Alter und Angebote in deiner Nähe.","url":"{$home}/unsere-orte/","badge":"01"} /-->

<!-- wp:ec/card {"cardType":"audience","variant":"paper","title":"Für Eltern","text":"Erfahre, wie wir junge Menschen begleiten, stärken und in ihrer Entwicklung fördern.","url":"{$home}/fuer-eltern/","badge":"02"} /-->

<!-- wp:ec/card {"cardType":"audience","variant":"soft","title":"Für Mitarbeitende","text":"Du möchtest dich einbringen? Entdecke Möglichkeiten, Teil der Bewegung zu werden.","url":"{$home}/mitarbeit/","badge":"03"} /-->

<!-- wp:ec/card {"cardType":"audience","variant":"dark","title":"Über den EC","text":"Lerne unsere Geschichte, unsere Werte und die Menschen hinter dem EC Nordheide kennen.","url":"{$home}/ueber-uns/","badge":"04"} /-->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"is-style-ec-accent","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull is-style-ec-accent">
<!-- wp:heading {"className":"ec-heading"} -->
<h2 class="wp-block-heading ec-heading">Deine Gruppe. Dein Ort. Deine Menschen.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"ec-copy"} -->
<p class="ec-copy">Bei uns findest du Gemeinschaft, in der du gesehen wirst, Fragen stellen kannst und deinen Glauben mitten im Leben entdeckst.</p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"is-style-ec-card-strip","layout":{"type":"constrained"}} -->
<div class="wp-block-group is-style-ec-card-strip">
<!-- wp:ec/card {"cardType":"age","title":"Jungschar","text":"Abenteuer, Gemeinschaft und erste Schritte im Glauben.","ageRange":"8–12 Jahre","linkLabel":"Entdecken","url":"{$home}/jungschar/"} /-->

<!-- wp:ec/card {"cardType":"age","title":"Teenkreis","text":"Echte Freundschaften, gute Fragen und gemeinsam unterwegs sein.","ageRange":"12–16 Jahre","linkLabel":"Entdecken","url":"{$home}/teenkreis/"} /-->

<!-- wp:ec/card {"cardType":"age","title":"Jugendkreis","text":"Glaube, Leben und Verantwortung mit anderen Jugendlichen teilen.","ageRange":"16–18 Jahre","linkLabel":"Entdecken","url":"{$home}/jugendkreis/"} /-->

<!-- wp:ec/card {"cardType":"age","title":"Junge Erwachsene","text":"Gemeinschaft, Tiefgang und Raum für deinen nächsten Schritt.","ageRange":"18+ Jahre","linkLabel":"Entdecken","url":"{$home}/junge-erwachsene/"} /-->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"is-style-ec-paper","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull is-style-ec-paper">
<!-- wp:group {"className":"ec-split","layout":{"type":"default"}} -->
<div class="wp-block-group ec-split">
<!-- wp:html -->
<div class="ec-about-preview__media" aria-hidden="true"></div>
<!-- /wp:html -->

<!-- wp:group {"className":"ec-about-preview__copy","layout":{"type":"constrained"}} -->
<div class="wp-block-group ec-about-preview__copy">
<!-- wp:paragraph -->
<p>Wir sind der EC Nordheide: junge Menschen, engagierte Mitarbeitende und Gemeinden, die gemeinsam unterwegs sind.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Wir glauben, dass jeder Mensch wertvoll ist, dass Jesus Christus Leben verändert und dass Gemeinschaft stark macht.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><a class="ec-text-link" href="{$home}/ueber-uns/">Mehr über uns →</a></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"is-style-ec-dark","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull is-style-ec-dark">
<!-- wp:group {"className":"ec-split ec-split--center","layout":{"type":"default"}} -->
<div class="wp-block-group ec-split ec-split--center">
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
<!-- wp:heading {"className":"ec-heading"} -->
<h2 class="wp-block-heading ec-heading">Kommende Veranstaltungen</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"ec-copy"} -->
<p class="ec-copy">Freizeiten, Aktionen und Treffen, bei denen du dabei sein kannst. Die vollständige Übersicht folgt hier, sobald die Anmeldung steht.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:group {"className":"ec-event-placeholder","layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-group ec-event-placeholder">
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="{$home}/veranstaltungen/">Alle Veranstaltungen</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"is-style-ec-paper","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull is-style-ec-paper">
<!-- wp:heading {"className":"ec-heading"} -->
<h2 class="wp-block-heading ec-heading">Neues aus der Nordheide</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"ec-copy"} -->
<p class="ec-copy">Geschichten, Einblicke und aktuelle Neuigkeiten aus unserem Kreisverband.</p>
<!-- /wp:paragraph -->

<!-- wp:ec/news {"postsPerPage":3,"categoryId":0} /-->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"is-style-ec-paper","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull is-style-ec-paper">
<!-- wp:heading {"className":"ec-heading"} -->
<h2 class="wp-block-heading ec-heading">Wer sich um was kümmert</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"ec-copy"} -->
<p class="ec-copy">Der EC Nordheide lebt von Menschen, die Verantwortung übernehmen. Das ist eure erste Anlaufstelle für Fragen.</p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"is-style-ec-card-grid","layout":{"type":"constrained"}} -->
<div class="wp-block-group is-style-ec-card-grid">
<!-- wp:ec/card {"cardType":"people","title":"Kreisleitung","text":"Der EC Nordheide als Ganzes: Ausrichtung, Vernetzung und Ansprechpartner für Gemeinden.","linkLabel":"Kontakt aufnehmen","url":"{$home}/ueber-uns/"} /-->

<!-- wp:ec/card {"cardType":"people","title":"Jungschar-Team","text":"Zuständig für alle Jungschargruppen und Angebote für 8- bis 12-Jährige.","linkLabel":"Kontakt aufnehmen","url":"{$home}/ueber-uns/"} /-->

<!-- wp:ec/card {"cardType":"people","title":"Teen- & Jugendkreis","text":"Begleitet Teenkreis und Jugendkreis durch Alltag, Freizeiten und Glaubensfragen.","linkLabel":"Kontakt aufnehmen","url":"{$home}/ueber-uns/"} /-->

<!-- wp:ec/card {"cardType":"people","title":"Mitarbeit & Freiwillige","text":"Erster Kontakt, wenn du selbst mitarbeiten oder ein Team unterstützen willst.","linkLabel":"Kontakt aufnehmen","url":"{$home}/mitarbeit/"} /-->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"is-style-ec-accent","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull is-style-ec-accent">
<!-- wp:heading {"textAlign":"center","className":"ec-heading"} -->
<h2 class="wp-block-heading ec-heading has-text-align-center">Du kannst einen Unterschied machen.</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","className":"ec-copy"} -->
<p class="ec-copy has-text-align-center">Ob durch deine Zeit, dein Gebet oder deine Unterstützung: Danke, dass du Teil unserer Bewegung bist.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons">
<!-- wp:button {"className":"is-style-ec-ghost"} -->
<div class="wp-block-button is-style-ec-ghost"><a class="wp-block-button__link wp-element-button" href="{$home}/spenden/">Unterstütze uns</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
HTML,
			)
		);
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style( 'ec-nordheide-v2', get_stylesheet_uri(), array(), EC_NORDHEIDE_V2_VERSION );
		wp_enqueue_script( 'ec-nordheide-v2-navigation', get_theme_file_uri( 'assets/js/navigation.js' ), array(), EC_NORDHEIDE_V2_VERSION, true );
	}
);

// Die beiden Schriftschnitte für den ersten Bildschirm (Fließtext, fette Headline)
// früh laden. Der Rest folgt regulär per @font-face.
add_action(
	'wp_head',
	function () {
		printf(
			'<link rel="preload" as="font" type="font/woff2" href="%1$s" crossorigin="anonymous">',
			esc_url( get_theme_file_uri( 'fonts/montserrat-regular.woff2' ) )
		);
		printf(
			'<link rel="preload" as="font" type="font/woff2" href="%1$s" crossorigin="anonymous">',
			esc_url( get_theme_file_uri( 'fonts/montserrat-black.woff2' ) )
		);
	},
	5
);

add_action(
	'wp_head',
	function () {
		$options = ec_nordheide_v2_get_options();
		printf(
			'<style>:root{--ec-leaf:%1$s;--ec-paper:%2$s;--ec-ink:%3$s;--ec-accent:%4$s}</style>',
			esc_attr( $options['color_orange'] ),
			esc_attr( $options['color_paper'] ),
			esc_attr( $options['color_ink'] ),
			esc_attr( $options['color_accent'] )
		);
	}
);

add_action(
	'wp_head',
	function () {
		printf( '<meta name="theme-color" content="%s">', esc_attr( ec_nordheide_v2_get_options()['color_paper'] ) );
	}
);

add_filter(
	'nav_menu_link_attributes',
	function ( $atts, $item ) {
		if ( in_array( 'menu-item-button', (array) $item->classes, true ) ) {
			$atts['class'] = trim( ( $atts['class'] ?? '' ) . ' menu-item--cta' );
		}
		return $atts;
	},
	10,
	2
);

function ec_nordheide_v2_fallback_menu() {
	$links = array(
		__( 'Über uns', 'ec-nordheide-v2' ) => home_url( '/ueber-uns/' ),
		__( 'Unsere Orte', 'ec-nordheide-v2' ) => home_url( '/unsere-orte/' ),
		__( 'Veranstaltungen', 'ec-nordheide-v2' ) => home_url( '/veranstaltungen/' ),
		__( 'Changelog', 'ec-nordheide-v2' ) => home_url( '/changelog/' ),
	);
	echo '<ul>';
	foreach ( $links as $label => $url ) {
		echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
	}
	echo '<li class="menu-item--cta"><a href="' . esc_url( home_url( '/spenden/' ) ) . '">' . esc_html__( 'Unterstütze uns', 'ec-nordheide-v2' ) . '</a></li>';
	echo '</ul>';
}

function ec_nordheide_v2_logo_url() {
	$options = ec_nordheide_v2_get_options();
	if ( ! empty( $options['logo_id'] ) ) {
		return esc_url( wp_get_attachment_image_url( (int) $options['logo_id'], 'medium' ) );
	}
	return esc_url( get_theme_file_uri( 'assets/images/brand/ec-logo-wide-left-black.png' ) );
}

/**
 * Nur noch site-weite Einstellungen (Logo, Farben, Footer). Die Startseite
 * selbst wird als WordPress-Seite mit den EC-Blöcken gebaut (Seiten -> Neu
 * -> Muster "EC Nordheide: Startseite"), nicht mehr hier.
 *
 * Ein Eintrag pro pflegbarem Feld: type steuert sowohl das Admin-Formular
 * als auch die Sanitisierung.
 * Typen: text, textarea, url, color, media
 */
function ec_nordheide_v2_field_schema() {
	return array(
		// Branding
		'logo_id' => array( 'type' => 'media', 'group' => 'branding', 'label' => __( 'Logo', 'ec-nordheide-v2' ), 'default' => 0 ),

		// Footer
		'footer_claim'  => array( 'type' => 'text', 'group' => 'footer', 'label' => __( 'Footer-Unterzeile', 'ec-nordheide-v2' ), 'default' => 'Glaube, Gemeinschaft & Leben.' ),
		'contact_text'  => array( 'type' => 'textarea', 'group' => 'footer', 'label' => __( 'Kontakttext', 'ec-nordheide-v2' ), 'default' => "Kreisverband Nordheide\n\u{201E}Entschieden f\u{00FC}r Christus\u{201C} e.V." ),
		'instagram_url' => array( 'type' => 'url', 'group' => 'footer', 'label' => __( 'Instagram-URL', 'ec-nordheide-v2' ), 'default' => '' ),
		'spotify_url'   => array( 'type' => 'url', 'group' => 'footer', 'label' => __( 'Spotify-URL', 'ec-nordheide-v2' ), 'default' => '' ),
		'linktree_url'  => array( 'type' => 'url', 'group' => 'footer', 'label' => __( 'Linktree-URL', 'ec-nordheide-v2' ), 'default' => '' ),

		// Farben
		'color_orange' => array( 'type' => 'color', 'group' => 'colors', 'label' => __( 'Blattgrün (Karten & Badges, dunkler Text)', 'ec-nordheide-v2' ), 'default' => '#92c355' ),
		'color_accent' => array( 'type' => 'color', 'group' => 'colors', 'label' => __( 'Waldgrün (Buttons & Links, heller Text)', 'ec-nordheide-v2' ), 'default' => '#3f6b28' ),
		'color_paper'  => array( 'type' => 'color', 'group' => 'colors', 'label' => __( 'Off-White (Hintergrund)', 'ec-nordheide-v2' ), 'default' => '#f4f9ee' ),
		'color_ink'    => array( 'type' => 'color', 'group' => 'colors', 'label' => __( 'Dunkle Farbe (Text & dunkle Flächen)', 'ec-nordheide-v2' ), 'default' => '#213214' ),
	);
}

function ec_nordheide_v2_get_options() {
	$defaults = wp_list_pluck( ec_nordheide_v2_field_schema(), 'default' );
	return wp_parse_args( get_option( 'ec_nordheide_v2_options', array() ), $defaults );
}

add_action( 'admin_menu', 'ec_nordheide_v2_add_settings_page' );
function ec_nordheide_v2_add_settings_page() {
	add_menu_page(
		__( 'EC Nordheide', 'ec-nordheide-v2' ),
		__( 'EC Nordheide', 'ec-nordheide-v2' ),
		'manage_options',
		'ec-nordheide-v2',
		'ec_nordheide_v2_render_settings_page',
		'dashicons-admin-customizer',
		61
	);
}

add_action( 'admin_init', 'ec_nordheide_v2_register_settings' );
function ec_nordheide_v2_register_settings() {
	register_setting(
		'ec_nordheide_v2_options_group',
		'ec_nordheide_v2_options',
		array(
			'sanitize_callback' => 'ec_nordheide_v2_sanitize_options',
		)
	);
}

function ec_nordheide_v2_sanitize_options( $input ) {
	$old = ec_nordheide_v2_get_options();
	$out = $old;
	foreach ( ec_nordheide_v2_field_schema() as $key => $field ) {
		$raw = $input[ $key ] ?? '';
		switch ( $field['type'] ) {
			case 'media':
				$out[ $key ] = absint( $raw );
				break;
			case 'url':
				$out[ $key ] = esc_url_raw( $raw );
				break;
			case 'textarea':
				$out[ $key ] = sanitize_textarea_field( $raw );
				break;
			case 'color':
				$out[ $key ] = sanitize_hex_color( $raw ) ?: $old[ $key ];
				break;
			case 'text':
			default:
				$out[ $key ] = sanitize_text_field( $raw );
				break;
		}
	}
	return $out;
}

/**
 * Sichtbare Reihenfolge und Überschriften der Einstellungsseite.
 * Jede Sektion zieht ihre Felder aus einer oder mehreren Schema-Gruppen.
 */
function ec_nordheide_v2_settings_sections() {
	return array(
		array(
			'title'       => __( 'Branding', 'ec-nordheide-v2' ),
			'groups'      => array( 'branding' ),
			'description' => __( 'Die Startseite selbst baut ihr unter Seiten -> Neu -> Muster einfügen -> "EC Nordheide: Startseite" und passt sie dort ganz normal im Editor an.', 'ec-nordheide-v2' ),
		),
		array( 'title' => __( 'Footer & Links', 'ec-nordheide-v2' ), 'groups' => array( 'footer' ) ),
		array(
			'title'       => __( 'Farben', 'ec-nordheide-v2' ),
			'groups'      => array( 'colors' ),
			'description' => __( 'Zwei Grüntöne mit fester Rolle: das helle Blattgrün steht für Karten und Badges (immer mit dunklem Text), das tiefe Waldgrün für Buttons und Links (immer mit hellem Text). So bleibt der Kontrast überall lesbar.', 'ec-nordheide-v2' ),
		),
	);
}

function ec_nordheide_v2_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$options = ec_nordheide_v2_get_options();
	$schema  = ec_nordheide_v2_field_schema();
	$sections = ec_nordheide_v2_settings_sections();
	wp_enqueue_media();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'EC Nordheide: Theme-Einstellungen', 'ec-nordheide-v2' ); ?></h1>
		<p><?php esc_html_e( 'Hier pflegt ihr alle Texte, Karten und Links der Startseite direkt im Backend, ohne den Code anzufassen. Beiträge, Veranstaltungen und Mitarbeiter bleiben eigene WordPress-Inhalte.', 'ec-nordheide-v2' ); ?></p>
		<p class="ec-v2-jumplist">
			<?php foreach ( $sections as $section ) : ?>
				<a href="#ec-v2-<?php echo esc_attr( sanitize_title( $section['title'] ) ); ?>"><?php echo esc_html( $section['title'] ); ?></a>
			<?php endforeach; ?>
		</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'ec_nordheide_v2_options_group' ); ?>
			<?php foreach ( $sections as $section ) : ?>
				<h2 id="ec-v2-<?php echo esc_attr( sanitize_title( $section['title'] ) ); ?>"><?php echo esc_html( $section['title'] ); ?></h2>
				<?php if ( ! empty( $section['description'] ) ) : ?>
					<p class="description"><?php echo esc_html( $section['description'] ); ?></p>
				<?php endif; ?>
				<table class="form-table" role="presentation">
					<?php
					foreach ( $schema as $key => $field ) {
						if ( in_array( $field['group'], $section['groups'], true ) ) {
							ec_nordheide_v2_render_field( $key, $field, $options[ $key ] ?? $field['default'] );
						}
					}
					?>
				</table>
			<?php endforeach; ?>
			<?php submit_button(); ?>
		</form>
	</div>
	<style>
		.ec-v2-jumplist { display: flex; flex-wrap: wrap; gap: .3rem .9rem; padding: .75rem 1rem; background: #fff; border: 1px solid #dcdcde; }
	</style>
	<script>
	(function($){
		$('.ec-v2-media-button').on('click', function(e){
			e.preventDefault();
			const button = $(this), target = $('#' + button.data('target'));
			const frame = wp.media({ title: button.data('title'), multiple: false, library: { type: 'image' } });
			frame.on('select', function(){ const item = frame.state().get('selection').first().toJSON(); target.val(item.id); button.siblings('.description').text(item.filename); });
			frame.open();
		});
		$('.ec-v2-page-picker').on('change', function(){
			if ( this.value ) { $('#' + $(this).data('target')).val(this.value); }
		});
	}(jQuery));
	</script>
	<?php
}

function ec_nordheide_v2_render_field( $key, $field, $value ) {
	switch ( $field['type'] ) {
		case 'textarea':
			ec_nordheide_v2_textarea_field( $key, $field['label'], $value );
			break;
		case 'color':
			ec_nordheide_v2_color_field( $key, $field['label'], $value );
			break;
		case 'media':
			ec_nordheide_v2_media_field( $key, $field['label'], $value );
			break;
		case 'url':
			ec_nordheide_v2_url_field( $key, $field['label'], $value );
			break;
		case 'text':
		default:
			ec_nordheide_v2_text_field( $key, $field['label'], $value );
			break;
	}
}

function ec_nordheide_v2_text_field( $name, $label, $value ) {
	printf( '<tr><th scope="row"><label for="ec-v2-%1$s">%2$s</label></th><td><input class="regular-text" id="ec-v2-%1$s" name="ec_nordheide_v2_options[%1$s]" value="%3$s"></td></tr>', esc_attr( $name ), esc_html( $label ), esc_attr( $value ) );
}

function ec_nordheide_v2_textarea_field( $name, $label, $value ) {
	printf( '<tr><th scope="row"><label for="ec-v2-%1$s">%2$s</label></th><td><textarea class="large-text" rows="3" id="ec-v2-%1$s" name="ec_nordheide_v2_options[%1$s]">%3$s</textarea></td></tr>', esc_attr( $name ), esc_html( $label ), esc_textarea( $value ) );
}

function ec_nordheide_v2_color_field( $name, $label, $value ) {
	printf( '<tr><th scope="row"><label for="ec-v2-%1$s">%2$s</label></th><td><input type="color" id="ec-v2-%1$s" name="ec_nordheide_v2_options[%1$s]" value="%3$s"> <code>%3$s</code></td></tr>', esc_attr( $name ), esc_html( $label ), esc_attr( $value ) );
}

function ec_nordheide_v2_media_field( $name, $label, $value ) {
	$file = $value ? get_attached_file( $value ) : '';
	$description = $file ? basename( $file ) : __( 'Noch kein Bild ausgewählt', 'ec-nordheide-v2' );
	printf( '<tr><th scope="row">%1$s</th><td><input type="hidden" id="ec-v2-%2$s" name="ec_nordheide_v2_options[%2$s]" value="%3$d"><button class="button ec-v2-media-button" data-target="ec-v2-%2$s" data-title="%1$s">%4$s</button> <span class="description">%5$s</span></td></tr>', esc_html( $label ), esc_attr( $name ), absint( $value ), esc_html__( 'Bild auswählen', 'ec-nordheide-v2' ), esc_html( $description ) );
}

/**
 * URL-Feld mit Seiten-Auswahl: die Auswahl einer vorhandenen WordPress-Seite
 * trägt deren aktuelle Adresse automatisch in das Textfeld ein. Das Textfeld
 * bleibt frei editierbar, für externe Links (z. B. eine Spendenplattform).
 */
function ec_nordheide_v2_url_field( $name, $label, $value ) {
	printf(
		'<tr><th scope="row"><label for="ec-v2-%1$s">%2$s</label></th><td><input class="regular-text" type="text" id="ec-v2-%1$s" name="ec_nordheide_v2_options[%1$s]" value="%3$s"> ',
		esc_attr( $name ),
		esc_html( $label ),
		esc_attr( $value )
	);
	echo '<select class="ec-v2-page-picker" data-target="ec-v2-' . esc_attr( $name ) . '">';
	echo '<option value="">' . esc_html__( '— Seite wählen —', 'ec-nordheide-v2' ) . '</option>';
	$pages = get_pages( array( 'sort_column' => 'post_title' ) );
	foreach ( $pages as $page ) {
		printf( '<option value="%s">%s</option>', esc_url( get_permalink( $page ) ), esc_html( $page->post_title ) );
	}
	echo '</select></td></tr>';
}
