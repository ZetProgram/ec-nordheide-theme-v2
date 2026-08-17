<?php
/**
 * EC Nordheide Theme v2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EC_NORDHEIDE_V2_VERSION', '0.1.0' );

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
		$ec_nordheide_updater->setBranch( 'production' );
		$ec_nordheide_updater->getVcsApi()->enableReleaseAssets();
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
	$image = get_theme_mod( 'ec_hero_image', '' );
	return $image ? esc_url( $image ) : '';
}
