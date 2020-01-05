<?php
$tip = __('Tip', 'athena');
$passTestParamater = __('Showing ACF in rest responses can cause a significant performance decrease when sending responses with a large amount of entities (posts, pages, etc.)', 'athena');
?>
<input type='checkbox' name='athena_settings[show_acf_in_api]' <?php checked( $show_acf_in_api, 1 ); ?> value='1' />
<div class="notice notice-info">
    <h4 class="notice-title"><?php echo $tip; ?></h4>
    <p><?php echo $passTestParamater; ?></p>    
</div>