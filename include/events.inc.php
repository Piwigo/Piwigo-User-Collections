<?php
defined('USER_COLLEC_PATH') or die('Hacking attempt!');

// +-----------------------------------------------------------------------+
// | SECTION INIT
// +-----------------------------------------------------------------------+
/* define page section from url */
function user_collections_section_init()
{
  global $tokens, $page, $conf;

  if ($tokens[0] == 'collections')
  {
    $page['section'] = 'collections';
    $page['title'] = l10n('Collections');
    $page['body_id'] = 'theCollectionPage';
    $page['is_external'] = true;
    $page['is_homepage'] = false;

    $page['section_title'] = '<a href="'.get_absolute_root_url().'">'.l10n('Home').'</a>'.$conf['level_separator'];
    if (is_a_guest())
    {
      $page['section_title'].= l10n('Collections');
    }
    else
    {
      $page['section_title'].= '<a href="'.USER_COLLEC_PUBLIC.'">'.l10n('Collections').'</a>';
    }

    $page['sub_section'] = 'list';
    if (in_array(@$tokens[1], array('edit','view','list')))
    {
      $page['sub_section'] = $tokens[1];
    }

    if ($page['sub_section']=='edit' && isset($conf['GThumb']) && is_array($conf['GThumb']))
    {
      $conf['GThumb']['big_thumb'] = false; // big thumb is buggy with removes
    }

    if (!empty($tokens[2]))
    {
      $page['col_id'] = $tokens[2];
    }
  }
}

/* collections section */
function user_collections_page()
{
  global $page, $template;

  if (isset($page['section']) and $page['section'] == 'collections')
  {
    include(USER_COLLEC_PATH . 'include/collections.inc.php');
  }

  if (!is_a_guest() && count($page['items']))
  {
    $template->set_filename('uc_thumbnails_cssjs', realpath(USER_COLLEC_PATH . 'template/thumbnails_css_js.tpl'));
    $template->parse('uc_thumbnails_cssjs');
  }
  
  if (isset($page['section']) and $page['section'] == 'categories' and isset($page['category']))
  {
    $template->assign(array(
      'CATEGORY_ID' => $page['category']['id'],
      'USER_COLLEC_PATH' => USER_COLLEC_PATH,
      'USER_COLLEC_ABS_PATH' => realpath(USER_COLLEC_PATH).'/',
      ));
    
    $template->set_filename('uc_button_category', realpath(USER_COLLEC_PATH.'template/buttons/album.tpl'));
    $template->add_index_button($template->parse('uc_button_category', true));
  }
}


// +-----------------------------------------------------------------------+
// | CATEGORY PAGE
// +-----------------------------------------------------------------------+
/* add buttons on thumbnails list */
function user_collections_thumbnails_list($tpl_thumbnails_var, $pictures)
{
  if (is_a_guest()) return $tpl_thumbnails_var;

  global $page, $template, $user;

  // the content is different on collection edition page and no button on batch downloader set edition page
  if (empty($pictures) or (@$page['section'] == 'collections' and @$page['sub_section']=='edit') or @$page['section'] == 'download')
  {
    return $tpl_thumbnails_var;
  }

  $image_ids = array();
  foreach ($pictures as $picture) {
      $image_ids[] = $picture['id'];
  }

  // get collections for each picture
  $query = '
SELECT
    image_id,
    GROUP_CONCAT(col_id) AS col_ids
  FROM '.COLLECTION_IMAGES_TABLE.'
  WHERE col_id IN (
      SELECT id
      FROM '.COLLECTIONS_TABLE.'
      WHERE user_id = '.$user['id'].'
    )
    AND image_id IN('.implode(',', $image_ids).')
  GROUP BY image_id
;';
  $image_collections = simple_hash_from_query($query, 'image_id', 'col_ids');

  foreach ($tpl_thumbnails_var as &$thumbnail)
  {
    $thumbnail['COLLECTIONS'] = @$image_collections[ $thumbnail['id'] ];
  }
  unset($thumbnail);

  // get all collections
  $query = '
SELECT id, name, nb_images
  FROM '.COLLECTIONS_TABLE.'
  WHERE user_id = '.$user['id'].'
  ORDER BY name ASC
;';
  $collections = hash_from_query($query, 'id');

  foreach ($collections as &$col)
  {
    $col["name"] = trigger_change('render_category_name', $col["name"]);
  }
  unset($col);

  $template->assign(array(
    'COLLECTIONS' => $collections,
    'USER_COLLEC_PATH' => USER_COLLEC_PATH,
    ));

  // thumbnails buttons
  $template->set_prefilter('index_thumbnails', 'user_collections_thumbnails_list_button');

  return $tpl_thumbnails_var;
}

