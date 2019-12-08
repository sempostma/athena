<input type='checkbox' name='athena_settings[disable_legacy_support]' <?php checked( $disable_legacy_support, 1 ); ?> value='1'  <?php echo ( $is_global ? 'disabled' : '' ); ?> />
<?php
if ( $is_global ) {
	echo '<br /><small>' . esc_html__( 'Defined in wp-config.php', 'athena' ) . '</small>';
}
?>
