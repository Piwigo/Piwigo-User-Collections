<?php
defined('USER_COLLEC_PATH') or die('Hacking attempt!');

# this file is called on basket public page #

global $page, $template, $conf, $user, $tokens;

switch ($page['sub_section'])
{
  /* list */
  case 'list':
  {
    if (is_a_guest()) access_denied();
    
    $template->set_filename('index', dirname(__FILE__) . '/../template/list.tpl');
    
    if ( isset($_GET['action']) and filter_var($_GET['col_id'], FILTER_VALIDATE_INT) !== false )
    {
      switch ($_GET['action'])
      {
        // new
        case 'new':
        {
          new UserCollection('new', array(), empty($_GET['name']) ? 'temp' : $_GET['name'], 1);
          redirect(USER_COLLEC_PUBLIC);
          break;
        }
        
        // clear
        case 'clear':
        {
          $query = '
DELETE ci
  FROM '.COLLECTION_IMAGES_TABLE.' AS ci
    INNER JOIN '.COLLECTIONS_TABLE.' AS c 
    ON ci.col_id = c.id
  WHERE
    c.user_id = '.$user['id'].'
    AND c.id = '.$_GET['col_id'].'
;';
          pwg_query($query);
          
          if (!empty($_SERVER['HTTP_REFERER']))
          {
            redirect($_SERVER['HTTP_REFERER']);
          }
          break;
        }
          
        // delete
        case 'delete':
        {
          $query = '
DELETE ci, c
  FROM '.COLLECTION_IMAGES_TABLE.' AS ci
    RIGHT JOIN '.COLLECTIONS_TABLE.' AS c 
    ON ci.col_id = c.id
  WHERE
    c.user_id = '.$user['id'].'
    AND c.id = '.$_GET['col_id'].'
;';
          pwg_query($query);
      
          redirect(USER_COLLEC_PUBLIC);
          break;
        }
        
        // save
        case 'save':
        {
          if (empty($_GET['name']))
          {
            array_push($page['errors'], l10n('Please give a name'));
          }
          else
          {
            $query = '
UPDATE '.COLLECTIONS_TABLE.'
  SET
    name = "'.pwg_db_real_escape_string($_GET['name']).'",
    active = 0
  WHERE
    user_id = '.$user['id'].'
    AND id = '.$_GET['col_id'].'
;';
            pwg_query($query);
            
            redirect(USER_COLLEC_PUBLIC);
          }
          break;
        }
        
        // set active
        case 'set_active':
        {
          $query = '
UPDATE '.COLLECTIONS_TABLE.'
  SET active = 0
  WHERE user_id = '.$user['id'].'
;';
          pwg_query($query);
          
          $query = '
UPDATE '.COLLECTIONS_TABLE.'
  SET active = 1
  WHERE
    user_id = '.$user['id'].'
    AND id = '.$_GET['col_id'].'
;';
          pwg_query($query);
          
          redirect(USER_COLLEC_PUBLIC);
          break;
        }
      }
    }
      
    // get collections
    $query = '
SELECT * 
  FROM '.COLLECTIONS_TABLE.'
  WHERE user_id = '.$user['id'].'
  ORDER BY date_creation DESC
';
    $collections = hash_from_query($query, 'id');
    
    foreach ($collections as $col)
    {
      $col['date_creation'] = format_date($col['date_creation'], true);
      $col['U_EDIT'] = USER_COLLEC_PUBLIC.'edit/'.$col['id'];
      $col['U_ACTIVE'] = USER_COLLEC_PUBLIC.'&amp;action=set_active&amp;col_id='.$col['id'];
      $col['U_DELETE'] = USER_COLLEC_PUBLIC.'&amp;action=delete&amp;col_id='.$col['id'];
      
      if ($col['name'] == 'temp')
      {
        $col['name'] = 'temp #'.$col['id'];
        $col['U_SAVE'] = USER_COLLEC_PUBLIC.'&amp;action=save&amp;col_id='.$col['id'];
        $template->append('temp_col', $col);
      }
      else
      {
        $template->append('collections', $col);
      }
    }
    
    $template->assign('U_CREATE', USER_COLLEC_PUBLIC.'&amp;action=new&amp;col_id=0');
    break;
  }
  
  /* edit */
  case 'edit':
  {
    if (empty($page['col_id']))
    {
      $_SESSION['page_errors'][] = l10n('Invalid collection');
      redirect(USER_COLLEC_PUBLIC);
    }
    
    $self_url = USER_COLLEC_PUBLIC . 'edit/'.$page['col_id'];
    
    $template->set_filename('index', dirname(__FILE__).'/../template/edit.tpl');
    $template->assign(array(
      'USER_COLLEC_PATH' => USER_COLLEC_PATH,
      'U_VIEW' => $self_url,
      'U_LIST' => USER_COLLEC_PUBLIC,
      'COL_ID' => $page['col_id'],
      ));
    
    try {
      $UserCollection = new UserCollection($page['col_id']);
      
      if (!is_admin() and $UserCollection->getParam('user_id') != $user['id'])
      {
        access_denied();
      }
      
      // save properties
      if (isset($_POST['save_col']))
      {
        $UserCollection->updateParam('name', $_POST['name']);
        $UserCollection->updateParam('public', $_POST['public']);
      }
      
      // remove an element
      if ( isset($_GET['remove']) and preg_match('#^[0-9]+$#', $_GET['remove']) )
      {
        $UserCollection->removeImages(array($_GET['remove']));
      }
      
      $template->assign('collection', $UserCollection->getCollectionInfo());
      
      $template->set_prefilter('index_thumbnails', 'user_collections_thumbnails_list_special_prefilter');
      
      $page['start'] = isset($_GET['start']) ? $_GET['start'] : 0;
      $page['items'] = $UserCollection->getImages();
      
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
      
      include(PHPWG_ROOT_PATH . 'include/category_default.inc.php');
      
      $template->concat('TITLE', $conf['level_separator'].$UserCollection->getParam('name'));
    }
    catch (Exception $e)
    {
      array_push($page['errors'], $e->getMessage());
    }
    
    break;
  }
  
  /* view */
  case 'view':
  {
    if (empty($page['col_id']))
    {
      $_SESSION['page_errors'][] = l10n('Invalid collection');
      redirect(get_home_url());
    }
    
    $self_url = USER_COLLEC_PUBLIC . 'view/'.$page['col_id'];
    
    $template->set_filename('index', dirname(__FILE__).'/../template/view.tpl');
    $template->assign(array(
      'USER_COLLEC_PATH' => USER_COLLEC_PATH,
      'U_VIEW' => $self_url,
      ));
    
    try
    {
      $UserCollection = new UserCollection($page['col_id']);
      
      if ($UserCollection->getParam('user_id') == $user['id'])
      {
        $template->assign('U_LIST', USER_COLLEC_PUBLIC);
      }
      
      $template->assign('collection', $UserCollection->getCollectionInfo());
      
      $page['start'] = isset($_GET['start']) ? $_GET['start'] : 0;
      $page['items'] = $UserCollection->getImages();
      
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
      
      include(PHPWG_ROOT_PATH . 'include/category_default.inc.php');
      
      include_once(PHPWG_ROOT_PATH.'admin/include/functions.php');
      $template->concat('TITLE', 
        $conf['level_separator'].$UserCollection->getParam('name').
        ' ('.sprintf(l10n('by %s'), get_username($UserCollection->getParam('user_id'))).')'
        );
    }
    catch (Exception $e)
    {
      access_denied();
    }
    
    break;
  }
}