// add links
function user_collections_thumbnails_list_button($content, &$smarty)
{
  $search = '#(<li>|<li class="gthumb">)#';
  $replace = '$1
{strip}<a class="addCollection" data-id="{$thumbnail.id}" data-cols="[{$thumbnail.COLLECTIONS}]" rel="nofollow">
{if not isset($UC_IN_EDIT)}
{\'Add to collection\'|translate}&nbsp;<img src="{$ROOT_URL}{$USER_COLLEC_PATH}template/resources/image_add.png" alt="[+]">
{else}
{\'Remove from collection\'|translate}&nbsp;<img src="{$ROOT_URL}{$USER_COLLEC_PATH}template/resources/image_delete.png" alt="[+]">
{/if}
</a>{/strip}';

  return preg_replace($search, $replace, $content);
}


// +-----------------------------------------------------------------------+
// | PICTURE PAGE
// +-----------------------------------------------------------------------+
/* add button on picture page */
function user_collections_picture_page()
{
  if (is_a_guest())
  {
    return;
  }

  global $template, $picture, $user;

  // get collections for this picture
  $query = '
SELECT GROUP_CONCAT(col_id)
  FROM '.COLLECTION_IMAGES_TABLE.'
  WHERE col_id IN (
      SELECT id
      FROM '.COLLECTIONS_TABLE.'
      WHERE user_id = '.$user['id'].'
    )
    AND image_id = '.$picture['current']['id'].'
  GROUP BY image_id
;';
  list($image_collections) = pwg_db_fetch_row(pwg_query($query));

  // get all collections
  $query = '
SELECT id, name, nb_images
  FROM '.COLLECTIONS_TABLE.'
  WHERE user_id = '.$user['id'].'
  ORDER BY name ASC
;';
  $collections = hash_from_query($query, 'id');

  foreach ($collections as &$col)
  {
    $col['name'] = trigger_change('render_category_name', $col["name"]);
  }
  unset($col);

  $template->assign(array(
    'CURRENT_COLLECTIONS' => $image_collections,
    'COLLECTIONS' => $collections,
    'USER_COLLEC_PATH' => USER_COLLEC_PATH,
    'USER_COLLEC_ABS_PATH' => realpath(USER_COLLEC_PATH).'/',
    'IN_PICTURE' => true,
    ));

  // toolbar button
  $template->set_filename('usercol_button', realpath(USER_COLLEC_PATH.'template/buttons/picture.tpl'));
  $button = $template->parse('usercol_button', true);
  $template->add_picture_button($button, 50);
}


// +-----------------------------------------------------------------------+
// | MENU BLOCK
// +-----------------------------------------------------------------------+
/* register block */
function user_collections_add_menublock($menu_ref_arr)
{
  $menu = &$menu_ref_arr[0];
  if (is_a_guest() || $menu->get_id() != 'menubar')
  {
    return;
  }

  $menu->register_block(new RegisteredBlock('mbUserCollection', l10n('Collections'), 'UserCollection'));
}

/* fill block */
function user_collections_applymenu($menu_ref_arr)
{
  $max = 6;

  global $template, $page, $conf, $user;
  $menu = &$menu_ref_arr[0];

  if (($block = $menu->get_block('mbUserCollection')) != null)
  {
    $query = '
SELECT id, name, nb_images
  FROM '.COLLECTIONS_TABLE.'
  WHERE user_id = '.$user['id'].'
  ORDER BY date_creation DESC
;';
    $collections = array_values(hash_from_query($query, 'id'));

    $data['collections'] = array();
    for ($i=0; $i<$max && $i<count($collections); $i++)
    {
      $collections[$i]['name'] = trigger_change('render_category_name', $collections[$i]['name']);
      $collections[$i]['u_edit'] = USER_COLLEC_PUBLIC.'edit/'.$collections[$i]['id'];
      $data['collections'][] = $collections[$i];
    }

    $data['NB_COL'] = count($collections);
    if ($data['NB_COL'] > $max)
    {
      $data['MORE'] = count($collections)-$max;
    }

    $data['U_LIST'] = USER_COLLEC_PUBLIC;

    $block->set_title('<a href="'.USER_COLLEC_PUBLIC.'">'.l10n('Collections').'</a>');
    $template->set_template_dir(USER_COLLEC_PATH.'template/');
    $block->template = 'menubar_user_collections.tpl';
    $block->data = $data;
  }
}
