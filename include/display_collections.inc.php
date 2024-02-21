<?php
defined('USER_COLLEC_PATH') or die('Hacking attempt!');

// collections orders
if (isset($_GET['uc_collection_order']))
{
  if ( (int)$_GET['uc_collection_order'] > 0)
  {
    pwg_set_session_var('uc_collection_order', (int)$_GET['uc_collection_order']);
  }
  else
  {
    pwg_unset_session_var('uc_collection_order');
  }
  redirect(USER_COLLEC_PUBLIC);
}

$col_order_id = pwg_get_session_var('uc_collection_order', 0);
$orders = get_collections_preferred_orders();


// get sorted collections
$query = '
SELECT *
  FROM '.COLLECTIONS_TABLE.'
  WHERE user_id = '.$user['id'].'
  ORDER BY '.$orders[$col_order_id][1].'
';
$categories = hash_from_query($query, 'id');


// pagination
$page['startcat'] = isset($_GET['startcat']) ? $_GET['startcat'] : 0;
$page['total_categories'] = count($categories);

$template->assign('COLLECTIONS_COUNT', $page['total_categories']);

$categories = array_slice(
  $categories,
  $page['startcat'],
  $conf['nb_categories_page']
  );


// order menu
if ($page['total_categories'])
{
  $url = add_url_params(USER_COLLEC_PUBLIC, array('uc_collection_order' => ''));

  foreach ($orders as $order_id => $order)
  {
    if ($order[2])
    {
      $template->append(
        'image_orders',
        array(
          'DISPLAY' => $order[0],
          'URL' => $url.$order_id,
          'SELECTED' => ($col_order_id == $order_id ? true:false),
          )
        );
    }
  }
}


// collections details
if ($page['total_categories'])
{
  $categories_id = array();
  foreach ($categories as $category) {
      $categories_id[] = $category['id'];
  }

  $query = '
SELECT * FROM (
  SELECT
      i.*,
      ci.col_id
    FROM '.IMAGES_TABLE.' AS i
    INNER JOIN '.COLLECTION_IMAGES_TABLE.' AS ci
      ON i.id = ci.image_id
    WHERE col_id IN('.implode(',', $categories_id).')
    ORDER BY ci.add_date DESC
  ) AS t
  GROUP BY col_id
;';
  $thumbnails = hash_from_query($query, 'col_id');

  $thumbnails[0] = array(
    'id' => 0,
    'path' => USER_COLLEC_PATH.'template/images/image.png',
    'picture_ext' => 'png',
    );

  foreach ($thumbnails as &$info)
  {
    $info['src_image'] = new SrcImage($info);
  }
  unset($info);

  $tpl_thumbnails_var = array();

  foreach ($categories as $category)
  {
    $thumb = empty($thumbnails[ $category['id'] ]) ? $thumbnails[0] : $thumbnails[ $category['id'] ];
    $counter = get_display_images_count($category['nb_images'], $category['nb_images'], 0);

    $tpl_var = array_merge($category, array(
      'representative' =>     $thumb,
      'TN_ALT' =>             strip_tags($category['name']),
      'URL' =>                USER_COLLEC_PUBLIC.'edit/'.$category['id'],
      'CAPTION_NB_IMAGES' =>  empty($counter) ? l10n('%d photo', 0) : $counter,
      'NAME' =>               trigger_change('render_category_name', $category['name']),
      'DESCRIPTION' =>        trigger_change('render_category_description', $category['comment'], 'subcatify_category_description'),
      'INFO_DATES' =>         format_date($category['date_creation'], true),
      'U_DELETE' =>           add_url_params(USER_COLLEC_PUBLIC, array('action'=>'delete','col_id'=>$category['id'])),
      ));

    $tpl_thumbnails_var[] = $tpl_var;
  }

  $derivative_params = trigger_change('get_index_album_derivative_params', ImageStdParams::get_by_type(IMG_THUMB) );
  $template->assign(array(
    'maxRequests' => $conf['max_requests'],
    'category_thumbnails' => $tpl_thumbnails_var,
    'derivative_params' => $derivative_params,
    ));

  // navigation bar
  $page['cats_navigation_bar'] = array();
  if ($page['total_categories'] > $conf['nb_categories_page'])
  {
    $page['cats_navigation_bar'] = create_navigation_bar(
      $self_url,
      $page['total_categories'],
      $page['startcat'],
      $conf['nb_categories_page'],
      false, 'startcat'
      );

    $template->assign('cats_navbar', $page['cats_navigation_bar'] );
  }

  $template->set_filename('index_category_thumbnails', 'mainpage_categories.tpl');
  $template->assign_var_from_handle('CATEGORIES', 'index_category_thumbnails');
}
