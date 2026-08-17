<?php
/**
 * EC Nordheide Theme v2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EC_NORDHEIDE_V2_VERSION', '0.3.2' );

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

add_action(
	'wp_head',
	function () {
		$options = ec_nordheide_v2_get_options();
		printf( '<style>:root{--ec-orange:%1$s;--ec-paper:%2$s;--ec-ink:%3$s}</style>', esc_attr( $options['color_orange'] ), esc_attr( $options['color_paper'] ), esc_attr( $options['color_ink'] ) );
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

function ec_nordheide_v2_hero_image() {
	$options = ec_nordheide_v2_get_options();
	if ( ! empty( $options['hero_image_id'] ) ) {
		return esc_url( wp_get_attachment_image_url( (int) $options['hero_image_id'], 'full' ) );
	}
	return esc_url( get_theme_file_uri( 'assets/images/brand/hero-bible.jpg' ) );
}

function ec_nordheide_v2_logo_url() {
	$options = ec_nordheide_v2_get_options();
	if ( ! empty( $options['logo_id'] ) ) {
		return esc_url( wp_get_attachment_image_url( (int) $options['logo_id'], 'medium' ) );
	}
	return esc_url( get_theme_file_uri( 'assets/images/brand/ec-logo-wide-left-black.png' ) );
}

function ec_nordheide_v2_get_options() {
	$defaults = array(
		'logo_id'          => 0,
		'hero_image_id'    => 0,
		'hero_kicker'      => 'EC Nordheide',
		'hero_title'       => 'Glaube, Gemeinschaft & Leben',
		'hero_subtitle'    => 'entschieden für Christus',
		'hero_cta_label'   => 'Mitglied werden!',
		'hero_cta_url'     => '/mitglied-werden/',
		'footer_claim'     => 'Glaube, Gemeinschaft & Leben.',
		'contact_text'     => 'Kreisverband Nordheide\n„Entschieden für Christus“ e.V.',
		'instagram_url'    => '',
		'spotify_url'      => '',
		'linktree_url'     => '',
		'color_orange'    => '#92c355',
		'color_paper'     => '#f4f9ee',
		'color_ink'       => '#213214',
	);
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
	$out['logo_id']        = absint( $input['logo_id'] ?? 0 );
	$out['hero_image_id']  = absint( $input['hero_image_id'] ?? 0 );
	$out['hero_kicker']    = sanitize_text_field( $input['hero_kicker'] ?? '' );
	$out['hero_title']     = sanitize_text_field( $input['hero_title'] ?? '' );
	$out['hero_subtitle']  = sanitize_text_field( $input['hero_subtitle'] ?? '' );
	$out['hero_cta_label'] = sanitize_text_field( $input['hero_cta_label'] ?? '' );
	$out['hero_cta_url']   = esc_url_raw( $input['hero_cta_url'] ?? '' );
	$out['footer_claim']   = sanitize_text_field( $input['footer_claim'] ?? '' );
	$out['contact_text']   = sanitize_textarea_field( $input['contact_text'] ?? '' );
	$out['instagram_url']  = esc_url_raw( $input['instagram_url'] ?? '' );
	$out['spotify_url']    = esc_url_raw( $input['spotify_url'] ?? '' );
	$out['linktree_url']   = esc_url_raw( $input['linktree_url'] ?? '' );
	foreach ( array( 'color_orange', 'color_paper', 'color_ink' ) as $color ) {
		$out[ $color ] = sanitize_hex_color( $input[ $color ] ?? '' ) ?: $old[ $color ];
	}
	return $out;
}

function ec_nordheide_v2_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$options = ec_nordheide_v2_get_options();
	wp_enqueue_media();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'EC Nordheide – Theme-Einstellungen', 'ec-nordheide-v2' ); ?></h1>
		<p><?php esc_html_e( 'Hier werden zentrale Elemente des neuen Auftritts gepflegt. Beiträge, Veranstaltungen und Mitarbeiter bleiben eigene WordPress-Inhalte.', 'ec-nordheide-v2' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'ec_nordheide_v2_options_group' ); ?>
			<h2><?php esc_html_e( 'Branding & Hero', 'ec-nordheide-v2' ); ?></h2>
			<table class="form-table" role="presentation">
				<?php ec_nordheide_v2_media_field( 'logo_id', __( 'Logo', 'ec-nordheide-v2' ), $options['logo_id'] ); ?>
				<?php ec_nordheide_v2_media_field( 'hero_image_id', __( 'Hero-Hintergrundbild', 'ec-nordheide-v2' ), $options['hero_image_id'] ); ?>
				<?php ec_nordheide_v2_text_field( 'hero_kicker', __( 'Hero-Kicker', 'ec-nordheide-v2' ), $options['hero_kicker'] ); ?>
				<?php ec_nordheide_v2_text_field( 'hero_title', __( 'Hero-Titel', 'ec-nordheide-v2' ), $options['hero_title'] ); ?>
				<?php ec_nordheide_v2_text_field( 'hero_subtitle', __( 'Hero-Unterzeile', 'ec-nordheide-v2' ), $options['hero_subtitle'] ); ?>
				<?php ec_nordheide_v2_text_field( 'hero_cta_label', __( 'CTA-Beschriftung', 'ec-nordheide-v2' ), $options['hero_cta_label'] ); ?>
				<?php ec_nordheide_v2_text_field( 'hero_cta_url', __( 'CTA-Link', 'ec-nordheide-v2' ), $options['hero_cta_url'] ); ?>
			</table>
			<h2><?php esc_html_e( 'Footer & Links', 'ec-nordheide-v2' ); ?></h2>
			<table class="form-table" role="presentation">
				<?php ec_nordheide_v2_text_field( 'footer_claim', __( 'Footer-Unterzeile', 'ec-nordheide-v2' ), $options['footer_claim'] ); ?>
				<?php ec_nordheide_v2_textarea_field( 'contact_text', __( 'Kontakttext', 'ec-nordheide-v2' ), $options['contact_text'] ); ?>
				<?php ec_nordheide_v2_text_field( 'instagram_url', __( 'Instagram-URL', 'ec-nordheide-v2' ), $options['instagram_url'] ); ?>
				<?php ec_nordheide_v2_text_field( 'spotify_url', __( 'Spotify-URL', 'ec-nordheide-v2' ), $options['spotify_url'] ); ?>
				<?php ec_nordheide_v2_text_field( 'linktree_url', __( 'Linktree-URL', 'ec-nordheide-v2' ), $options['linktree_url'] ); ?>
			</table>
			<h2><?php esc_html_e( 'Farben', 'ec-nordheide-v2' ); ?></h2>
			<table class="form-table" role="presentation">
				<?php ec_nordheide_v2_color_field( 'color_orange', __( 'Akzent-/CTA-Farbe', 'ec-nordheide-v2' ), $options['color_orange'] ); ?>
				<?php ec_nordheide_v2_color_field( 'color_paper', __( 'Off-White', 'ec-nordheide-v2' ), $options['color_paper'] ); ?>
				<?php ec_nordheide_v2_color_field( 'color_ink', __( 'Dunkle Farbe', 'ec-nordheide-v2' ), $options['color_ink'] ); ?>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<script>
	(function($){
		$('.ec-v2-media-button').on('click', function(e){
			e.preventDefault();
			const button = $(this), target = $('#' + button.data('target'));
			const frame = wp.media({ title: button.data('title'), multiple: false, library: { type: 'image' } });
			frame.on('select', function(){ const item = frame.state().get('selection').first().toJSON(); target.val(item.id); button.siblings('.description').text(item.filename); });
			frame.open();
		});
	}(jQuery));
	</script>
	<?php
}

function ec_nordheide_v2_text_field( $name, $label, $value ) {
	printf( '<tr><th scope="row"><label for="ec-v2-%1$s">%2$s</label></th><td><input class="regular-text" id="ec-v2-%1$s" name="ec_nordheide_v2_options[%1$s]" value="%3$s"></td></tr>', esc_attr( $name ), esc_html( $label ), esc_attr( $value ) );
}

function ec_nordheide_v2_textarea_field( $name, $label, $value ) {
	printf( '<tr><th scope="row"><label for="ec-v2-%1$s">%2$s</label></th><td><textarea class="large-text" rows="4" id="ec-v2-%1$s" name="ec_nordheide_v2_options[%1$s]">%3$s</textarea></td></tr>', esc_attr( $name ), esc_html( $label ), esc_textarea( $value ) );
}

function ec_nordheide_v2_color_field( $name, $label, $value ) {
	printf( '<tr><th scope="row"><label for="ec-v2-%1$s">%2$s</label></th><td><input type="color" id="ec-v2-%1$s" name="ec_nordheide_v2_options[%1$s]" value="%3$s"> <code>%3$s</code></td></tr>', esc_attr( $name ), esc_html( $label ), esc_attr( $value ) );
}

function ec_nordheide_v2_media_field( $name, $label, $value ) {
	$file = $value ? get_attached_file( $value ) : '';
	$description = $file ? basename( $file ) : __( 'Noch kein Bild ausgewählt', 'ec-nordheide-v2' );
	printf( '<tr><th scope="row">%1$s</th><td><input type="hidden" id="ec-v2-%2$s" name="ec_nordheide_v2_options[%2$s]" value="%3$d"><button class="button ec-v2-media-button" data-target="ec-v2-%2$s" data-title="%1$s">%4$s</button> <span class="description">%5$s</span></td></tr>', esc_html( $label ), esc_attr( $name ), absint( $value ), esc_html__( 'Bild auswählen', 'ec-nordheide-v2' ), esc_html( $description ) );
}
