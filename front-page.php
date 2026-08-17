<?php
/**
 * Startseite – erste Umsetzung des gelieferten Designvorschlags.
 */
get_header();
$ec_options = ec_nordheide_v2_get_options();
$hero_image = ec_nordheide_v2_hero_image();
?>

<main>
	<section class="ec-hero"<?php if ( $hero_image ) : ?> style="--ec-hero-image: url('<?php echo esc_url( $hero_image ); ?>');"<?php endif; ?>><div class="ec-container ec-hero__content"><p class="ec-kicker"><?php echo esc_html( $ec_options['hero_kicker'] ); ?></p><h1 class="ec-hero__title"><?php echo esc_html( $ec_options['hero_title'] ); ?></h1><p class="ec-hero__subtitle"><?php echo esc_html( $ec_options['hero_subtitle'] ); ?></p><a class="ec-button" href="<?php echo esc_url( $ec_options['hero_cta_url'] ); ?>"><?php echo esc_html( $ec_options['hero_cta_label'] ); ?></a></div></section>
	<section class="ec-section ec-section--paper ec-intro"><div class="ec-container"><h2 class="ec-heading">Das sind wir</h2><p class="ec-copy ec-intro__copy">Wir wollen junge Menschen in ihrer Lebenswelt erreichen und unterstützen, als lebensfrohe Christen und befähigte Mitarbeiter in der Nachfolge zu leben.</p><div class="ec-hex-grid"><article class="ec-hex-card"><h3>Unsere Ziele</h3><div class="ec-hexagon" aria-hidden="true"></div></article><article class="ec-hex-card"><h3>Unsere Werte</h3><div class="ec-hexagon ec-hexagon--gray" aria-hidden="true"></div></article></div></div></section>
	<section class="ec-section ec-section--dark"><div class="ec-container"><div class="ec-link-band"><a class="ec-link-card" href="#">Höre unsere neue Playlist</a><a class="ec-link-card" href="#">Wichtige Links</a><a class="ec-link-card" href="#">Termine &amp; Angebote</a></div></div></section>
	<section class="ec-section ec-section--paper ec-news"><div class="ec-container"><h2 class="ec-heading">Neues aus der Nordheide</h2><p class="ec-copy">Aktuelles, Veranstaltungen und Geschichten aus unserem Kreisverband.</p><div class="ec-news__list"><?php $news = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 3 ) ); if ( $news->have_posts() ) : while ( $news->have_posts() ) : $news->the_post(); ?><article class="ec-news-card"><?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large' ); } ?><div class="ec-news-card__body"><h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3><p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p></div></article><?php endwhile; wp_reset_postdata(); else : ?><article class="ec-news-card"><div class="ec-news-card__body"><h3>Noch mehr aus der Nordheide</h3><p>Hier erscheinen bald aktuelle Nachrichten und Veranstaltungen.</p></div></article><?php endif; ?></div></div></section>
</main>

<?php get_footer(); ?>
