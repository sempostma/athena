<?php
$description = esc_html__( 'Should be a long string of letters, numbers and symbols.', 'athena' );
$readonly    = '';
$input_value = $server_to_server_secret_key;
$input_type  = 'text';
// Override with hidden value if it's been defined as a constant.
?>

<input type="<?php echo $input_type; ?>" name='athena_settings[secret_key]' minlength="64" value='<?php echo $input_value; // phpcs:ignore ?>' <?php echo $readonly; ?> size="100" autocomplete="off" />
<br /><small><?php echo esc_html( $description ); ?></small>
