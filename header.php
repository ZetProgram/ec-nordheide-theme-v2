<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main-content"><?php esc_html_e( 'Zum Inhalt springen', 'ec-nordheide-v2' ); ?></a>
<div class="site-shell">
	<header class="site-header">
		<div class="site-header__inner">
			<a class="site-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<img class="site-brand__image" src="<?php echo esc_url( ec_nordheide_v2_logo_url() ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="240" height="58">
			</a>
			<button class="menu-toggle" type="button" aria-expanded="false" aria-controls="site-navigation" aria-label="<?php esc_attr_e( 'Menü öffnen', 'ec-nordheide-v2' ); ?>">
				<svg class="icon-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
				<svg class="icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
			</button>
			<nav id="site-navigation" class="site-nav" aria-label="<?php esc_attr_e( 'Hauptmenü', 'ec-nordheide-v2' ); ?>">
				<?php if ( has_nav_menu( 'primary' ) ) : ?>
					<?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false ) ); ?>
				<?php else : ?>
					<?php ec_nordheide_v2_fallback_menu(); ?>
				<?php endif; ?>
			</nav>
		</div>
	</header>
