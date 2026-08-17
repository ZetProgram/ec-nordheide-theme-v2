<?php get_header(); ?>
<main class="ec-section ec-section--paper"><div class="ec-container"><h1 class="ec-heading"><?php the_archive_title(); ?></h1><?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?><article class="ec-news-card"><div class="ec-news-card__body"><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php the_excerpt(); ?></div></article><?php endwhile; the_posts_pagination(); else : ?><p><?php esc_html_e( 'Keine Inhalte gefunden.', 'ec-nordheide-v2' ); ?></p><?php endif; ?></div></main>
<?php get_footer(); ?>
