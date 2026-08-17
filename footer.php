<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
	<footer class="site-footer">
		<div class="ec-container">
			<div class="site-footer__grid">
				<div><h2 class="site-footer__title">EC Nordheide</h2><p>Glaube, Gemeinschaft &amp; Leben.</p></div>
				<div><strong><?php esc_html_e( 'Kontakt', 'ec-nordheide-v2' ); ?></strong><p>Kreisverband Nordheide<br>„Entschieden für Christus“ e.V.</p></div>
				<div><strong><?php esc_html_e( 'Weiteres', 'ec-nordheide-v2' ); ?></strong><p><a href="<?php echo esc_url( home_url( '/impressum/' ) ); ?>">Impressum</a><br><a href="<?php echo esc_url( home_url( '/datenschutz/' ) ); ?>">Datenschutz</a></p></div>
			</div>
			<div class="site-footer__bottom"><span>© <?php echo esc_html( gmdate( 'Y' ) ); ?> EC Nordheide</span><span><?php esc_html_e( 'Alle Rechte vorbehalten.', 'ec-nordheide-v2' ); ?></span></div>
		</div>
	</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
