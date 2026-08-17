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
<div class="site-shell">
	<header class="site-header">
		<div class="site-header__inner">
			<a class="site-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<span class="site-brand__mark" aria-hidden="true">€</span>
				<span class="site-brand__text"><span>Entschieden für Christus</span><span>EC Nordheide</span></span>
			</a>
			<button class="menu-toggle" type="button" aria-expanded="false" aria-controls="site-navigation"><span class="screen-reader-text"><?php esc_html_e( 'Menü öffnen', 'ec-nordheide-v2' ); ?></span>☰</button>
			<nav id="site-navigation" class="site-nav" aria-label="<?php esc_attr_e( 'Hauptmenü', 'ec-nordheide-v2' ); ?>">
				<?php if ( has_nav_menu( 'primary' ) ) : ?>
					<?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false ) ); ?>
				<?php else : ?>
					<?php ec_nordheide_v2_fallback_menu(); ?>
				<?php endif; ?>
			</nav>
		</div>
	</header>
