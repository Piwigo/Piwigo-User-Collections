<?php
defined('USER_COLLEC_PATH') or die('Hacking attempt!');

if (defined('USER_COLLEC_REMOVE_GTHUMB'))
{
  global $uc_nb_image_page_save;

  $user['nb_image_page'] = $uc_nb_image_page_save['user'];
  $page['nb_image_page'] = $uc_nb_image_page_save['page'];
  remove_event_handler('loc_end_index_thumbnails', 'process_GThumb', 50);
  remove_event_handler('loc_end_index', 'GThumb_remove_thumb_size');
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

// display
include(PHPWG_ROOT_PATH . 'include/category_default.inc.php');

// multisize link
$url = add_url_params(
    $self_url,
    array('display' => '')
  );
  
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
  
?>