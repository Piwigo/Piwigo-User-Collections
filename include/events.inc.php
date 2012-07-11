<?php
defined('USER_COLLEC_PATH') or die('Hacking attempt!');

# this file contains all functions directly called by the triggers #

/* unserialize conf and load language */
function user_collections_init()
{
  load_language('plugin.lang', USER_COLLEC_PATH);
}


/* define page section from url */
function user_collections_section_init()
{
  global $tokens, $page, $conf;
  
  if ($tokens[0] == 'collections')
  {
    $page['section'] = 'collections';
    $page['title'] = '<a href="'.get_absolute_root_url().'">'.l10n('Home').'</a>'.$conf['level_separator'].'<a href="'.USER_COLLEC_PUBLIC.'">'.l10n('Collections').'</a>';
    
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
  
  // if ( script_basename() == 'picture' and @$tokens[1] == 'collections' and preg_match('#^[0-9]+$#', @$tokens[2]) )
  // {
    // try
    // {
      // $UserCollection = new UserCollection($tokens[2]);
      // $page['title'].= $conf['level_separator'].l10n('Collection').': <a href="'.USER_COLLEC_PUBLIC . 'view/'.$tokens[2].'">'.$UserCollection->getParam('name').'</a>';
      // $page['items'] = $UserCollection->getImages();
      // $page['col_id'] = $tokens[2];
    // } catch (Exception $e) {}
  // }
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


/* add buttons on thumbnails list */
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

function user_collections_thumbnails_list($tpl_thumbnails_var, $pictures)
{
  if (is_a_guest()) return $tpl_thumbnails_var;
  
  global $page, $template, $UserCollection;
  
  // the prefilter is different on collection page
  if (isset($page['section']) and ($page['section'] == 'collections' or $page['section'] == 'download')) return $tpl_thumbnails_var;
  
  // get existing collections
  if (empty($UserCollection) and ($col_id = get_current_collection_id(false)) !== false)
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
  
  
  $self_url = duplicate_index_url(array(), array('collection_toggle'));  
  
  foreach ($tpl_thumbnails_var as &$thumbnail)
  {
    if (in_array($thumbnail['id'], $collection))
    {
      $thumbnail['COLLECTION_SELECTED'] = true;
    }
  }
  unset($thumbnail);
  
  // thumbnails buttons
  $template->assign(array(
    'USER_COLLEC_PATH' => USER_COLLEC_PATH,
    'collection_toggle_url' =>  $self_url,
    ));
  $template->set_prefilter('index_thumbnails', 'user_collections_thumbnails_list_prefilter');
  
  return $tpl_thumbnails_var;
}

function user_collections_thumbnails_list_prefilter($content, &$smarty)
{
  // add links
  $search = '<span class="wrap1">';
  $replace = $search.'
{strip}<a class="addCollection" href="{$collection_toggle_url}&amp;collection_toggle={$thumbnail.id}" data-id="{$thumbnail.id}" rel="nofollow">
{if $COL_ID or $thumbnail.COLLECTION_SELECTED}
{\'Remove from collection\'|@translate}&nbsp;<img src="{$USER_COLLEC_PATH}template/image_delete.png" title="{\'Remove from collection\'|@translate}">
{else}
{\'Add to collection\'|@translate}&nbsp;<img src="{$USER_COLLEC_PATH}template/image_add.png" title="{\'Add to collection\'|@translate}">
{/if}
</a>{/strip}';

  // custom CSS and AJAX request
  $content.= file_get_contents(USER_COLLEC_PATH.'template/thumbnails_css_js.tpl');

  return str_replace($search, $replace, $content);
}


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
  
  $url = duplicate_picture_url().'&amp;action=collection_toggle';    
  
  $button = '
<a href="'.$url.'" title="'.($collection?l10n('Remove from collection'):l10n('Add to collection')).'" class="pwg-state-default pwg-button" rel="nofollow">
  <span class="pwg-icon" style="background:url(\''.USER_COLLEC_PATH.'template/image_'.($collection?'delete':'add').'.png\') center center no-repeat;"> </span>
  <span class="pwg-button-text">'.($collection?l10n('Remove from collection'):l10n('Add to collection')).'</span>
</a>';
    
  $template->concat('PLUGIN_PICTURE_ACTIONS', $button);
}


/* menu block */
function user_collections_add_menublock($menu_ref_arr)
{
  if (is_a_guest()) return;
  
  global $user;
  
  $menu = &$menu_ref_arr[0];
  if ($menu->get_id() != 'menubar') return;
  
  $query = '
SELECT id
  FROM '.COLLECTIONS_TABLE.'
  WHERE user_id = '.$user['id'].'
  LIMIT 1
;';
  $result = pwg_query($query);
  
  if (!pwg_db_num_rows($result)) return;
  
  $menu->register_block(new RegisteredBlock('mbUserCollection', l10n('Collections'), 'UserCollection'));
}

function user_collections_applymenu($menu_ref_arr)
{
  global $template, $conf, $UserCollection;
  $menu = &$menu_ref_arr[0];
  
  if (($block = $menu->get_block('mbUserCollection')) != null)
  {
    if (($col_id = get_current_collection_id(false)) !== false)
    {
      if (empty($UserCollection))
      {
        $UserCollection = new UserCollection($col_id);
      }
    
      $data = array(
        'current' => array(
          'NAME' => $UserCollection->getParam('name'),
          'NB_IMAGES' => $UserCollection->getParam('nb_images'),
          ),
        'links' => array(),
        );
        
      if ($data['current']['NB_IMAGES'] > 0)
      {
        $data['links'] = array(
          array(
            'URL' => USER_COLLEC_PUBLIC.'edit/'.$UserCollection->getParam('col_id'),
            'NAME' => l10n('Display collection'),
            ),
          array(
            'URL' => USER_COLLEC_PUBLIC.'&amp;action=clear&amp;col_id='.$UserCollection->getParam('col_id'),
            'NAME' => l10n('Clear collection'),
            ),
          );
      }
    }
    
    $data['U_LIST'] = USER_COLLEC_PUBLIC;
    
    $template->set_template_dir(USER_COLLEC_PATH . 'template/');
    $block->set_title(l10n('Collections'));
    $block->template = 'menublock_user_collec.tpl';
    $block->data = $data;
  }
}

?>