$template->assign('USER_COLLEC_PATH', USER_COLLEC_PATH);


function user_collections_thumbnails_list_special_prefilter($content, &$smarty)
{
  // custom style
  $search[0] = '{/html_style}';
  $replace[0] = '.thumbnails  .wrap1 {ldelim} position:relative; }
.addCollection {ldelim} width:100%;height:16px;display:none;position:absolute;top:0;background:rgba(0,0,0,0.8);padding:2px;border-radius:2px;font-size:0.8em; }
.wrap1:hover .addCollection {ldelim} display:block; }'
.$search[0];

  // links
  $search[1] = '<span class="wrap1">';
  $replace[1] = $search[1].'
{strip}<a class="addCollection" href="{$U_VIEW}&amp;remove={$thumbnail.id}" data-id="{$thumbnail.id}" rel="nofollow">
{\'Remove from collection\'|@translate}&nbsp;<img src="{$USER_COLLEC_PATH}template/image_delete.png" title="{\'Remove from collection\'|@translate}">
</a>{/strip}';

  // AJAX request
  $search[2] = '{/html_style}';
  $replace[2] = $search[2].'
{footer_script require=\'jquery\'}
jQuery(".addCollection").click(function() {ldelim}
  var toggle_id = jQuery(this).data("id");
  var $trigger = jQuery(this);
  
  jQuery.ajax({ldelim}
    type: "POST",
    url: "{$USER_COLLEC_PATH}toggle_image.php",
    data: {ldelim} "col_id": "{$COL_ID}", "toggle_id": toggle_id }
  }).done(function(msg) {ldelim}
    if (msg == "false") {ldelim}
      $trigger.parent(".wrap1").hide("fast", function() {ldelim} $trigger.remove() });
    } else {ldelim}
      $trigger.html(\'{\'Un unknown error occured\'|@translate}\');
    }
  });
  
  return false;
});
{/footer_script}';

  return str_replace($search, $replace, $content);
}

?>