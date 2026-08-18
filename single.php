<?php get_header(); ?>
<main id="main-content" class="ec-page ec-section ec-section--paper">
	<div class="ec-container ec-page__container">
		<?php while ( have_posts() ) : the_post(); ?>
			<article <?php post_class( 'ec-page__article' ); ?>>
				<p class="ec-page__meta"><?php echo esc_html( get_the_date() ); ?></p>
				<h1 class="ec-heading"><?php the_title(); ?></h1>
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="ec-page__image"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?></div>
				<?php endif; ?>
				<div class="ec-page__content"><?php the_content(); ?></div>
			</article>
		<?php endwhile; ?>
	</div>
</main>
<?php get_footer(); ?>
