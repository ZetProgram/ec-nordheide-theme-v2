<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<form role="search" method="get" class="ec-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<div class="ec-search-form__field">
		<label for="ec-search-field"><?php esc_html_e( 'Suche', 'ec-nordheide-v2' ); ?></label>
		<input
			type="search"
			id="ec-search-field"
			name="s"
			value="<?php echo esc_attr( get_search_query() ); ?>"
			placeholder="<?php esc_attr_e( 'z. B. Jungschar…', 'ec-nordheide-v2' ); ?>"
			autocomplete="off"
			spellcheck="false"
		>
	</div>
	<button type="submit" class="ec-button"><?php esc_html_e( 'Suchen', 'ec-nordheide-v2' ); ?></button>
</form>
