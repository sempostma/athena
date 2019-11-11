<?php
$description = esc_html__( 'Should be the App ID from the firebase console.', 'athena' );
$create_new = __('Create new', 'athena');
$key_label = __('Key', 'atehna');
$secret_label = __('Secret', 'atehna');
$secret_description = __('Must be a long string of characters. Do not share this with anyone.', 'atehna');
$action_label = __('Action', 'athena');
$delete = __('Delete', 'atehna');
echo json_encode($webhooks_list);
// delete_option('athena_settings');
?>
<div id="athena_settings_webhooks_list">
<?php
foreach ($webhooks_list as $key => $value) {
    $secret = $value['secret'];
    $type = $value['type'];
    $template = $value['template'];
    ?>
    <div class="athena_settings_webhooks_list_item">
        <h5><?php echo $key; ?></h5>
        <label><?php echo $secret_label; ?></label><br>
        <input minlength="80" required type="text" name='athena_settings[webhooks_list][<?php echo $key; ?>][secret]' value='<?php echo $secret; ?>' size="50" autocomplete="off" />
        <br echo/><small><?php echo $secret_description; ?></small><br><br>
        <label><?php echo $action_label; ?></label><br>
        <select minlength="80" required type="text" name='athena_settings[webhooks_list][<?php echo $key; ?>][type]' value='<?php echo $type; ?>'>
            <option value="insert_user">Create User</option>
        </select>
        <br>
        <textarea cols="100" rows="10" name="athena_settings[webhooks_list][<?php echo $key; ?>][template]"><?php echo $template; ?></textarea>
        <br echo/><small>Mustache formatted json string.</small><br><br>
        <button class="athena_settings_webhooks_list_delete_item"><?php echo $delete; ?></button>
    </div>
    <?php
}
?>
</div>
<br>
<button type="button" id="athena_settings_webhooks_list_create_new"><?php echo $create_new; ?></button>
<script>
    (function() {
        var counter = Date.now();
        function athena_settings_webhooks_list_random_string(length) {
            length = length || 256
            var items = new Array(length);
            var max = 122;
            var min = 48;
            var range = max - min; 
            for(let i = 0; i < length; i++) {
                const charCode = min + Math.floor(Math.random() * range);
                items[i] = String.fromCharCode(charCode);
            }
            return items.join('');
        }

        function athena_settings_webhooks_list_create_new(event) {
            var key = 'new_webhook_' + counter++
            jQuery('#athena_settings_webhooks_list').append(
                '<input type="hidden" name="athena_settings[webhooks_list][' + key + ']" value="{}" />'
                + '<input required minlength="80" type="text" name="athena_settings[webhooks_list][' + key + '][secret]" value="' + athena_settings_webhooks_list_random_string(80) + '" <?php echo $readonly; ?> size="50" autocomplete="off" />'
                + '<br echo/><small><?php echo $secret_label; ?></small><br><br>'
            );
            event.preventDefault();
            event.stopPropagation();
            return false;
        }

        function athena_settings_webhooks_list_delete_item(event) {
            jQuery(event.target)
                .closest('.athena_settings_webhooks_list_item')
                .remove();
            event.preventDefault();
            event.stopPropagation();
            return false;
        }

        jQuery('#athena_settings_webhooks_list_create_new')
            .on('click', athena_settings_webhooks_list_create_new);

        jQuery('.athena_settings_webhooks_list_delete_item')
            .on('click', athena_settings_webhooks_list_delete_item);
    })();
    
</script>
