<?php
/**
 * Server-Render für den ec/card Block.
 * $attributes, $content, $block stehen automatisch zur Verfügung.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$card_type  = $attributes['cardType'] ?? 'audience';
$title      = $attributes['title'] ?? '';
$text       = $attributes['text'] ?? '';
$url        = $attributes['url'] ?? '';
$link_label = $attributes['linkLabel'] ?? 'Mehr erfahren';

if ( 'people' === $card_type ) {
	$image_url = '';
	if ( ! empty( $attributes['imageId'] ) ) {
		$src = wp_get_attachment_image_url( (int) $attributes['imageId'], 'medium' );
		if ( $src ) {
			$image_url = $src;
		}
	} elseif ( ! empty( $attributes['imageUrl'] ) ) {
		$image_url = $attributes['imageUrl'];
	}
	$initial = '';
	if ( '' !== trim( $title ) ) {
		$initial = function_exists( 'mb_strtoupper' ) ? mb_strtoupper( mb_substr( $title, 0, 1 ) ) : strtoupper( substr( $title, 0, 1 ) );
	}
	$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => 'ec-person-card' ) );
	?>
	<div <?php echo $wrapper_attributes; /* phpcs:ignore, von WP escaped */ ?>>
		<?php if ( $image_url ) : ?>
			<div class="ec-person-card__photo"><img src="<?php echo esc_url( $image_url ); ?>" alt="" width="240" height="240"></div>
		<?php else : ?>
			<div class="ec-person-card__photo" aria-hidden="true"><span class="ec-person-card__initial"><?php echo esc_html( $initial ); ?></span></div>
		<?php endif; ?>
		<?php if ( $title ) : ?><h3><?php echo esc_html( $title ); ?></h3><?php endif; ?>
		<?php if ( $text ) : ?><p><?php echo esc_html( $text ); ?></p><?php endif; ?>
		<?php if ( $url && $link_label ) : ?>
			<a class="ec-text-link" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $link_label ); ?> →</a>
		<?php endif; ?>
	</div>
	<?php
	return;
}

if ( 'age' === $card_type ) {
	$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => 'ec-age-card' ) );
	?>
	<a <?php echo $wrapper_attributes; /* phpcs:ignore, von WP escaped */ ?> href="<?php echo esc_url( $url ); ?>">
		<?php if ( ! empty( $attributes['ageRange'] ) ) : ?>
			<span class="ec-age-card__age"><?php echo esc_html( $attributes['ageRange'] ); ?></span>
		<?php endif; ?>
		<?php if ( $title ) : ?><h3><?php echo esc_html( $title ); ?></h3><?php endif; ?>
		<?php if ( $text ) : ?><p><?php echo esc_html( $text ); ?></p><?php endif; ?>
		<?php if ( $link_label ) : ?><span class="ec-card-link"><?php echo esc_html( $link_label ); ?> →</span><?php endif; ?>
	</a>
	<?php
	return;
}

// Standard: audience-Karte.
$variant             = $attributes['variant'] ?? 'leaf';
$allowed_variants    = array( 'leaf', 'paper', 'soft', 'dark' );
$variant             = in_array( $variant, $allowed_variants, true ) ? $variant : 'leaf';
$wrapper_attributes  = get_block_wrapper_attributes( array( 'class' => 'ec-audience-card ec-card--' . $variant ) );
?>
<a <?php echo $wrapper_attributes; /* phpcs:ignore, von WP escaped */ ?> href="<?php echo esc_url( $url ); ?>">
	<?php if ( ! empty( $attributes['badge'] ) ) : ?>
		<span class="ec-card-number" aria-hidden="true"><?php echo esc_html( $attributes['badge'] ); ?></span>
	<?php endif; ?>
	<?php if ( $title ) : ?><h3><?php echo esc_html( $title ); ?></h3><?php endif; ?>
	<?php if ( $text ) : ?><p><?php echo esc_html( $text ); ?></p><?php endif; ?>
	<?php if ( $link_label ) : ?><span class="ec-card-link"><?php echo esc_html( $link_label ); ?> →</span><?php endif; ?>
</a>
