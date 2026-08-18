<?php
/**
 * Startseite - EC Nordheide.
 * Alle Texte, Karten und Links kommen aus den Theme-Einstellungen
 * (Menü „EC Nordheide" im Backend), damit sie ohne PHP-Kenntnisse
 * gepflegt werden können.
 */
get_header();
$o = ec_nordheide_v2_get_options();

$hero_image = ec_nordheide_v2_hero_image();
$hero_class = $hero_image ? 'ec-hero--photo' : 'ec-hero--placeholder';
$hero_style = $hero_image ? sprintf( ' style="--ec-hero-image: url(%s);"', esc_url( $hero_image ) ) : '';

$about_image = ! empty( $o['about_image_id'] ) ? wp_get_attachment_image_url( (int) $o['about_image_id'], 'large' ) : '';

// Feste Kartenfarben je Position sorgen für Abwechslung im Grid,
// unabhängig davon, welchen Titel/Text die Redaktion einträgt.
$audience_classes = array( 'ec-card--leaf', 'ec-card--paper', 'ec-card--soft', 'ec-card--dark' );
?>

<main id="main-content">
	<section class="ec-hero <?php echo esc_attr( $hero_class ); ?>"<?php echo $hero_style; /* phpcs:ignore, bereits escaped */ ?>>
		<div class="ec-container ec-hero__content">
			<p class="ec-kicker"><?php echo esc_html( $o['hero_kicker'] ); ?></p>
			<h1 class="ec-hero__title"><?php echo esc_html( $o['hero_title'] ); ?></h1>
			<p class="ec-hero__subtitle"><?php echo esc_html( $o['hero_subtitle'] ); ?></p>
			<div class="ec-button-row">
				<a class="ec-button" href="<?php echo esc_url( $o['hero_primary_url'] ); ?>"><?php echo esc_html( $o['hero_primary_label'] ); ?></a>
				<a class="ec-button ec-button--ghost" href="<?php echo esc_url( $o['hero_secondary_url'] ); ?>"><?php echo esc_html( $o['hero_secondary_label'] ); ?></a>
			</div>
		</div>
	</section>

	<section class="ec-section ec-section--paper">
		<div class="ec-container">
			<div class="ec-section-heading" data-animate>
				<p class="ec-kicker"><?php echo esc_html( $o['search_kicker'] ); ?></p>
				<h2 class="ec-heading"><?php echo esc_html( $o['search_heading'] ); ?></h2>
				<p class="ec-copy"><?php echo esc_html( $o['search_copy'] ); ?></p>
			</div>
			<div class="ec-audience-grid" data-animate-group>
				<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
					<?php if ( '' === trim( (string) $o[ "audience{$i}_title" ] ) ) { continue; } ?>
					<a class="ec-audience-card <?php echo esc_attr( $audience_classes[ $i - 1 ] ); ?>" href="<?php echo esc_url( $o[ "audience{$i}_url" ] ); ?>" data-animate>
						<span class="ec-card-number" aria-hidden="true">0<?php echo esc_html( $i ); ?></span>
						<h3><?php echo esc_html( $o[ "audience{$i}_title" ] ); ?></h3>
						<p><?php echo esc_html( $o[ "audience{$i}_text" ] ); ?></p>
						<span class="ec-card-link">Mehr erfahren →</span>
					</a>
				<?php endfor; ?>
			</div>
		</div>
	</section>

	<section class="ec-section ec-section--green">
		<div class="ec-container">
			<div class="ec-section-heading" data-animate>
				<h2 class="ec-heading ec-heading--light"><?php echo esc_html( $o['age_heading'] ); ?></h2>
				<p class="ec-copy ec-copy--light"><?php echo esc_html( $o['age_copy'] ); ?></p>
			</div>
			<div class="ec-age-strip" data-animate-group>
				<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
					<?php if ( '' === trim( (string) $o[ "age{$i}_title" ] ) ) { continue; } ?>
					<a class="ec-age-card" href="<?php echo esc_url( $o[ "age{$i}_url" ] ); ?>" data-animate>
						<span class="ec-age-card__age"><?php echo esc_html( $o[ "age{$i}_range" ] ); ?></span>
						<h3><?php echo esc_html( $o[ "age{$i}_title" ] ); ?></h3>
						<p><?php echo esc_html( $o[ "age{$i}_text" ] ); ?></p>
						<span class="ec-card-link">Entdecken →</span>
					</a>
				<?php endfor; ?>
			</div>
		</div>
	</section>

	<section class="ec-section ec-section--paper ec-about-preview">
		<div class="ec-container ec-split">
			<?php if ( $about_image ) : ?>
				<div class="ec-about-preview__media" data-animate>
					<img src="<?php echo esc_url( $about_image ); ?>" alt="" width="640" height="800">
				</div>
			<?php else : ?>
				<div class="ec-about-preview__media" data-animate aria-hidden="true"></div>
			<?php endif; ?>
			<div class="ec-about-preview__copy" data-animate>
				<h2 class="screen-reader-text"><?php echo esc_html( $o['about_heading'] ); ?></h2>
				<p><?php echo esc_html( $o['about_text_1'] ); ?></p>
				<p><?php echo esc_html( $o['about_text_2'] ); ?></p>
				<a class="ec-text-link" href="<?php echo esc_url( $o['about_link_url'] ); ?>"><?php echo esc_html( $o['about_link_label'] ); ?> →</a>
			</div>
		</div>
	</section>

	<section class="ec-section ec-section--dark ec-events-preview">
		<div class="ec-container ec-split ec-split--center">
			<div data-animate>
				<h2 class="ec-heading ec-heading--light"><?php echo esc_html( $o['events_heading'] ); ?></h2>
				<p class="ec-copy ec-copy--light"><?php echo esc_html( $o['events_copy'] ); ?></p>
			</div>
			<div class="ec-event-placeholder" data-animate>
				<a class="ec-button" href="<?php echo esc_url( $o['events_cta_url'] ); ?>"><?php echo esc_html( $o['events_cta_label'] ); ?></a>
			</div>
		</div>
	</section>

	<section class="ec-section ec-section--paper ec-news">
		<div class="ec-container">
			<div class="ec-section-heading ec-section-heading--center" data-animate>
				<h2 class="ec-heading"><?php echo esc_html( $o['news_heading'] ); ?></h2>
				<p class="ec-copy"><?php echo esc_html( $o['news_copy'] ); ?></p>
			</div>
			<div class="ec-news__list" data-animate-group>
				<?php
				$news = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 3 ) );
				if ( $news->have_posts() ) :
					while ( $news->have_posts() ) :
						$news->the_post();
						?>
						<article class="ec-news-card" data-animate>
							<div class="ec-news-card__media">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 'large' ); ?>
								<?php endif; ?>
							</div>
							<div class="ec-news-card__body">
								<p class="ec-news-card__date"><?php echo esc_html( get_the_date() ); ?></p>
								<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
								<a class="ec-text-link" href="<?php the_permalink(); ?>">Weiterlesen →</a>
							</div>
						</article>
						<?php
					endwhile;
					wp_reset_postdata();
				else :
					?>
					<p><?php esc_html_e( 'Hier erscheinen bald aktuelle Nachrichten aus der Nordheide.', 'ec-nordheide-v2' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section class="ec-section ec-section--paper ec-people">
		<div class="ec-container">
			<div class="ec-section-heading" data-animate>
				<h2 class="ec-heading"><?php echo esc_html( $o['people_heading'] ); ?></h2>
				<p class="ec-copy"><?php echo esc_html( $o['people_copy'] ); ?></p>
			</div>
			<div class="ec-people-grid" data-animate-group>
				<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
					<?php
					$title = trim( (string) $o[ "people{$i}_title" ] );
					if ( '' === $title ) { continue; }
					$initial = function_exists( 'mb_strtoupper' ) ? mb_strtoupper( mb_substr( $title, 0, 1 ) ) : strtoupper( substr( $title, 0, 1 ) );
					?>
					<div class="ec-person-card" data-animate>
						<div class="ec-person-card__photo" aria-hidden="true"><span class="ec-person-card__initial"><?php echo esc_html( $initial ); ?></span></div>
						<h3><?php echo esc_html( $title ); ?></h3>
						<p><?php echo esc_html( $o[ "people{$i}_text" ] ); ?></p>
						<a class="ec-text-link" href="<?php echo esc_url( $o[ "people{$i}_url" ] ); ?>">Kontakt aufnehmen →</a>
					</div>
				<?php endfor; ?>
			</div>
		</div>
	</section>

	<section class="ec-section ec-section--accent ec-support-preview">
		<div class="ec-container ec-section-heading--center" data-animate>
			<h2 class="ec-heading ec-heading--light"><?php echo esc_html( $o['support_heading'] ); ?></h2>
			<p class="ec-copy ec-copy--light"><?php echo esc_html( $o['support_copy'] ); ?></p>
			<div class="ec-button-row ec-button-row--center">
				<a class="ec-button ec-button--light" href="<?php echo esc_url( $o['support_cta_url'] ); ?>"><?php echo esc_html( $o['support_cta_label'] ); ?></a>
			</div>
		</div>
	</section>
</main>

<?php get_footer(); ?>
