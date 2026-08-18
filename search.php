<?php get_header(); ?>

<main id="main-content" class="ec-page ec-section ec-section--paper">
	<div class="ec-container ec-page__container">
		<h1 class="ec-heading">
			<?php
			printf(
				/* translators: %s: search query */
				esc_html__( 'Suchergebnisse für: %s', 'ec-nordheide-v2' ),
				'<span>' . esc_html( get_search_query() ) . '</span>'
			);
			?>
		</h1>
		<?php if ( have_posts() ) : ?>
			<div class="ec-news__list">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article class="ec-news-card">
						<div class="ec-news-card__media">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'large' ); ?>
							<?php endif; ?>
						</div>
						<div class="ec-news-card__body">
							<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
						</div>
					</article>
				<?php endwhile; ?>
			</div>
			<nav class="ec-pagination" aria-label="<?php esc_attr_e( 'Beitragsnavigation', 'ec-nordheide-v2' ); ?>">
				<?php
				echo wp_kses_post(
					paginate_links(
						array(
							'prev_text' => __( '← Neuer', 'ec-nordheide-v2' ),
							'next_text' => __( 'Älter →', 'ec-nordheide-v2' ),
						)
					)
				);
				?>
			</nav>
		<?php else : ?>
			<div class="ec-empty-state">
				<p class="ec-copy"><?php esc_html_e( 'Dazu haben wir leider nichts gefunden. Versuch es mit einem anderen Begriff.', 'ec-nordheide-v2' ); ?></p>
				<?php get_search_form(); ?>
			</div>
		<?php endif; ?>
	</div>
</main>

<?php get_footer(); ?>
