<?php
/**
 * Startseite – EC Nordheide.
 * Die Sektionen bilden die Zielgruppen- und Informationsstruktur des neuen Auftritts.
 */
get_header();
$ec_options = ec_nordheide_v2_get_options();
$hero_image = ec_nordheide_v2_hero_image();

$audiences = array(
	array( 'title' => 'Für Jugendliche', 'text' => 'Finde deine Gruppe, Menschen in deinem Alter und Angebote in deiner Nähe.', 'url' => home_url( '/unsere-orte/' ), 'class' => 'ec-card--green' ),
	array( 'title' => 'Für Eltern', 'text' => 'Erfahre, wie wir junge Menschen begleiten, stärken und in ihrer Entwicklung fördern.', 'url' => home_url( '/fuer-eltern/' ), 'class' => 'ec-card--paper' ),
	array( 'title' => 'Für Mitarbeitende', 'text' => 'Du möchtest dich einbringen? Entdecke Möglichkeiten, Teil der Bewegung zu werden.', 'url' => home_url( '/mitarbeit/' ), 'class' => 'ec-card--gray' ),
	array( 'title' => 'Über den EC', 'text' => 'Lerne unsere Geschichte, unsere Werte und die Menschen hinter dem EC Nordheide kennen.', 'url' => home_url( '/ueber-uns/' ), 'class' => 'ec-card--dark' ),
);

$age_groups = array(
	array( 'title' => 'Jungschar', 'age' => '8–12 Jahre', 'text' => 'Abenteuer, Gemeinschaft und erste Schritte im Glauben.', 'url' => home_url( '/jungschar/' ) ),
	array( 'title' => 'Teenkreis', 'age' => '12–16 Jahre', 'text' => 'Echte Freundschaften, gute Fragen und gemeinsam unterwegs sein.', 'url' => home_url( '/teenkreis/' ) ),
	array( 'title' => 'Jugendkreis', 'age' => '16–18 Jahre', 'text' => 'Glaube, Leben und Verantwortung mit anderen Jugendlichen teilen.', 'url' => home_url( '/jugendkreis/' ) ),
	array( 'title' => 'Junge Erwachsene', 'age' => '18+ Jahre', 'text' => 'Gemeinschaft, Tiefgang und Raum für deinen nächsten Schritt.', 'url' => home_url( '/junge-erwachsene/' ) ),
);
?>

