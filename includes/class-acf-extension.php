<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
  die;
}

class Athena_ACF_Extension
{

  public static function get_all_field_objects_cached($object_ids, $options = array())
  {
    

    // global
    global $wpdb;


    // vars
    $field_key = '';
    $value = array();


    // get field_names
    if (is_numeric($post_id)) {
      $keys = $wpdb->get_col($wpdb->prepare(
        "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d and meta_key LIKE %s AND meta_value LIKE %s",
        $post_id,
        '_%',
        'field_%'
      ));
    } elseif (strpos($post_id, 'user_') !== false) {
      $user_id = str_replace('user_', '', $post_id);

      $keys = $wpdb->get_col($wpdb->prepare(
        "SELECT meta_value FROM $wpdb->usermeta WHERE user_id = %d and meta_key LIKE %s AND meta_value LIKE %s",
        $user_id,
        '_%',
        'field_%'
      ));
    } else {
      $keys = $wpdb->get_col($wpdb->prepare(
        "SELECT option_value FROM $wpdb->options WHERE option_name LIKE %s",
        '_' . $post_id . '_%'
      ));
    }
    if (is_array($keys)) {
      foreach ($keys as $key) {
        $field = get_field_object($key, $post_id, $options);

        if (!is_array($field)) {
          continue;
        }

        $value[$field['name']] = $field;
      }
    }


    // no value
    if (empty($value)) {
      return false;
    }


    // return
    return $value;
  }
}
