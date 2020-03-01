function athena_settings_secret_keys_list_init() {


  function athena_settings_secret_keys_list_add_created_to_list(alg, data) {
    console.log(data)

    for (var kid = ''; kid.length < 40;) kid += (Math.random() * 16 | 0).toString(16)

    var algSpecific = ''
    if (alg.startsWith('RS')) {
      algSpecific = '<br><textarea readonly="true" required name=\'athena_settings[jwt_secret_keys][' + kid + '][public]\' rows="20" cols="100" autocomplete="off">' + data.public + '</textarea>'
    }

    jQuery('#athena_settings_secret_keys_list').append(
      '<label>' + athena_messages.kid_label + '</label><br>'
      + '<p>' + kid + '</p>'
      + '<br>'
      + '<label><?php echo $secret_label; ?></label><br>'
      + '<textarea readonly="true" required name=\'athena_settings[jwt_secret_keys][' + kid + '][secret]\' rows="20" cols="100" autocomplete="off">' + data.secret + '</textarea>'
      + algSpecific
      + '<br>'
      + '<input size="100" readonly="true" name=\'athena_settings[jwt_secret_keys][' + kid + '][alg]\' type="text" value="' + alg + '">'
      + '<br>'
      + '<textarea rows="3" cols="100" required name=\'athena_settings[jwt_secret_keys][' + kid + '][description]\'></textarea>'
      + '<br>'
      + '<button class="athena_settings_secret_keys_list_delete_item">' + athena_messages.delete + '</button>'
    );

    jQuery('#athena_settings_secret_keys_list_create_new_spinner').removeClass('is-active')
  }

  function athena_settings_secret_keys_list_create_new(event) {
    var alg = jQuery('#athena_settings_secret_keys_choose_alg').val()
    jQuery('#athena_settings_secret_keys_list_create_new_spinner').addClass('is-active')

    jQuery.ajax({
      type: 'POST',
      url: athena_messages.ajaxurl,
      data: {
        action: 'athena_openssl_pkey_new',
        alg: alg
      },
      success: function (output) {
        if (!output.success) return alert(output.data.errors[0].title)
        athena_settings_secret_keys_list_add_created_to_list(alg, output.data)
      },
      error: function (error) {
        console.error(error)
        jQuery('#athena_settings_secret_keys_list_create_new_spinner').removeClass('is-active')
      }
    })
    
    event.preventDefault();
    event.stopPropagation();
    return false;
  }

  function athena_settings_secret_keys_list_delete_item(event) {
    jQuery(event.target)
      .closest('.athena_settings_secret_keys_list_item')
      .remove();
    event.preventDefault();
    event.stopPropagation();
    return false;
  }

  jQuery('#athena_settings_secret_keys_list_create_new')
    .on('click', athena_settings_secret_keys_list_create_new);

  jQuery('.athena_settings_secret_keys_list_delete_item')
    .on('click', athena_settings_secret_keys_list_delete_item);
}


jQuery(document).ready(function () {
  athena_settings_secret_keys_list_init()
})

