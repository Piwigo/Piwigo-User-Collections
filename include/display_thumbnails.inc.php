<?php
defined('USER_COLLEC_PATH') or die('Hacking attempt!');


// caddie
if (isset($_GET['uc_caddie']))
{
  fill_caddie($page['items']);
  redirect($self_url);
}

// image order
if (isset($_GET['uc_image_order']))
{
  if ( (int)$_GET['uc_image_order'] > 0)
  {
    pwg_set_session_var('uc_image_order', (int)$_GET['uc_image_order']);
  }
  else
  {
    pwg_unset_session_var('uc_image_order');
  }
  redirect($self_url);
}

// get sorted elements
$image_order_id = pwg_get_session_var('uc_image_order', 0);
$orders = get_collection_preferred_image_orders();

$query = '
SELECT i.id
  FROM '.IMAGES_TABLE.' AS i
  JOIN '.COLLECTION_IMAGES_TABLE.' AS ci
    ON i.id = ci.image_id
    AND ci.col_id = '.$page['col_id'].'
  ORDER BY '.$orders[$image_order_id][1].'
;';
$page['items'] = array_from_query($query, 'id');

// image order menu
if ( $conf['index_sort_order_input']
    and count($page['items']) > 0)
{
  $url = add_url_params($self_url, array('uc_image_order' => ''));
  
  foreach ($orders as $order_id => $order)
  {
    if ($order[2])
    {
      $template->append(
        'image_orders',
        array(
          'DISPLAY' => $order[0],
          'URL' => $url.$order_id,
          'SELECTED' => ($image_order_id == $order_id ? true:false),
          )
        );
    }
  }
}


// navigation bar
$page['start'] = isset($_GET['start']) ? $_GET['start'] : 0;
if (count($page['items']) > $page['nb_image_page'])
{
  $page['navigation_bar'] = create_navigation_bar(
    $self_url,
    count($page['items']),
    $page['start'],
    $page['nb_image_page'],
    false
    );
  $template->assign('navbar', $page['navigation_bar']);
}

// add links for colorbox
add_event_handler('loc_end_index_thumbnails', 'user_collections_thumbnails_in_collection', EVENT_HANDLER_PRIORITY_NEUTRAL, 2);

include(PHPWG_ROOT_PATH . 'include/category_default.inc.php');


// multisize menu
if ( !empty($page['items']) )
{
  $url = add_url_params($self_url, array('display' => ''));
    
  $selected_type = $template->get_template_vars('derivative_params')->type;
  $template->clear_assign( 'derivative_params' );
  $type_map = ImageStdParams::get_defined_type_map();
  unset($type_map[IMG_XXLARGE], $type_map[IMG_XLARGE]);

  foreach($type_map as $params)
  {
    $template->append(
      'image_derivatives',
      array(
        'DISPLAY' => l10n($params->type),
        'URL' => $url.$params->type,
        'SELECTED' => ($params->type == $selected_type ? true:false),
        )
      );
  }
}


// caddie link
if (is_admin() and !empty($page['items']))
{
  $template->assign('U_CADDIE',
     add_url_params($self_url, array('uc_caddie'=>1) )
    );
}
  
?>