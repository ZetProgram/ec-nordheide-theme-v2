<?php get_header(); ?>
<main id="main-content" class="ec-section ec-section--paper">
	<div class="ec-container ec-container--narrow ec-empty-state">
		<p class="ec-kicker">404</p>
		<h1 class="ec-heading">Seite nicht gefunden</h1>
		<p class="ec-copy">Die gewünschte Seite gibt es nicht (mehr). Vielleicht hilft dir die Suche weiter, oder du gehst direkt zurück zur Startseite.</p>
		<div class="ec-button-row">
			<a class="ec-button" href="<?php echo esc_url( home_url( '/' ) ); ?>">Zur Startseite</a>
		</div>
		<?php get_search_form(); ?>
	</div>
</main>
<?php get_footer(); ?>
