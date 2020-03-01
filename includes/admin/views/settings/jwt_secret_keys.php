<?php
$description = esc_html__( 'Should be the App ID from the firebase console.', 'athena' );
$create_new = __('Create new', 'athena');
$key_label = __('Key', 'athena');
$secret_label = __('Secret', 'athena');
$secret_description = __('Must be a long string of characters. Do not share this with anyone.', 'athena');
$delete = __('Delete', 'athena');
$kid_label = __('kid', 'athena');
$alg_label = __('alg', 'athena');
?>
<style>
    #athena_settings_secret_keys_alert_1 {
        background: rgba
    }
    .athena_settings_secret_keys_list_item_create_user_info, .template-php-code, .athena_settings_secret_keys_action_insert_user_with_userdata {
        display: none;
    }
    .athena_settings_secret_keys_list_item_action + .athena_settings_secret_keys_list_item_create_user_info {
        display: block;
    }
    .athena_settings_secret_keys_list_item_use_php_eval_in_template:checked ~ .template-mustache-formatted-json {
        display: none;
    }
    .athena_settings_secret_keys_list_item_use_php_eval_in_template:checked ~ .template-php-code {
        display: block;
    }
    .athena_settings_secret_keys_list_item_action[value="insert_user"] ~ .athena_settings_secret_keys_action_insert_user_with_userdata {
        display: block;
    }
</style>
<div id="athena_settings_secret_keys_list">
<?php
foreach ($jwt_secret_keys as $kid => $value) {
    $secret = $value['secret'];
    $description = array_key_exists('description', $value) ? $value['description'] : '';
    $public = array_key_exists('public', $value) ? $value['public'] : '';
    $alg = $value['alg'];

    ?>
    <div class="athena_settings_secret_keys_list_item">
        <label><?php echo $kid_label; ?></label><br>
        <p><?php echo $kid; ?></p>
        <br>
        <label><?php echo $secret_label; ?></label><br>
        <textarea readonly="readonly" required type="text" name='athena_settings[jwt_secret_keys][<?php echo $kid; ?>][secret]' rows="20" cols="100" autocomplete="off"><?php echo htmlspecialchars($secret, ENT_QUOTES, 'UTF-8', false); ?></textarea>
        <?php if ($public) { ?>
            <textarea readonly="readonly" required type="text" name='athena_settings[jwt_secret_keys][<?php echo $kid; ?>][public]' rows="20" cols="100" autocomplete="off"><?php echo htmlspecialchars($public, ENT_QUOTES, 'UTF-8', false); ?></textarea>
        <?php } ?>
        <br>
        <label><?php echo $alg_label; ?></label><br>
        <input required readonly="true" name='athena_settings[jwt_secret_keys][<?php echo $kid; ?>][alg]' value='<?php echo $alg; ?>'>
        <br>
        <br>
        <button class="athena_settings_secret_keys_list_delete_item"><?php echo $delete; ?></button>
    </div>
    <?php
}
?>
</div>
<br>
<div id="athena_settings_secret_keys_list_create_new_spinner" class="spinner"></div>
<select id="athena_settings_secret_keys_choose_alg" required value="RS512">
    <option value="ES256">ES256 (openssl, SHA256)</option>
    <option value="HS256">HS256 (hash_hmac, SHA256)</option>
    <option value="HS384">HS384 (hash_hmac, SHA384)</option>
    <option value="HS384">HS512 (hash_hmac, SHA512)</option>
    <option value="RS256">RS256 (openssl, SHA256)</option>
    <option value="RS384">RS384 (openssl, SHA384)</option>
    <option value="RS512">RS512 (openssl, SHA512)</option>
</select>
<button type="button" id="athena_settings_secret_keys_list_create_new"><?php echo $create_new; ?></button>