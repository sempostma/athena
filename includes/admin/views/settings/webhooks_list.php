<?php
$description = esc_html__( 'Should be the App ID from the firebase console.', 'athena' );
$create_new = __('Create new', 'athena');
$key_label = __('Key', 'atehna');
$secret_label = __('Secret', 'atehna');
?>
<div id="athena_settings_wehooks_list">
<?php
foreach ($webhooks_list as $key => $value) {
    $secret = $value['secret'];
    ?>
    <input type="test" name='athena_settings[webhooks_list][]' value='<?php echo $key; // phpcs:ignore ?>' size="50" autocomplete="off" />
    <br echo/><small><?php echo $key_label; ?></small><br><br>
    <input type="test" name='athena_settings[webhooks_list][secret]' value='<?php echo $secret; // phpcs:ignore ?>' size="50" autocomplete="off" />
    <br echo/><small><?php echo $secret_label; ?></small><br><br>
    <?php
}
?>
</div>
<button onclick="athena_settings_wehooks_list_create_new()"><?php echo $create_new; ?></button>
<script>
    function athena_settings_wehooks_list_create_new() {
        var key = 'new_webhook_' + Date.now()
        jQuery('#athena_settings_wehooks_list').append(
            '<input type="text" name="' + key + '" value="' + key + '" <?php echo $readonly; ?> size="50" autocomplete="off" />'
            + '<br echo/><small>' + key + '</small>'
        );
    }
</script>
