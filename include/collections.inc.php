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
    
    $template->set_filename('index', realpath(USER_COLLEC_PATH.'template/collections_list.tpl'));
    
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
            $UserCollection = new UserCollection('new', $_GET['name']);
            
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
    
    $template->set_filename('index', realpath(USER_COLLEC_PATH.'template/collection_edit.tpl'));
    
    $self_url = USER_COLLEC_PUBLIC . 'edit/'.$page['col_id'];
    
    $template->assign(array(
      'user_collections' => $conf['user_collections'],
      'F_ACTION' => $self_url,
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
        $UserCollection->updateParam('comment', stripslashes($_POST['comment']));
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
        $conf['level_separator'].trigger_event('render_category_name', $col['NAME'])
        );
        
      $template->assign('CONTENT_DESCRIPTION', trigger_event('render_category_description', nl2br($col['COMMENT'])));
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
    
    $template->set_filename('index', realpath(USER_COLLEC_PATH.'template/collection_view.tpl'));
    
    $self_url = USER_COLLEC_PUBLIC . 'view/'.$page['col_id'];
    
    try {
      $UserCollection = new UserCollection($page['col_id']); // public id
      $page['col_id'] = $UserCollection->getParam('id'); // private id
      $col = $UserCollection->getCollectionInfo();
      
      $template->set_prefilter('index_thumbnails', 'user_collections_add_colorbox');
      
      // thumbnails
      include(USER_COLLEC_PATH . '/include/display_thumbnails.inc.php');
      
      // add username in title
      include_once(PHPWG_ROOT_PATH.'admin/include/functions.php');
      $template->concat('TITLE', 
        $conf['level_separator'] . trigger_event('render_category_name', $col['NAME']) .
        ' (' . sprintf(l10n('by %s'), get_username($UserCollection->getParam('user_id'))) . ')'
        );
        
      $template->assign('CONTENT_DESCRIPTION', trigger_event('render_category_description', nl2br($col['COMMENT'])));
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
  $search = '<div class="thumbnailCategory">';
  $replace = '<div class="thumbnailCategory">
  <div class="collectionActions">
    <a href="{$cat.URL}" rel="nofollow">{"Edit"|@translate}</a>
    | <a href="{$cat.U_DELETE}" onClick="return confirm(\'{"Are you sure?"|@translate}\');" rel="nofollow">{"Delete"|@translate}</a>
  </div>';
  
  return str_replace($search, $replace, $content);
}

// colorbox
function user_collections_add_colorbox($content)
{
  $search = '<a href="{$thumbnail.URL}"';
  $replace = $search.' class="preview-box" data-src="{$thumbnail.FILE_SRC}" data-id="{$thumbnail.id}"';
  
  return str_replace($search, $replace, $content);
}

?>