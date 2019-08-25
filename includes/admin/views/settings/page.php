<form action='options.php' method='post'>
	<h1>Athena</h1>
	<?php
	settings_fields( 'athena' );
	do_settings_sections( 'athena' );
	submit_button();
	?>
	<h2><?php esc_html_e( 'Getting started', 'athena' ); ?></h2>
	<p>
		<?php // Translators: %s is a link to wiki. ?>
		<?php echo sprintf( __( 'To get started check out the <a href="%s" target="_blank" rel="nofollow">documentation</a>', 'athena' ), 'https://github.com/LesterGallagher/athena' ); // phpcs:ignore ?>
	</p>
</form>
