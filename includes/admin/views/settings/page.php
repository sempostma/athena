<form action='options.php' method='post'>
	<h1>Athena</h1>
	<?php
	settings_fields( 'athena' );
	do_settings_sections( 'athena' );
	submit_button();
	?>
</form>
