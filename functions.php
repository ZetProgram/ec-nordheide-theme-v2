<?php
/**
 * EC Nordheide Theme v2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EC_NORDHEIDE_V2_VERSION', '0.5.0' );

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

		register_nav_menus(
			array(
				'primary' => __( 'Hauptmenü', 'ec-nordheide-v2' ),
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

/**
 * Liefert die Hero-Bild-URL oder einen leeren String.
 *
 * Ohne echtes Foto zeigt der Hero einen Marken-Platzhalter (Farbverlauf +
 * Hexagon-Motiv, siehe .ec-hero--placeholder) statt eines generischen
 * Stockfotos. Sobald unter „EC Nordheide" ein eigenes Foto hochgeladen wird,
 * ersetzt es automatisch den Platzhalter.
 */
function ec_nordheide_v2_hero_image() {
	$options = ec_nordheide_v2_get_options();
	if ( ! empty( $options['hero_image_id'] ) ) {
		$url = wp_get_attachment_image_url( (int) $options['hero_image_id'], 'full' );
		if ( $url ) {
			return esc_url( $url );
		}
	}
	return '';
}

function ec_nordheide_v2_logo_url() {
	$options = ec_nordheide_v2_get_options();
	if ( ! empty( $options['logo_id'] ) ) {
		return esc_url( wp_get_attachment_image_url( (int) $options['logo_id'], 'medium' ) );
	}
	return esc_url( get_theme_file_uri( 'assets/images/brand/ec-logo-wide-left-black.png' ) );
}

/**
 * Ein Eintrag pro pflegbarem Feld: type steuert sowohl das Admin-Formular
 * als auch die Sanitisierung. So bleibt jedes neue Startseiten-Feld an
 * genau einer Stelle definiert, statt an drei Stellen synchron gehalten
 * werden zu müssen.
 *
 * Typen: text, textarea, url, color, media
 */
