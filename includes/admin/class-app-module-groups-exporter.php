<?php

if (current_user_can('administrator')) { } else {
  // not enough permissions
  die;
}

class AppModuleGroupsExporter
{
  function __construct()
  { }

  public function download()
  { }

  public function get_app_module_groups()
  {
    $terms = get_terms(
      array(
        'taxonomy'   => 'app_module_groups',
        'hide_empty' => false,
      )
    );

    $filename = 'wordpress.' . date( 'Y-m-d' ) . '.xml';

    header( 'Content-Description: File Transfer' );
    header( 'Content-Disposition: attachment; filename=' . $filename );
    header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );

    echo json_encode($terms);

    echo "Hello world";
  }
}

