<?php get_header(); ?>
<main class="ec-section ec-section--paper"><div class="ec-container"><?php while ( have_posts() ) : the_post(); ?><article><p class="ec-kicker"><?php echo esc_html( get_the_date() ); ?></p><h1 class="ec-heading"><?php the_title(); ?></h1><?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large' ); } ?><div class="ec-copy"><?php the_content(); ?></div></article><?php endwhile; ?></div></main>
<?php get_footer(); ?>
