<input type='checkbox' name='athena_settings[app_modules_post_type_force_private]' <?php checked( $app_modules_post_type_force_private, 1 ); ?> value='1'  <?php echo ( $is_global ? 'disabled' : '' ); ?> />
<?php
if ( $is_global ) {
	echo '<br /><small>' . esc_html__( 'Defined in wp-config.php', 'athena' ) . '</small>';
}
?>
