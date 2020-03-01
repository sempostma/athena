<?php
$description = esc_html__( 'Should be the App ID from the firebase console.', 'athena' );
$create_new = __('Create new', 'athena');
$key_label = __('Key', 'athena');
$url_label = __('Url', 'athena');
$url_description = __('A url to call when this trigger fires.', 'athena');
$action_label = __('Action', 'athena');
$method_label = __('Method', 'athena');
$action_data = __('Data', 'athena');
$delete = __('Delete', 'athena');
$tip = __('Tip', 'athena');
$secret_label = __('Secret', 'athena');
$passTestParamater = __('Pass ?test=1 to the trigger to echo the data back.', 'athena');
$json_template_example_title = __('Example json template', 'athena');
$json_template_example_text = __('the variables, {{ display_name }} and {{ user_email }} for example, are retrieved from the data for a particular action.
For the Create User action returns <a href="https://developer.wordpress.org/reference/classes/wp_user/">WP_User</a> which you means you can access the user_email property for example.
Internally get_user_meta is called.', 'athena');
$php_template_example_title = __('Example php template', 'athena');
$passTestParamater = __('Pass ?test=1 to the webhook to echo the data back.', 'athena');
$php_template_warning_json_encode = __('PHP Json encode error', 'athena');
$headers_title = __('Headers (not yet implemented)', 'athena');
$trigger_php_code_explained = __('Accessible variables are:<br>
- $key: the webhook key<br>
- $webhook: the webhook blueprint (contains: secret, method, type, use_php_eval_in_template, template)<br>
- $test = whether this is a test call. Test calls echo the input.<br>
- $template = the same as $webhook["template"]<br>
- $use_php_eval_in_template = the same as $webhook["use_php_eval_in_template"]
.', 'athena');
?>
<style>
    #athena_settings_triggers_alert_1 {
    }
    .athena_settings_triggers_list_item_create_user_info, .template-php-code {
        display: none;
    }
    .athena_settings_triggers_list_item_action + .athena_settings_triggers_list_item_create_user_info {
        display: block;
    }
    .athena_settings_triggers_list_item_use_php_eval_in_template:checked ~ .trigger-template-mustache-formatted-json {
        display: none;
    }
    .athena_settings_triggers_list_item_use_php_eval_in_template:checked ~ .trigger-template-php-code {
        display: block;
    }
</style>
<div class="notice notice-info" id="athena_settings_triggers_alert_1">
    <h4 class="notice-title"><?php echo $tip; ?></h4>
    <p><?php echo $passTestParamater; ?></p>    
</div>
<div id="athena_settings_triggers_list">
<?php
$i = -1;
foreach ($triggers_list as $key => $value) {
    $i++;
    $url = $value['url'];
    $type = $value['type'];
    $template = $value['template'];
    $method = $value['method'];
    $use_php_eval_in_template = array_key_exists('use_php_eval_in_template', $value) && $value['use_php_eval_in_template'] == 'true';
    $test = array_key_exists('test', $value) && $value['test'] == 'true';
    $headers = $value['headers'];
    ?>
    <div class="athena_settings_triggers_list_item">
        <label><?php echo $url_label; ?></label><br>
        <input required type="text" name='athena_settings[triggers_list][<?php echo $key; ?>][url]' value='<?php echo $url; ?>' size="100" autocomplete="off" />
        <br echo/><small><?php echo $url_description; ?></small><br><br>
        <label><?php echo $method_label; ?></label><br>
        <select class="athena_settings_triggers_list_item_method" required name='athena_settings[triggers_list][<?php echo $key; ?>][method]' value='<?php echo $method; ?>'>
            <option value="POST">POST</option>
            <option value="GET">GET</option>
        </select>
        <br>
        <label><?php echo $action_label; ?></label><br>
        <select class="athena_settings_triggers_list_item_action" minlength="80" required type="text" name='athena_settings[triggers_list][<?php echo $key; ?>][type]' value='<?php echo $type; ?>'>
            <option value="insert_user">Create User</option>
        </select>
        <br>
        <div class="notice notice-info athena_settings_triggers_list_item_create_user_info">
            <h4 class="notice-title"><?php echo $tip; ?></h4>
            <p><?php echo $passTestParamater; ?></p>    
        </div>
        <br>
        <input type="checkbox"<?php echo $test ? ' checked' : ''; ?> class="athena_settings_triggers_list_item_test_in_template" name="athena_settings[triggers_list][<?php echo $key; ?>][test]" id="test_in_template<?php echo $i; ?>" value="true">
        <label for="test_in_template<?php echo $i; ?>">Test</label>
        <br>
        <input type="checkbox"<?php echo $use_php_eval_in_template ? ' checked' : ''; ?> class="athena_settings_triggers_list_item_use_php_eval_in_template" name="athena_settings[triggers_list][<?php echo $key; ?>][use_php_eval_in_template]" id="use_php_eval_in_template<?php echo $i; ?>" value="true">
        <label for="use_php_eval_in_template<?php echo $i; ?>">Use PHP Eval (unsafe)</label>
        <br>
        <label><?php echo $headers_title; ?></label><br>
        <textarea onkeydown="enableTab(event)" cols="100" class="athena_settings_triggers_list_item_headers_textarea" rows="10" name="athena_settings[triggers_list][<?php echo $key; ?>][headers]"><?php echo $headers; ?></textarea>
        <br>
        <label><?php echo $action_data; ?></label><br>
        <textarea onkeydown="enableTab(event)" cols="100" class="athena_settings_triggers_list_item_template_textarea" rows="10" name="athena_settings[triggers_list][<?php echo $key; ?>][template]"><?php echo $template; ?></textarea>
        <div class="trigger-template-mustache-formatted-json"><br echo/><small>Mustache formatted json string.</small><br>
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
        <div class="trigger-template-php-code"><br echo/><small>PHP code.</small><br>
            <div class="notice notice-info">
                <h4 class="notice-title"><?php echo $php_template_example_title; ?></h4>
                <br>
                <pre><code</code></pre>
                <br>
                <p><?php echo $trigger_php_code_explained; ?></p>
            </div>
            <br>
            <div class="notice notice-warning">
                <h4 class="notice-title"><?php echo $php_template_warning_json_encode; ?></h4>
                <p>The code should return a value that can be accepted by <a href="https://www.php.net/manual/en/function.json-encode.php" target="_blank">json_encode</a></p>
            </div>
            </div>
        </div>
        <br>
        <button class="athena_settings_triggers_list_delete_item"><?php echo $delete; ?></button>
    </div>
    <?php
}
?>
</div>
<br>
<button type="button" id="athena_settings_triggers_list_create_new"><?php echo $create_new; ?></button>
<script>
    (function() {
        var counter = Date.now();
        function athena_settings_triggers_list_random_string(length) {
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

        function athena_settings_triggers_list_create_new(event) {
            var key = 'new_trigger_' + counter++
            jQuery('#athena_settings_triggers_list').append(
                '<input type="hidden" name="athena_settings[triggers_list][' + key + ']" value="{}" />'
                + '<input required minlength="80" type="text" name="athena_settings[triggers_list][' + key + '][secret]" value="' + athena_settings_triggers_list_random_string(80) + '" size="100" autocomplete="off" />'
                + '<br echo/><small><?php echo $secret_label; ?></small><br><br>'
            );
            event.preventDefault();
            event.stopPropagation();
            return false;
        }

        function athena_settings_triggers_list_delete_item(event) {
            jQuery(event.target)
                .closest('.athena_settings_triggers_list_item')
                .remove();
            event.preventDefault();
            event.stopPropagation();
            return false;
        }

        jQuery('#athena_settings_triggers_list_create_new')
            .on('click', athena_settings_triggers_list_create_new);

        jQuery('.athena_settings_triggers_list_delete_item')
            .on('click', athena_settings_triggers_list_delete_item);
    })();
    
</script>
