<?php
/**
 * Standard-Seitentemplate für WordPress-Seiten.
 *
 * Für die als Startseite eingestellte Seite (Einstellungen -> Lesen)
 * wird der automatische Seitentitel unterdrückt und der Freiraum über
 * dem Inhalt entfernt, damit ein Hero-Block direkt am oberen Rand
 * beginnen kann. Alle anderen Seiten zeigen Titel und übliches Padding.
 */
get_header();
$is_home = is_front_page();
?>

<main id="main-content" class="ec-page ec-section--paper">
	<div class="ec-container ec-page__container<?php echo $is_home ? ' ec-page__container--flush' : ''; ?>">
		<?php while ( have_posts() ) : the_post(); ?>
			<article <?php post_class( 'ec-page__article' ); ?>>
				<?php if ( ! $is_home ) : ?>
					<h1 class="ec-heading"><?php the_title(); ?></h1>
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="ec-page__image"><?php the_post_thumbnail( 'large' ); ?></div>
					<?php endif; ?>
				<?php endif; ?>
				<div class="ec-page__content"><?php the_content(); ?></div>
			</article>
		<?php endwhile; ?>
	</div>
</main>

<?php get_footer(); ?>
