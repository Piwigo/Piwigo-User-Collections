<?php
defined('USER_COLLEC_PATH') or die('Hacking attempt!');

# this file contains all functions directly called by the triggers #

/* unserialize conf and load language */
function user_collections_init()
{
  global $conf;
  
  // $conf['user_collections'] = unserialize($conf['user_collections']);
  load_language('plugin.lang', USER_COLLEC_PATH);
}


/* define page section from url */
function user_collections_section_init()
{
  global $tokens, $page, $conf;
  
  if ($tokens[0] == 'collections')
  {
    $page['section'] = 'collections';
    $page['title'] = '<a href="'.USER_COLLEC_PUBLIC.'">'.l10n('Collections').'</a>';
    
    switch (@$tokens[1])
    {
      case 'edit':
        $page['sub_section'] = 'edit';
        $page['title'].= $conf['level_separator'].' '.l10n('Edit');
        break;
      case 'view':
        $page['sub_section'] = 'view';
        $page['title'].= $conf['level_separator'].' '.l10n('View');
        break;
      // case 'send':
        // $page['sub_section'] = 'send';
        // $page['title'].= $conf['level_separator'].' '.l10n('Send');
        // break;
      default:
        $page['sub_section'] = 'list';
    }
    
    if (!empty($tokens[2]))
    {
      $page['col_id'] = $tokens[2];
    }
  }
}

/* collection section */
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
  global $page, $template, $UserCollection;
  
  // the prefilter is different on collection page
  if (isset($page['section']) and $page['section'] == 'collections') return $tpl_thumbnails_var;
  
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
  $search = '<span class="thumbName">';
  
  $add = '<a href="{$collection_toggle_url}&amp;collection_toggle={$thumbnail.id}" rel="nofollow">
{if $thumbnail.COLLECTION_SELECTED}
<img src="{$USER_COLLEC_PATH}template/image_delete.png" title="{\'Remove from collection\'|@translate}">
{else}
<img src="{$USER_COLLEC_PATH}template/image_add.png" title="{\'Add to collection\'|@translate}">
{/if}
</a>&nbsp;';

  return str_replace($search, $search.$add, $content);
}


/* add button on picture page */
function user_collections_picture_page()
{
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
  $menu = &$menu_ref_arr[0];
  if ($menu->get_id() != 'menubar') return;
  
  if (get_current_collection_id(false) === false) return;
  
  $menu->register_block(new RegisteredBlock('mbUserCollection', l10n('Download Basket'), 'UserCollection'));
}

function user_collections_applymenu($menu_ref_arr)
{
  global $template, $conf, $UserCollection;
  $menu = &$menu_ref_arr[0];
  
  if (($block = $menu->get_block('mbUserCollection')) != null)
  {
    if (empty($UserCollection))
    {
      $UserCollection = new UserCollection(get_current_collection_id());
    }
    
    $data = array(
      'U_LIST' => USER_COLLEC_PUBLIC,
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
          'URL' => USER_COLLEC_PUBLIC.'view/'.$UserCollection->getParam('col_id'),
          'NAME' => l10n('View'),
          ),
        array(
          'URL' => USER_COLLEC_PUBLIC.'&amp;action=clear&amp;col_id='.$UserCollection->getParam('col_id'),
          'NAME' => l10n('Clear'),
          ),
        );
    }
      
    $template->set_template_dir(USER_COLLEC_PATH . 'template/');
    $block->set_title(l10n('Collections'));
    $block->template = 'menublock.tpl';
    $block->data = $data;
  }
}

?>