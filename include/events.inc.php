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
    add_event_handler('loc_begin_page_header', 'user_collections_page_header');
    
    $page['section'] = 'collections';
    $page['section_title'] = '<a href="'.get_absolute_root_url().'">'.l10n('Home').'</a>'.$conf['level_separator'].'<a href="'.USER_COLLEC_PUBLIC.'">'.l10n('Collections').'</a>';
    $page['title'] = l10n('Collections');
    
    if (in_array(@$tokens[1], array('edit','view','list')))
    {
       $page['sub_section'] = $tokens[1];
    }
    else
    {
      $page['sub_section'] = 'list';
    }
    
    if (!empty($tokens[2]))
    {
      $page['col_id'] = $tokens[2];
    }
  }
}

function user_collections_page_header()
{
  global $page;
  $page['body_id'] = 'theCollectionPage';
}

/* collections section */
function user_collections_page()
{
  global $page;

  if (isset($page['section']) and $page['section'] == 'collections')
  {
    include(USER_COLLEC_PATH . '/include/collections.inc.php');
  }
}


// +-----------------------------------------------------------------------+
// | CATEGORY PAGE
// +-----------------------------------------------------------------------+
/* toggle an image, in case of no javascript or first call (must create the collection) */
function user_collections_index_actions()
{
  if (is_a_guest()) return;
  
  global $page, $UserCollection;
     
  // add image to collection list
  if ( isset($_GET['collection_toggle']) and  preg_match('#^[0-9]+$#', $_GET['collection_toggle']) )
  {
    if (empty($UserCollection))
    {
      $UserCollection = new UserCollection(get_current_collection_id(true));
    }
    $UserCollection->toggleImage($_GET['collection_toggle']);
    redirect(duplicate_index_url(array(), array('collection_toggle')));
  }
}

/* add buttons on thumbnails list */
function user_collections_thumbnails_list($tpl_thumbnails_var, $pictures)
{
  if (is_a_guest()) return $tpl_thumbnails_var;
  
  global $page, $template, $UserCollection;
  
  // the content is different on collection edition page and no button on batch downloader set edition page
  if ( (@$page['section'] == 'collections' and @$page['sub_section']=='edit') or @$page['section'] == 'download')
  {
    return $tpl_thumbnails_var;
  }
  
  // get existing collections
  $col_id = get_current_collection_id(false);
  if (empty($UserCollection) and $col_id !== false)
  {
    $UserCollection = new UserCollection($col_id);
    $collection = $UserCollection->getImages();
  }
  else if (!empty($UserCollection))
  {
    $collection = $UserCollection->getImages();
  }
  else
  {
    $collection = array();
  }
  
  // if the collection doesn't exists we don't use AJAX to force menu refresh
  if ($col_id === false)
  {
    $template->assign('NO_AJAX', true);
  }
  else
  {
    $template->assign('AJAX_COL_ID', $col_id );
  }
  
  // template vars
  $url = duplicate_index_url(array(), array('collection_toggle'));
  
  foreach ($tpl_thumbnails_var as &$thumbnail)
  {
    if (in_array($thumbnail['id'], $collection))
    {
      $thumbnail['COLLECTION_SELECTED'] = true;
    }
    $thumbnail['COLLECTION_TOGGLE_URL'] = add_url_params($url, array('collection_toggle'=>$thumbnail['id']));
  }
  unset($thumbnail);
  
  $template->assign(array(
    'USER_COLLEC_PATH' => USER_COLLEC_PATH,
    ));
  
  // thumbnails buttons
  $template->set_prefilter('index_thumbnails', 'user_collections_thumbnails_list_prefilter');
  
  return $tpl_thumbnails_var;
}

