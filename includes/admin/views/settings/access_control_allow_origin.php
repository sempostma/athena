<?php
$description = esc_html__( 'Should be: 1. An empty string to disable the header. 2. * 3. A domain (e.g example.com)', 'athena' );
$readonly    = '';
$input_value = $access_control_allow_origin;
$input_type  = 'text';
?>

<input type="<?php echo $input_type; ?>" name='athena_settings[access_control_allow_origin]' value='<?php echo $input_value; // phpcs:ignore ?>' <?php echo $readonly; ?> size="50" autocomplete="off" />
<br /><small><?php echo esc_html( $description ); ?></small>
