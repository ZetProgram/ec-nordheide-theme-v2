<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
	<?php $ec_options = ec_nordheide_v2_get_options(); ?>
	<footer class="site-footer">
		<div class="ec-container">
			<div class="site-footer__grid">
				<div><h2 class="site-footer__title">EC Nordheide</h2><p><?php echo esc_html( $ec_options['footer_claim'] ); ?></p></div>
				<div><strong><?php esc_html_e( 'Kontakt', 'ec-nordheide-v2' ); ?></strong><p><?php echo nl2br( esc_html( $ec_options['contact_text'] ) ); ?></p></div>
				<div><strong><?php esc_html_e( 'Weiteres', 'ec-nordheide-v2' ); ?></strong><p><?php if ( $ec_options['instagram_url'] ) : ?><a href="<?php echo esc_url( $ec_options['instagram_url'] ); ?>" rel="noopener">Instagram</a><br><?php endif; ?><?php if ( $ec_options['spotify_url'] ) : ?><a href="<?php echo esc_url( $ec_options['spotify_url'] ); ?>" rel="noopener">Spotify</a><br><?php endif; ?><?php if ( $ec_options['linktree_url'] ) : ?><a href="<?php echo esc_url( $ec_options['linktree_url'] ); ?>" rel="noopener">Linktree</a><br><?php endif; ?><a href="<?php echo esc_url( home_url( '/impressum/' ) ); ?>">Impressum</a><br><a href="<?php echo esc_url( home_url( '/datenschutz/' ) ); ?>">Datenschutz</a></p></div>
			</div>
			<div class="site-footer__bottom"><span>© <?php echo esc_html( gmdate( 'Y' ) ); ?> EC Nordheide</span><span><?php esc_html_e( 'Alle Rechte vorbehalten.', 'ec-nordheide-v2' ); ?></span></div>
		</div>
	</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
