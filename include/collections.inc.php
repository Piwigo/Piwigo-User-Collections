<?php
defined('USER_COLLEC_PATH') or die('Hacking attempt!');

# this file is called on basket public page #

global $page, $template, $conf, $user, $tokens, $pwg_loaded_plugins;

switch ($page['sub_section'])
{
  /* list */
  case 'list':
  {
    // security
    if (is_a_guest()) access_denied();
    
    $template->set_filename('index', dirname(__FILE__) . '/../template/list.tpl');
    
    // actions
    if ( isset($_GET['action']) and filter_var($_GET['col_id'], FILTER_VALIDATE_INT) !== false )
    {
      switch ($_GET['action'])
      {
        // new
        case 'new':
        {
          $UserCollection = new UserCollection('new', array(), empty($_GET['name']) ? 'temp' : $_GET['name'], 1);
          
          if (isset($_GET['redirect']))
          {
            $redirect = USER_COLLEC_PUBLIC.'edit/'.$UserCollection->getParam('id');
          }
          else
          {
            $redirect = USER_COLLEC_PUBLIC;
          }
          redirect($redirect);
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
      
      if (isset($pwg_loaded_plugins['BatchDownloader']))
      {
        $col['U_DOWNLOAD'] = USER_COLLEC_PUBLIC.'view/'.$col['public_id'].'&amp;action=advdown_set';
      }
      
      // temporary collections are above save collections
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
    // security
    if (empty($page['col_id']))
    {
      $_SESSION['page_errors'][] = l10n('Invalid collection');
      redirect(USER_COLLEC_PUBLIC);
    }
    
    $template->set_filename('index', dirname(__FILE__).'/../template/edit.tpl');
    
    $self_url = USER_COLLEC_PUBLIC . 'edit/'.$page['col_id'];
    $template->assign(array(
      'USER_COLLEC_PATH' => USER_COLLEC_PATH,
      'F_ACTION' => $self_url,
      'collection_toggle_url' => $self_url,
      'U_LIST' => USER_COLLEC_PUBLIC,
      'COL_ID' => $page['col_id'],
      ));
    
    try {
      $UserCollection = new UserCollection($page['col_id']);
      
      // security
      if ( !is_admin() and $UserCollection->getParam('user_id') != $user['id'] )
      {
        access_denied();
      }
      
      // save properties
      if (isset($_POST['save_col']))
      {
        $UserCollection->updateParam('name', $_POST['name']);
        $UserCollection->updateParam('public', $_POST['public']);
      }
      
      // clear
      if ( isset($_GET['action']) and $_GET['action'] == 'clear' )
      {
        $UserCollection->clearImages();
      }
      
      // remove an element
      if ( isset($_GET['collection_toggle']) and preg_match('#^[0-9]+$#', $_GET['collection_toggle']) )
      {
        $UserCollection->removeImages(array($_GET['collection_toggle']));
        unset($_GET['collection_toggle']);
      }
      
      // special template
      add_event_handler('loc_end_index_thumbnails', 'user_collections_thumbnails_in_collection', EVENT_HANDLER_PRIORITY_NEUTRAL+10, 2); // +10 to overload GThumb+
      $template->set_prefilter('index_thumbnails', 'user_collections_thumbnails_list_prefilter');
      
      // collection content
      $col = $UserCollection->getCollectionInfo();
      $col['U_CLEAR'] = $self_url.'&amp;action=clear';
      $col['U_DELETE'] = USER_COLLEC_PUBLIC.'&amp;action=delete&amp;col_id='.$page['col_id'];
      $template->assign('collection', $col);
      
      $page['items'] = $UserCollection->getImages();
      
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
    // security
    if ( empty($page['col_id']) or strlen($page['col_id']) != 10 or strpos($page['col_id'], 'uc') === false )
    {
      $_SESSION['page_errors'][] = l10n('Invalid collection');
      redirect('index.php');
    }
    
    $template->set_filename('index', dirname(__FILE__).'/../template/view.tpl');
    
    $self_url = USER_COLLEC_PUBLIC . 'view/'.$page['col_id'];
    
    try {
      $UserCollection = new UserCollection($page['col_id']);
      
      // special template
      add_event_handler('loc_end_index_thumbnails', 'user_collections_thumbnails_in_collection', EVENT_HANDLER_PRIORITY_NEUTRAL+10, 2); // +10 to overload GThumb+
      
      // collection content
      $page['items'] = $UserCollection->getImages();
      
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


function user_collections_thumbnails_in_collection($tpl_thumbnails_var, $pictures)
{
  global $template, $page;
  
  $template->set_filename('index_thumbnails', dirname(__FILE__).'/../template/thumbnails.tpl');
  
  foreach ($tpl_thumbnails_var as &$thumbnail)
  {
    $src_image = new SrcImage($thumbnail);
    
    $thumbnail['FILE_SRC'] = DerivativeImage::url(IMG_LARGE, $src_image);
    $thumbnail['URL'] = duplicate_picture_url(
        array(
          'image_id' => $thumbnail['id'],
          'image_file' => $thumbnail['file'],
          'section' => 'none',
        ),
        array('start')
      );
  }
  
  return $tpl_thumbnails_var;
}

?>