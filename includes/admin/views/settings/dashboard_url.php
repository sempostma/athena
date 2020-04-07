<?php
$description = esc_html__( 'The url to load the Athena dashboard from.', 'athena' );
$input_value = $dashboard_url;
$input_type  = 'url';
?>
<input required type="<?php echo $input_type; ?>" name='athena_settings[dashboard_url]' value='<?php echo $input_value; // phpcs:ignore ?>' size="100" autocomplete="off" />
<br /><small><?php echo esc_html( $description ); ?></small>
