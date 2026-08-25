<?php
/**
 * Server-Render für den ec/news Block.
 * $attributes, $content, $block stehen automatisch zur Verfügung.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Textanfang für die News-Karte: immer automatisch aus post_content
 * geschnitten, unabhängig von einem manuell gesetzten Auszug-Feld.
 *
 * In function_exists() gekapselt: WordPress lädt render.php bei jedem
 * Rendern des Blocks per require() (nicht require_once), daher würde
 * eine zweite Karte auf derselben Seite sonst zu einem Fatal Error
 * "Cannot redeclare function" führen.
 */
if ( ! function_exists( 'ec_nordheide_v2_news_excerpt' ) ) {
	function ec_nordheide_v2_news_excerpt( $post_id, $words = 22 ) {
		$content = get_post_field( 'post_content', $post_id );
		$content = strip_shortcodes( $content );
		$content = wp_strip_all_tags( $content );
		return wp_trim_words( $content, $words, '…' );
	}
}

$posts_per_page = ! empty( $attributes['postsPerPage'] ) ? max( 1, (int) $attributes['postsPerPage'] ) : 3;
$category_id    = ! empty( $attributes['categoryId'] ) ? (int) $attributes['categoryId'] : 0;
$show_category  = ! isset( $attributes['showCategory'] ) || $attributes['showCategory'];
$meta_size      = ! empty( $attributes['metaFontSize'] ) ? (float) $attributes['metaFontSize'] : 0.78;
$title_size     = ! empty( $attributes['titleFontSize'] ) ? (float) $attributes['titleFontSize'] : 1.2;
$excerpt_size   = ! empty( $attributes['excerptFontSize'] ) ? (float) $attributes['excerptFontSize'] : 0.95;

$query_args = array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => $posts_per_page,
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
);
if ( $category_id ) {
	$query_args['cat'] = $category_id;
}

$ec_news_query = new WP_Query( $query_args );

if ( ! $ec_news_query->have_posts() ) {
	return;
}

$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => 'ec-newsfeed-grid' ) );
?>
<div <?php echo $wrapper_attributes; /* phpcs:ignore, von WP escaped */ ?>>
	<?php
	while ( $ec_news_query->have_posts() ) :
		$ec_news_query->the_post();
		$ec_news_post_id = get_the_ID();
		$ec_news_cats     = get_the_category( $ec_news_post_id );
		$ec_news_cat_name = ! empty( $ec_news_cats ) ? $ec_news_cats[0]->name : '';
		$ec_news_has_img  = has_post_thumbnail( $ec_news_post_id );
		$ec_news_card_cls = 'ec-newsfeed-card' . ( $ec_news_has_img ? '' : ' ec-newsfeed-card--no-image' );
		?>
		<?php $ec_news_permalink = get_permalink( $ec_news_post_id ); ?>
		<div class="<?php echo esc_attr( $ec_news_card_cls ); ?>">
			<?php if ( $ec_news_has_img ) : ?>
				<div class="ec-newsfeed-card__media">
					<?php echo get_the_post_thumbnail( $ec_news_post_id, 'medium_large' ); ?>
				</div>
			<?php endif; ?>
			<div class="ec-newsfeed-card__body">
				<div class="ec-newsfeed-card__meta" style="font-size:<?php echo esc_attr( $meta_size ); ?>rem">
					<span><?php echo esc_html( get_the_date( '', $ec_news_post_id ) ); ?></span>
					<?php if ( $show_category && $ec_news_cat_name ) : ?><span><?php echo esc_html( $ec_news_cat_name ); ?></span><?php endif; ?>
				</div>
				<h3 class="ec-newsfeed-card__title" style="font-size:<?php echo esc_attr( $title_size ); ?>rem">
					<a class="ec-newsfeed-card__title-link" href="<?php echo esc_url( $ec_news_permalink ); ?>"><?php echo esc_html( get_the_title( $ec_news_post_id ) ); ?></a>
				</h3>
				<p class="ec-newsfeed-card__excerpt" style="font-size:<?php echo esc_attr( $excerpt_size ); ?>rem"><?php echo esc_html( ec_nordheide_v2_news_excerpt( $ec_news_post_id ) ); ?></p>
				<a class="ec-newsfeed-card__readmore" href="<?php echo esc_url( $ec_news_permalink ); ?>"><?php esc_html_e( 'Weiterlesen', 'ec-nordheide-v2' ); ?> →</a>
			</div>
		</div>
		<?php
	endwhile;
	wp_reset_postdata();
	?>
</div>
