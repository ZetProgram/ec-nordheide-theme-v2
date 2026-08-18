<?php
/**
 * Server-Render für den ec/hero Block.
 * $attributes, $content, $block stehen automatisch zur Verfügung
 * (block.json "render": "file:./render.php").
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$image_url = '';
if ( ! empty( $attributes['imageId'] ) ) {
	$src = wp_get_attachment_image_url( (int) $attributes['imageId'], 'full' );
	if ( $src ) {
		$image_url = $src;
	}
} elseif ( ! empty( $attributes['imageUrl'] ) ) {
	$image_url = $attributes['imageUrl'];
}

$hero_class = $image_url ? 'ec-hero--photo' : 'ec-hero--placeholder';
$hero_style = $image_url ? sprintf( ' style="--ec-hero-image: url(%s);"', esc_url( $image_url ) ) : '';

$wrapper_attributes = get_block_wrapper_attributes(
	array( 'class' => 'ec-hero ' . $hero_class )
);
?>
<section <?php echo $wrapper_attributes; /* phpcs:ignore, von WP escaped */ ?><?php echo $hero_style; /* phpcs:ignore, oben escaped */ ?>>
	<div class="ec-container ec-hero__content">
		<?php if ( ! empty( $attributes['kicker'] ) ) : ?>
			<p class="ec-kicker"><?php echo esc_html( $attributes['kicker'] ); ?></p>
		<?php endif; ?>
		<h1 class="ec-hero__title"><?php echo esc_html( $attributes['title'] ?? '' ); ?></h1>
		<?php if ( ! empty( $attributes['subtitle'] ) ) : ?>
			<p class="ec-hero__subtitle"><?php echo esc_html( $attributes['subtitle'] ); ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $attributes['primaryLabel'] ) || ! empty( $attributes['secondaryLabel'] ) ) : ?>
			<div class="ec-button-row">
				<?php if ( ! empty( $attributes['primaryLabel'] ) ) : ?>
					<a class="ec-button" href="<?php echo esc_url( $attributes['primaryUrl'] ?? '' ); ?>"><?php echo esc_html( $attributes['primaryLabel'] ); ?></a>
				<?php endif; ?>
				<?php if ( ! empty( $attributes['secondaryLabel'] ) ) : ?>
					<a class="ec-button ec-button--ghost" href="<?php echo esc_url( $attributes['secondaryUrl'] ?? '' ); ?>"><?php echo esc_html( $attributes['secondaryLabel'] ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