<main>
	<section class="ec-hero" style="--ec-hero-image: url('<?php echo esc_url( $hero_image ); ?>');"><div class="ec-container ec-hero__content"><p class="ec-kicker"><?php echo esc_html( $ec_options['hero_kicker'] ); ?></p><h1 class="ec-hero__title"><?php echo esc_html( $ec_options['hero_title'] ); ?></h1><p class="ec-hero__subtitle"><?php echo esc_html( $ec_options['hero_subtitle'] ); ?></p><div class="ec-hero__actions"><a class="ec-button" href="<?php echo esc_url( home_url( '/unsere-orte/' ) ); ?>">Finde deine Gruppe</a><a class="ec-button ec-button--ghost" href="<?php echo esc_url( home_url( '/veranstaltungen/' ) ); ?>">Kommende Veranstaltungen</a></div></div></section>

	<section class="ec-section ec-section--paper"><div class="ec-container"><div class="ec-section-heading"><p class="ec-kicker">Dein Einstieg</p><h2 class="ec-heading">Was suchst du?</h2><p class="ec-copy">Egal, ob du neu dabei bist, dein Kind begleiten möchtest oder selbst mitarbeiten willst: Hier findest du deinen nächsten Schritt.</p></div><div class="ec-audience-grid"><?php foreach ( $audiences as $index => $audience ) : ?><a class="ec-audience-card <?php echo esc_attr( $audience['class'] ); ?>" href="<?php echo esc_url( $audience['url'] ); ?>"><span class="ec-card-number" aria-hidden="true">0<?php echo esc_html( $index + 1 ); ?></span><h3><?php echo esc_html( $audience['title'] ); ?></h3><p><?php echo esc_html( $audience['text'] ); ?></p><span class="ec-card-link">Mehr erfahren →</span></a><?php endforeach; ?></div></div></section>

	<section class="ec-section ec-section--green"><div class="ec-container"><div class="ec-section-heading ec-section-heading--light"><p class="ec-kicker">Gemeinsam wachsen</p><h2 class="ec-heading ec-heading--light">Deine Gruppe. Dein Ort. Deine Menschen.</h2><p class="ec-copy ec-copy--light">Bei uns findest du Gemeinschaft, in der du gesehen wirst, Fragen stellen kannst und deinen Glauben mitten im Leben entdecken darfst.</p></div><div class="ec-age-grid"><?php foreach ( $age_groups as $group ) : ?><a class="ec-age-card" href="<?php echo esc_url( $group['url'] ); ?>"><span class="ec-age-card__age"><?php echo esc_html( $group['age'] ); ?></span><h3><?php echo esc_html( $group['title'] ); ?></h3><p><?php echo esc_html( $group['text'] ); ?></p><span class="ec-card-link">Entdecken →</span></a><?php endforeach; ?></div></div></section>

	<section class="ec-section ec-section--paper ec-about-preview"><div class="ec-container ec-split"><div><p class="ec-kicker">Wer wir sind</p><h2 class="ec-heading">Glaube, der ins Leben passt.</h2></div><div class="ec-about-preview__copy"><p>Wir sind der EC Nordheide: junge Menschen, engagierte Mitarbeitende und Gemeinden, die gemeinsam unterwegs sind.</p><p>Wir glauben, dass jeder Mensch wertvoll ist, dass Jesus Christus Leben verändert und dass Gemeinschaft stark macht.</p><a class="ec-text-link" href="<?php echo esc_url( home_url( '/ueber-uns/' ) ); ?>">Mehr über uns →</a></div></div></section>

	<section class="ec-section ec-section--dark ec-events-preview"><div class="ec-container"><div class="ec-section-heading ec-section-heading--light"><p class="ec-kicker">Dabei sein</p><h2 class="ec-heading ec-heading--light">Kommende Veranstaltungen</h2></div><div class="ec-event-placeholder"><p>Entdecke, was als Nächstes in der Nordheide passiert.</p><a class="ec-button" href="<?php echo esc_url( home_url( '/veranstaltungen/' ) ); ?>">Alle Veranstaltungen</a></div></div></section>

	<section class="ec-section ec-section--paper ec-news"><div class="ec-container"><div class="ec-section-heading"><p class="ec-kicker">Aus unserem Verband</p><h2 class="ec-heading">Neues aus der Nordheide</h2><p class="ec-copy">Geschichten, Einblicke und aktuelle Neuigkeiten aus unserem Kreisverband.</p></div><div class="ec-news__list"><?php $news = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 3 ) ); if ( $news->have_posts() ) : while ( $news->have_posts() ) : $news->the_post(); ?><article class="ec-news-card"><?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large' ); } ?><div class="ec-news-card__body"><p class="ec-news-card__date"><?php echo esc_html( get_the_date() ); ?></p><h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3><p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p><a class="ec-text-link" href="<?php the_permalink(); ?>">Weiterlesen →</a></div></article><?php endwhile; wp_reset_postdata(); else : ?><p><?php esc_html_e( 'Hier erscheinen bald aktuelle Nachrichten aus der Nordheide.', 'ec-nordheide-v2' ); ?></p><?php endif; ?></div></div></section>

	<section class="ec-section ec-section--green ec-support-preview"><div class="ec-container ec-split ec-split--center"><div><p class="ec-kicker">Gemeinsam möglich machen</p><h2 class="ec-heading ec-heading--light">Du kannst einen Unterschied machen.</h2></div><div><p class="ec-copy ec-copy--light">Ob durch deine Zeit, dein Gebet oder deine Unterstützung: Danke, dass du Teil unserer Bewegung bist.</p><a class="ec-button ec-button--light" href="<?php echo esc_url( home_url( '/spenden/' ) ); ?>">Unterstütze uns</a></div></div></section>
</main>

<?php get_footer(); ?>
