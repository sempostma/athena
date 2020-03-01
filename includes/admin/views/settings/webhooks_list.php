<?php
$description = esc_html__( 'Should be the App ID from the firebase console.', 'athena' );
$create_new = __('Create new', 'athena');
$key_label = __('Key', 'athena');
$secret_label = __('Secret', 'athena');
$secret_description = __('Must be a long string of characters. Do not share this with anyone.', 'athena');
$action_label = __('Action', 'athena');
$method_label = __('Method', 'athena');
$action_data = __('Data', 'athena');
$delete = __('Delete', 'athena');
$tip = __('Tip', 'athena');
$json_template_example_title = __('Example json template', 'athena');
$json_template_example_text = __('the variables, {{ username }}, {{ email }} for example, are retrieved from the query and body of the request.', 'athena');
$php_template_example_title = __('Example php template', 'athena');
$passTestParamater = __('Pass ?test=1 to the webhook to echo the data back.', 'athena');
$php_template_warning_json_encode = __('PHP Json encode error', 'athena');
$secret_may_be_passed_in_body_for_more_security = __('Secret may be passed in the body for more security.', 'athena');
$action_insert_user_with_userdata = __('<a target="_blank" href="https://developer.wordpress.org/reference/functions/wp_insert_user/">wp_insert_user($data)</a>');
$webhook_php_code_explained = __('Accessible variables are:<br>
- $key: the webhook key<br>
- $webhook: the webhook blueprint (contains: secret, method, type, use_php_eval_in_template, template)<br>
- $test = whether this is a test call. Test calls echo the input.<br>
- $template = the same as $webhook["template"]<br>
- $use_php_eval_in_template = the same as $webhook["use_php_eval_in_template"]
.', 'athena');
$webhook_php_code_explained_title = __('Execution context', 'athena');
?>
<style>
    #athena_settings_webhooks_alert_1 {
        background: rgba
    }
    .athena_settings_webhooks_list_item_create_user_info, .template-php-code, .athena_settings_webhooks_action_insert_user_with_userdata {
        display: none;
    }
    .athena_settings_webhooks_list_item_action + .athena_settings_webhooks_list_item_create_user_info {
        display: block;
    }
    .athena_settings_webhooks_list_item_use_php_eval_in_template:checked ~ .template-mustache-formatted-json {
        display: none;
    }
    .athena_settings_webhooks_list_item_use_php_eval_in_template:checked ~ .template-php-code {
        display: block;
    }
    .athena_settings_webhooks_list_item_action[value="insert_user"] ~ .athena_settings_webhooks_action_insert_user_with_userdata {
        display: block;
    }
</style>
<div id="athena_settings_webhooks_list">
<?php
foreach ($webhooks_list as $key => $value) {
    $secret = $value['secret'];
    $type = $value['type'];
    $template = $value['template'];
    $method = $value['method'];
    $use_php_eval_in_template = $value['use_php_eval_in_template'];
    ?>
    <div class="athena_settings_webhooks_list_item">
        <small><b>Url:</b> /wp-json/athena/v1/webhooks/incoming?key=<?php echo $key; ?>?secret=<?php echo htmlspecialchars($secret); ?></small> 
        <div class="notice notice-info">
            <p><?php echo $secret_may_be_passed_in_body_for_more_security; ?></p>    
        </div>
        <div class="notice notice-info" id="athena_settings_webhooks_alert_1">
            <p><?php echo $passTestParamater; ?></p>    
        </div>
        <br>
        <label><?php echo $secret_label; ?></label><br>
        <input minlength="80" required type="text" name='athena_settings[webhooks_list][<?php echo $key; ?>][secret]' value='<?php echo htmlspecialchars($secret, ENT_QUOTES, 'UTF-8', false); ?>' size="100" autocomplete="off" />
        <br echo/><small><?php echo $secret_description; ?></small><br><br>
        <label><?php echo $method_label; ?></label><br>
        <select class="athena_settings_webhooks_list_item_method" required name='athena_settings[webhooks_list][<?php echo $key; ?>][method]' value='<?php echo $method; ?>'>
            <option value="POST">POST</option>
            <option value="GET">GET</option>
            <option value="PUT">PUT</option>
            <option value="PATCH">PATCH</option>
            <option value="DELETE">DELETE</option>
        </select>
        <br>
        <label><?php echo $action_label; ?></label><br>
        <select class="athena_settings_webhooks_list_item_action" minlength="80" required type="text" name='athena_settings[webhooks_list][<?php echo $key; ?>][type]' value='<?php echo $type; ?>'>
            <option value="insert_user">Create User</option>
        </select>
        <br>
        <div class="notice notice-info athena_settings_webhooks_action_insert_user_with_userdata">
            <p><?php echo $action_insert_user_with_userdata; ?></p>    
        </div>
        <br>
        <div class="notice notice-info athena_settings_webhooks_list_item_create_user_info">
            <p><?php echo $passTestParamater; ?></p>    
        </div>
        <br>
        <input type="checkbox"<?php echo $use_php_eval_in_template ? ' checked' : ''; ?> class="athena_settings_webhooks_list_item_use_php_eval_in_template" name="athena_settings[webhooks_list][<?php echo $key; ?>][use_php_eval_in_template]" value="true" id="use_php_eval_in_template<?php echo $key; ?>">
        <label for="use_php_eval_in_template<?php echo $key; ?>">Use PHP Eval (unsafe)</label>
        <br>
        <label><?php echo $action_data; ?></label><br>
        <textarea onkeydown="enableTab(event)" cols="100" rows="10" name="athena_settings[webhooks_list][<?php echo $key; ?>][template]"><?php echo $template; ?></textarea>
        <div class="template-mustache-formatted-json"><br echo/><small>Mustache formatted json string.</small><br>
            <div class="notice notice-info">
                <h4 class="notice-title"><?php echo $json_template_example_title; ?></h4>
                <br>
                <pre><code>{
    "user_email": {{email}},
    "user_login": {{username}},
    "user_nicename": {{username}},
    "first_name": {{username}},
    "last_name": {{last_name}},
    "role": "app_module_user",
    "locale": "nl_NL"
}</code></pre>
                <br>
                <p><?php echo $json_template_example_text; ?></p>
            </div>
        </div>
        <div class="template-php-code"><br echo/><small>PHP code.</small><br>
            <div class="notice notice-info">
                <h4 class="notice-title"><?php echo $php_template_example_title; ?></h4>
                <br>
                <pre><code></code></pre>
                <br>
            </div>
            <br>
            <div class="notice notice-info">
                <h4 class="notice-title"><?php echo $webhook_php_code_explained_title; ?></h4>
                <p><?php echo $webhook_php_code_explained; ?></p>
            </div>
            <br>
            <div class="notice notice-warning">
                <h4 class="notice-title"><?php echo $php_template_warning_json_encode; ?></h4>
                <p>The code should return a value that can be accepted by <a href="https://www.php.net/manual/en/function.json-encode.php" target="_blank">json_encode</a></p>
            </div>
            <br>
            
            </div>
        </div>
        <br>
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
            var key = 'webhook_' + counter++
            jQuery('#athena_settings_webhooks_list').append(
                '<input type="hidden" name="athena_settings[webhooks_list][' + key + ']" value="{}" />'
                + '<input required minlength="80" type="text" name="athena_settings[webhooks_list][' + key + '][secret]" value="' + athena_settings_webhooks_list_random_string(80) + '" size="100" autocomplete="off" />'
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
