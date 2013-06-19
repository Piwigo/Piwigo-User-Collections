<?php
defined('USER_COLLEC_PATH') or die('Hacking attempt!');

# this file is called on basket public page #

global $page, $template, $conf, $user, $tokens, $pwg_loaded_plugins;

$template->assign(array(
  'USER_COLLEC_PATH' => USER_COLLEC_PATH,
  'USER_COLLEC_ABS_PATH' => realpath(USER_COLLEC_PATH).'/',
  ));

switch ($page['sub_section'])
{
  /* list */
  case 'list':
  {
    // security
    if (is_a_guest()) access_denied();
    
    $template->set_filename('index', realpath(USER_COLLEC_PATH.'template/list.tpl'));
    
    // actions
    if ( isset($_GET['action']) and preg_match('#^([0-9]+)$#', $_GET['col_id']) )
    {
      switch ($_GET['action'])
      {
        // new
        case 'new':
        {
          if (empty($_GET['name']))
          {
            array_push($page['errors'], l10n('Please give a name'));
          }
          else
          {
            $UserCollection = new UserCollection('new', array(), $_GET['name'], 1);
            
            if (isset($_GET['redirect']))
            {
              $redirect = USER_COLLEC_PUBLIC.'edit/'.$UserCollection->getParam('id');
            }
            else
            {
              $redirect = USER_COLLEC_PUBLIC;
            }
            redirect($redirect);
          }
          break;
        }
          
        // delete
        case 'delete':
        {
          try {
            $UserCollection = new UserCollection($_GET['col_id']);
            $UserCollection->delete();
            redirect(USER_COLLEC_PUBLIC);
          }
          catch (Exception $e)
          {
            $page['errors'][] = $e->getMessage();
          }
          break;
        }
        
        // set active
        case 'toggle_active':
        {
          $query = '
UPDATE '.COLLECTIONS_TABLE.'
  SET active = IF(active=1, 0, 1)
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
    
    $template->assign('U_CREATE', add_url_params(USER_COLLEC_PUBLIC, array('action'=>'new','col_id'=>'0')));
    
    $template->set_prefilter('index_category_thumbnails', 'user_collections_categories_list');
    
    include(USER_COLLEC_PATH . '/include/display_collections.inc.php');
    
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
    
    $template->set_filename('index', realpath(USER_COLLEC_PATH.'template/edit.tpl'));
    
    $self_url = USER_COLLEC_PUBLIC . 'edit/'.$page['col_id'];
    
    $template->assign(array(
      'user_collections' => $conf['user_collections'],
      'F_ACTION' => $self_url,
      'collection_toggle_url' => $self_url,
      'U_LIST' => USER_COLLEC_PUBLIC,
      'UC_IN_EDIT' => true,
      ));
    
    try {
      $UserCollection = new UserCollection($page['col_id']);
      
      // save properties
      if (isset($_POST['save_col']))
      {
        if (empty($_POST['name']))
        {
          array_push($page['errors'], l10n('Please give a name'));
        }
        else
        {
          $UserCollection->updateParam('name', stripslashes($_POST['name']));
        }
        if (!$conf['user_collections']['allow_public'])
        {
          $_POST['public'] = '0';
        }
        $UserCollection->updateParam('public', $_POST['public']);
      }
      
      // send mail
      if ( $conf['user_collections']['allow_public'] and $conf['user_collections']['allow_mails'] )
      {
        $contact = array(
            'sender_name' => $user['username'],
            'sender_email' => $user['email'],
            'recipient_name' => null,
            'recipient_email' => null,
            'nb_images' => 4,
            'message' => null,
            );
            
        if ( isset($_POST['send_mail']) and (bool)$UserCollection->getParam('public') )
        {
          $contact = array(
            'sender_email' => trim($_POST['sender_email']),
            'sender_name' => trim($_POST['sender_name']),
            'recipient_email' => trim($_POST['recipient_email']),
            'recipient_name' => trim($_POST['recipient_name']),
            'nb_images' => $_POST['nb_images'],
            'message' => $_POST['message'],
            );
            
          $errors = $UserCollection->sendEmail($contact, @$_POST['key']);
          if (count($errors))
          {
            $template->assign('uc_mail_errors', $errors);
          }
          else
          {
            array_push($page['infos'], l10n('E-mail sent successfully'));
          }
        }
        
        $contact['KEY'] = get_ephemeral_key(3);
        $template->assign('contact', $contact);
      }
      
      // clear
      if ( isset($_GET['action']) and $_GET['action'] == 'clear' )
      {
        $UserCollection->clearImages();
      }
      
      
      // add remove item links
      $template->set_prefilter('index_thumbnails', 'user_collections_thumbnails_list_button');
      $template->set_prefilter('index', 'user_collections_thumbnails_list_cssjs');
      $template->set_prefilter('index_thumbnails', 'user_collections_add_colorbox');
      
      // thumbnails
      include(USER_COLLEC_PATH . '/include/display_thumbnails.inc.php');
      
      
      // collection properties
      $col = $UserCollection->getCollectionInfo();
      $col['DATE_CREATION'] = format_date($col['DATE_CREATION'], true);
      $template->assign('collection', $col); 
      
      // toolbar buttons
      if (!empty($page['items']))
      {
        $template->assign('U_CLEAR',
          add_url_params($self_url, array('action'=>'clear') )
          );
      }
      $template->assign('U_DELETE',
        add_url_params(USER_COLLEC_PUBLIC, array('action'=>'delete','col_id'=>$page['col_id']))
        );
      if ($conf['user_collections']['allow_public'] and $conf['user_collections']['allow_mails'] and !empty($page['items']))
      {
        $template->assign('U_MAIL', true);
      }
      
      
      $template->concat('TITLE', 
        $conf['level_separator'].$UserCollection->getParam('name')
        );
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
    if ( empty($page['col_id']) or strlen($page['col_id']) != 10 or strpos($page['col_id'], 'uc') === false or !$conf['user_collections']['allow_public'] )
    {
      $_SESSION['page_errors'][] = l10n('Invalid collection');
      redirect('index.php');
    }
    
    $template->set_filename('index', dirname(__FILE__).'/../template/view.tpl');
    
    $self_url = USER_COLLEC_PUBLIC . 'view/'.$page['col_id'];
    
    try {
      $UserCollection = new UserCollection($page['col_id']);
      $page['col_id'] = $UserCollection->getParam('id');
      
      $template->set_prefilter('index_thumbnails', 'user_collections_add_colorbox');
      
      // thumbnails
      include(USER_COLLEC_PATH . '/include/display_thumbnails.inc.php');
      
      // add username in title
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

// modification on mainpage_categories.tpl
function user_collections_categories_list($content, &$samrty)
{
  $search[0] = '<div class="thumbnailCategory">';
  $replace[0] = '<div class="thumbnailCategory {*if $cat.active}activeCollection{/if*}">
  <div class="collectionActions">
    <a href="{$cat.URL}" rel="nofollow">{"Edit"|@translate}</a>
    | <a href="{$cat.U_DELETE}" onClick="return confirm(\'{"Are you sure?"|@translate}\');" rel="nofollow">{"Delete"|@translate}</a>
    {if $cat.U_ACTIVE}| <a href="{$cat.U_ACTIVE}" rel="nofollow">{if $cat.active}{"set inactive"|@translate}{else}{"set active"|@translate}{/if}</a>{/if}
  </div>';
  
  // $search[1] = '<a href="{$cat.URL}">{$cat.NAME}</a>';
  // $replace[1] = '<a href="{$cat.URL}">{$cat.NAME}</a> {if $cat.active}<span class="collectionActive">({"active"|@translate})</span>{/if}';
  
  return str_replace($search, $replace, $content);
}

// colorbox
function user_collections_add_colorbox($content)
{
  // add datas
  $search = '<a href="{$thumbnail.URL}"';
  $replace = $search.' class="preview-box" data-src="{$thumbnail.FILE_SRC}" data-id="{$thumbnail.id}"';
  
  // colorbox script
  $content.= file_get_contents(USER_COLLEC_PATH.'template/thumbnails_colorbox.tpl');
  
  return str_replace($search, $replace, $content);
}

?>