function user_collections_thumbnails_list_prefilter($content, &$smarty)
{
  // add links
  $search = '#(<li>|<li class="gthumb">)#';
  $replace = '$1
{strip}<a class="addCollection" href="{$thumbnail.COLLECTION_TOGGLE_URL}" data-id="{$thumbnail.id}" data-stat="{if $thumbnail.COLLECTION_SELECTED}remove{else}add{/if}" rel="nofollow">
<span class="uc_remove" {if not $thumbnail.COLLECTION_SELECTED}style="display:none;"{/if}>
{\'Remove from collection\'|@translate}&nbsp;<img src="{$ROOT_URL}{$USER_COLLEC_PATH}template/resources/image_delete.png" title="{\'Remove from collection\'|@translate}">
</span>
<span class="uc_add" {if $thumbnail.COLLECTION_SELECTED}style="display:none;"{/if}>
{\'Add to collection\'|@translate}&nbsp;<img src="{$ROOT_URL}{$USER_COLLEC_PATH}template/resources/image_add.png" title="{\'Add to collection\'|@translate}">
</span>
</a>{/strip}';

  // custom CSS and AJAX request
  $content.= file_get_contents(USER_COLLEC_PATH.'template/thumbnails_css_js.tpl');
  
  return preg_replace($search, $replace, $content);
}


// +-----------------------------------------------------------------------+
// | PICTURE PAGE
// +-----------------------------------------------------------------------+
/* add button on picture page */
function user_collections_picture_page()
{
  if (is_a_guest()) return;
  
  global $template, $picture, $UserCollection;
  
  // add image to collection list
  if ( isset($_GET['action']) and $_GET['action'] == 'collection_toggle' )
  {
    if (empty($UserCollection))
    {
      $UserCollection = new UserCollection(get_current_collection_id(true));
    }
    
    $UserCollection->toggleImage($picture['current']['id']);
    redirect(duplicate_picture_url());
  }
  
  // get existing collection
  if (empty($UserCollection) and ($col_id = get_current_collection_id(false)) !== false)
  {
    $UserCollection = new UserCollection($col_id);
    $collection = $UserCollection->isInSet($picture['current']['id']);
  }
  else if (!empty($UserCollection))
  {
    $collection = $UserCollection->isInSet($picture['current']['id']);
  }
  else
  {
    $collection = false;
  }  
  
  $url = add_url_params(duplicate_picture_url(), array('action'=>'collection_toggle'));    
  
  $button = '
<a href="'.$url.'" title="'.($collection?l10n('Remove from collection'):l10n('Add to collection')).'" class="pwg-state-default pwg-button" rel="nofollow">
  <span class="pwg-icon" style="background:url(\''.get_root_url().USER_COLLEC_PATH.'template/resources/image_'.($collection?'delete':'add').'.png\') center center no-repeat;"> </span>
  <span class="pwg-button-text">'.($collection?l10n('Remove from collection'):l10n('Add to collection')).'</span>
</a>';
  // $template->add_picture_button($button, 50);
  $template->concat('PLUGIN_PICTURE_ACTIONS', $button);
}


// +-----------------------------------------------------------------------+
// | MENU BLOCK
// +-----------------------------------------------------------------------+
/* register block */
function user_collections_add_menublock($menu_ref_arr)
{
  if (is_a_guest()) return;
  
  $menu = &$menu_ref_arr[0];
  if ($menu->get_id() != 'menubar') return;
    
  $menu->register_block(new RegisteredBlock('mbUserCollection', l10n('Collections'), 'UserCollection'));
}

/* fill block */
function user_collections_applymenu($menu_ref_arr)
{
  $max = 6;
  
  global $template, $page, $conf, $user, $UserCollection;
  $menu = &$menu_ref_arr[0];
  
  // the editable counter is for the active collection, except if we are currently editing a collection
  $col_in_edit = 0;
  if ( @$page['section'] == 'collections' and @$page['sub_section']=='edit' and !empty($page['col_id']) )
  {
    $col_in_edit = $page['col_id'];
  }
  
  if (($block = $menu->get_block('mbUserCollection')) != null)
  {
    $query = '
SELECT *
  FROM '.COLLECTIONS_TABLE.'
  WHERE user_id = '.$user['id'].'
  ORDER BY
    active DESC,
    date_creation DESC
;';
    $collections = array_values(hash_from_query($query, 'id'));
    
    $data['collections'] = array();
    for ($i=0; $i<$max && $i<count($collections); $i++)
    {
      $collections[$i]['count_handler'] = $col_in_edit!=0 ? $collections[$i]['id']==$col_in_edit : $collections[$i]['active'];
      $collections[$i]['U_EDIT'] = USER_COLLEC_PUBLIC.'edit/'.$collections[$i]['id'];
      array_push($data['collections'], $collections[$i]);
    }
    
    $data['NB_COL'] = count($collections);
    if ($data['NB_COL'] > $max)
    {
      $data['MORE'] = count($collections)-$max;
    }
    
    $data['U_LIST'] = USER_COLLEC_PUBLIC;
    $data['U_CREATE'] = add_url_params(USER_COLLEC_PUBLIC, array('action'=>'new','col_id'=>'0','redirect'=>'true'));
    
    $template->set_template_dir(USER_COLLEC_PATH . 'template/');
    $block->set_title('<a href="'.USER_COLLEC_PUBLIC.'">'.l10n('Collections').'</a>');
    $block->template = 'menublock_user_collec.tpl';
    $block->data = $data;
  }
}

?>