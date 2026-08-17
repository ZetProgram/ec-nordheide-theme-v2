<?php
get_header();
?>

<main class="ec-page ec-section ec-section--paper">
	<div class="ec-container ec-page__container">
		<p class="ec-kicker"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
		<h1 class="ec-heading"><?php the_archive_title(); ?></h1>
		<div class="ec-news__list">
			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
				<article class="ec-news-card">
					<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large' ); } ?>
					<div class="ec-news-card__body"><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php the_excerpt(); ?></div>
				</article>
			<?php endwhile; else : ?>
				<p><?php esc_html_e( 'Keine Inhalte gefunden.', 'ec-nordheide-v2' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</main>

<?php get_footer(); ?>
