<?php
$description = esc_html__( 'Should be the App ID from the firebase console.', 'athena' );
$readonly    = '';
$input_value = $firebase_app_id;
$input_type  = 'text';
// Override with hidden value if it's been defined as a constant.
if ( $is_global ) {
	$description = esc_html__( 'Defined in wp-config.php', 'athena' );
	$readonly    = 'readonly';
	$input_type  = 'password';
	$input_value = str_repeat( '*', strlen( $input_value ) );
}
?>

<input type="<?php echo $input_type; ?>" name='athena_settings[firebase_app_id]' value='<?php echo $input_value; // phpcs:ignore ?>' <?php echo $readonly; ?> size="50" autocomplete="off" />
<br /><small><?php echo esc_html( $description ); ?></small>