function ec_nordheide_v2_field_schema() {
	// Interne Standard-Links relativ zu home_url(), damit sie auch bei
	// einer WordPress-Installation in einem Unterverzeichnis stimmen.
	$u = function ( $path ) {
		return home_url( $path );
	};

	$audiences_default = array(
		array( 'title' => 'Für Jugendliche', 'text' => 'Finde deine Gruppe, Menschen in deinem Alter und Angebote in deiner Nähe.', 'url' => $u( '/unsere-orte/' ) ),
		array( 'title' => 'Für Eltern', 'text' => 'Erfahre, wie wir junge Menschen begleiten, stärken und in ihrer Entwicklung fördern.', 'url' => $u( '/fuer-eltern/' ) ),
		array( 'title' => 'Für Mitarbeitende', 'text' => 'Du möchtest dich einbringen? Entdecke Möglichkeiten, Teil der Bewegung zu werden.', 'url' => $u( '/mitarbeit/' ) ),
		array( 'title' => 'Über den EC', 'text' => 'Lerne unsere Geschichte, unsere Werte und die Menschen hinter dem EC Nordheide kennen.', 'url' => $u( '/ueber-uns/' ) ),
	);
	$age_groups_default = array(
		array( 'title' => 'Jungschar', 'range' => '8–12 Jahre', 'text' => 'Abenteuer, Gemeinschaft und erste Schritte im Glauben.', 'url' => $u( '/jungschar/' ) ),
		array( 'title' => 'Teenkreis', 'range' => '12–16 Jahre', 'text' => 'Echte Freundschaften, gute Fragen und gemeinsam unterwegs sein.', 'url' => $u( '/teenkreis/' ) ),
		array( 'title' => 'Jugendkreis', 'range' => '16–18 Jahre', 'text' => 'Glaube, Leben und Verantwortung mit anderen Jugendlichen teilen.', 'url' => $u( '/jugendkreis/' ) ),
		array( 'title' => 'Junge Erwachsene', 'range' => '18+ Jahre', 'text' => 'Gemeinschaft, Tiefgang und Raum für deinen nächsten Schritt.', 'url' => $u( '/junge-erwachsene/' ) ),
	);
	$people_default = array(
		array( 'title' => 'Kreisleitung', 'text' => 'Der EC Nordheide als Ganzes: Ausrichtung, Vernetzung und Ansprechpartner für Gemeinden.', 'url' => $u( '/ueber-uns/' ) ),
		array( 'title' => 'Jungschar-Team', 'text' => 'Zuständig für alle Jungschargruppen und Angebote für 8- bis 12-Jährige.', 'url' => $u( '/ueber-uns/' ) ),
		array( 'title' => 'Teen- & Jugendkreis', 'text' => 'Begleitet Teenkreis und Jugendkreis durch Alltag, Freizeiten und Glaubensfragen.', 'url' => $u( '/ueber-uns/' ) ),
		array( 'title' => 'Mitarbeit & Freiwillige', 'text' => 'Erster Kontakt, wenn du selbst mitarbeiten oder ein Team unterstützen willst.', 'url' => $u( '/mitarbeit/' ) ),
	);

	$schema = array(
		// Branding
		'logo_id'       => array( 'type' => 'media', 'group' => 'branding', 'label' => __( 'Logo', 'ec-nordheide-v2' ), 'default' => 0 ),
		'hero_image_id' => array( 'type' => 'media', 'group' => 'branding', 'label' => __( 'Hero-Hintergrundbild', 'ec-nordheide-v2' ), 'default' => 0 ),
		'hero_kicker'   => array( 'type' => 'text', 'group' => 'branding', 'label' => __( 'Hero-Kicker', 'ec-nordheide-v2' ), 'default' => 'EC Nordheide' ),
		'hero_title'    => array( 'type' => 'text', 'group' => 'branding', 'label' => __( 'Hero-Titel', 'ec-nordheide-v2' ), 'default' => 'Glaube, Gemeinschaft & Leben' ),
		'hero_subtitle' => array( 'type' => 'text', 'group' => 'branding', 'label' => __( 'Hero-Unterzeile', 'ec-nordheide-v2' ), 'default' => 'entschieden für Christus' ),
		'hero_primary_label'   => array( 'type' => 'text', 'group' => 'branding', 'label' => __( 'Hero-Button 1: Beschriftung', 'ec-nordheide-v2' ), 'default' => 'Finde deine Gruppe' ),
		'hero_primary_url'     => array( 'type' => 'url', 'group' => 'branding', 'label' => __( 'Hero-Button 1: Link', 'ec-nordheide-v2' ), 'default' => $u( '/unsere-orte/' ) ),
		'hero_secondary_label' => array( 'type' => 'text', 'group' => 'branding', 'label' => __( 'Hero-Button 2: Beschriftung', 'ec-nordheide-v2' ), 'default' => 'Alle Veranstaltungen' ),
		'hero_secondary_url'   => array( 'type' => 'url', 'group' => 'branding', 'label' => __( 'Hero-Button 2: Link', 'ec-nordheide-v2' ), 'default' => $u( '/veranstaltungen/' ) ),

		// Startseite: Was suchst du?
		'search_kicker' => array( 'type' => 'text', 'group' => 'search', 'label' => __( 'Kicker', 'ec-nordheide-v2' ), 'default' => 'Dein Einstieg' ),
		'search_heading' => array( 'type' => 'text', 'group' => 'search', 'label' => __( 'Überschrift', 'ec-nordheide-v2' ), 'default' => 'Was suchst du?' ),
		'search_copy'   => array( 'type' => 'textarea', 'group' => 'search', 'label' => __( 'Text', 'ec-nordheide-v2' ), 'default' => 'Egal, ob du neu dabei bist, dein Kind begleiten möchtest oder selbst mitarbeiten willst: Hier findest du deinen nächsten Schritt.' ),

		// Startseite: Altersgruppen
		'age_heading' => array( 'type' => 'text', 'group' => 'age', 'label' => __( 'Überschrift', 'ec-nordheide-v2' ), 'default' => 'Deine Gruppe. Dein Ort. Deine Menschen.' ),
		'age_copy'    => array( 'type' => 'textarea', 'group' => 'age', 'label' => __( 'Text', 'ec-nordheide-v2' ), 'default' => 'Bei uns findest du Gemeinschaft, in der du gesehen wirst, Fragen stellen kannst und deinen Glauben mitten im Leben entdeckst.' ),

		// Startseite: Wer wir sind
		'about_image_id' => array( 'type' => 'media', 'group' => 'about', 'label' => __( 'Bild', 'ec-nordheide-v2' ), 'default' => 0 ),
		'about_heading'   => array( 'type' => 'text', 'group' => 'about', 'label' => __( 'Überschrift', 'ec-nordheide-v2' ), 'default' => 'Wer wir sind' ),
		'about_text_1'    => array( 'type' => 'textarea', 'group' => 'about', 'label' => __( 'Absatz 1', 'ec-nordheide-v2' ), 'default' => 'Wir sind der EC Nordheide: junge Menschen, engagierte Mitarbeitende und Gemeinden, die gemeinsam unterwegs sind.' ),
		'about_text_2'    => array( 'type' => 'textarea', 'group' => 'about', 'label' => __( 'Absatz 2', 'ec-nordheide-v2' ), 'default' => 'Wir glauben, dass jeder Mensch wertvoll ist, dass Jesus Christus Leben verändert und dass Gemeinschaft stark macht.' ),
		'about_link_label' => array( 'type' => 'text', 'group' => 'about', 'label' => __( 'Link-Beschriftung', 'ec-nordheide-v2' ), 'default' => 'Mehr über uns' ),
		'about_link_url'    => array( 'type' => 'url', 'group' => 'about', 'label' => __( 'Link-Ziel', 'ec-nordheide-v2' ), 'default' => $u( '/ueber-uns/' ) ),

		// Startseite: Veranstaltungen
		'events_heading' => array( 'type' => 'text', 'group' => 'events', 'label' => __( 'Überschrift', 'ec-nordheide-v2' ), 'default' => 'Kommende Veranstaltungen' ),
		'events_copy'    => array( 'type' => 'textarea', 'group' => 'events', 'label' => __( 'Text', 'ec-nordheide-v2' ), 'default' => 'Freizeiten, Aktionen und Treffen, bei denen du dabei sein kannst. Die vollständige Übersicht folgt hier, sobald die Anmeldung steht.' ),
		'events_cta_label' => array( 'type' => 'text', 'group' => 'events', 'label' => __( 'Button-Beschriftung', 'ec-nordheide-v2' ), 'default' => 'Alle Veranstaltungen' ),
		'events_cta_url'    => array( 'type' => 'url', 'group' => 'events', 'label' => __( 'Button-Ziel', 'ec-nordheide-v2' ), 'default' => $u( '/veranstaltungen/' ) ),

		// Startseite: Neues aus der Nordheide
		'news_heading' => array( 'type' => 'text', 'group' => 'news', 'label' => __( 'Überschrift', 'ec-nordheide-v2' ), 'default' => 'Neues aus der Nordheide' ),
		'news_copy'    => array( 'type' => 'textarea', 'group' => 'news', 'label' => __( 'Text', 'ec-nordheide-v2' ), 'default' => 'Geschichten, Einblicke und aktuelle Neuigkeiten aus unserem Kreisverband.' ),

		// Startseite: Team
		'people_heading' => array( 'type' => 'text', 'group' => 'people', 'label' => __( 'Überschrift', 'ec-nordheide-v2' ), 'default' => 'Wer sich um was kümmert' ),
		'people_copy'    => array( 'type' => 'textarea', 'group' => 'people', 'label' => __( 'Text', 'ec-nordheide-v2' ), 'default' => 'Der EC Nordheide lebt von Menschen, die Verantwortung übernehmen. Das ist eure erste Anlaufstelle für Fragen.' ),

		// Startseite: Mitmachen
		'support_heading' => array( 'type' => 'text', 'group' => 'support', 'label' => __( 'Überschrift', 'ec-nordheide-v2' ), 'default' => 'Du kannst einen Unterschied machen.' ),
		'support_copy'    => array( 'type' => 'textarea', 'group' => 'support', 'label' => __( 'Text', 'ec-nordheide-v2' ), 'default' => 'Ob durch deine Zeit, dein Gebet oder deine Unterstützung: Danke, dass du Teil unserer Bewegung bist.' ),
		'support_cta_label' => array( 'type' => 'text', 'group' => 'support', 'label' => __( 'Button-Beschriftung', 'ec-nordheide-v2' ), 'default' => 'Unterstütze uns' ),
		'support_cta_url'    => array( 'type' => 'url', 'group' => 'support', 'label' => __( 'Button-Ziel', 'ec-nordheide-v2' ), 'default' => $u( '/spenden/' ) ),

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

	// Vierer-Karten-Gruppen: Titel/Text/Link (Altersgruppen zusätzlich mit Alterspanne).
	$card_groups = array(
		'audience' => array( 'items' => $audiences_default, 'has_range' => false, 'label' => __( 'Karte', 'ec-nordheide-v2' ) ),
		'age'      => array( 'items' => $age_groups_default, 'has_range' => true, 'label' => __( 'Karte', 'ec-nordheide-v2' ) ),
		'people'   => array( 'items' => $people_default, 'has_range' => false, 'label' => __( 'Karte', 'ec-nordheide-v2' ) ),
	);
	foreach ( $card_groups as $group_key => $group ) {
		foreach ( $group['items'] as $index => $item ) {
			$n = $index + 1;
			$schema[ "{$group_key}{$n}_title" ] = array( 'type' => 'text', 'group' => $group_key, 'label' => sprintf( '%s %d: Titel', $group['label'], $n ), 'default' => $item['title'] );
			if ( $group['has_range'] ) {
				$schema[ "{$group_key}{$n}_range" ] = array( 'type' => 'text', 'group' => $group_key, 'label' => sprintf( '%s %d: Altersspanne', $group['label'], $n ), 'default' => $item['range'] );
			}
			$schema[ "{$group_key}{$n}_text" ] = array( 'type' => 'textarea', 'group' => $group_key, 'label' => sprintf( '%s %d: Text', $group['label'], $n ), 'default' => $item['text'] );
			$schema[ "{$group_key}{$n}_url" ]  = array( 'type' => 'url', 'group' => $group_key, 'label' => sprintf( '%s %d: Link', $group['label'], $n ), 'default' => $item['url'] );
		}
	}

	return $schema;
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
		array( 'title' => __( 'Branding & Hero', 'ec-nordheide-v2' ), 'groups' => array( 'branding' ) ),
		array( 'title' => __( 'Startseite: Was suchst du?', 'ec-nordheide-v2' ), 'groups' => array( 'search', 'audience' ) ),
		array( 'title' => __( 'Startseite: Altersgruppen', 'ec-nordheide-v2' ), 'groups' => array( 'age' ) ),
		array( 'title' => __( 'Startseite: Wer wir sind', 'ec-nordheide-v2' ), 'groups' => array( 'about' ) ),
		array( 'title' => __( 'Startseite: Veranstaltungen', 'ec-nordheide-v2' ), 'groups' => array( 'events' ) ),
		array( 'title' => __( 'Startseite: Neues aus der Nordheide', 'ec-nordheide-v2' ), 'groups' => array( 'news' ), 'description' => __( 'Die Beiträge selbst kommen automatisch aus euren neuesten WordPress-Artikeln.', 'ec-nordheide-v2' ) ),
		array( 'title' => __( 'Startseite: Team', 'ec-nordheide-v2' ), 'groups' => array( 'people' ), 'description' => __( 'Rollenbasiert, solange noch kein Mitarbeiter-Plugin angebunden ist. Titel kann auch ein echter Name sein.', 'ec-nordheide-v2' ) ),
		array( 'title' => __( 'Startseite: Mitmachen', 'ec-nordheide-v2' ), 'groups' => array( 'support' ) ),
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
