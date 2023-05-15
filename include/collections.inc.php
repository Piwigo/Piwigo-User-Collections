<?php
defined('USER_COLLEC_PATH') or die('Hacking attempt!');

global $page, $template, $conf, $user;

$template->assign(array(
  'USER_COLLEC_PATH' => USER_COLLEC_PATH,
  'USER_COLLEC_ABS_PATH' => realpath(USER_COLLEC_PATH).'/',
  'USER_THEME' => $user['theme'],
  ));


switch ($page['sub_section'])
{
// +-----------------------------------------------------------------------+
// | Collections list                                                      |
// +-----------------------------------------------------------------------+
case 'list':
{
  if (is_a_guest())
  {
    access_denied();
  }

  $template->set_filename('uc_page', 'collections_list.tpl');
  

  $self_url = USER_COLLEC_PUBLIC . 'list';

  // actions
  if (isset($_GET['action']) and preg_match('#^([0-9]+)$#', $_GET['col_id']))
  {
    switch ($_GET['action'])
    {
      ## new collection ##
      case 'new':
      {
        if (empty($_GET['name']))
        {
          $page['errors'][] = l10n('Please give a name');
        }
        else
        {
          $collection = new UserCollection('new', $_GET['name']);

          if (isset($_GET['redirect']))
          {
            $redirect = USER_COLLEC_PUBLIC . 'edit/' . $collection->getParam('id');
          }
          else
          {
            $redirect = USER_COLLEC_PUBLIC;
          }
          redirect($redirect);
        }
        break;
      }

      ## delete collection ##
      case 'delete':
      {
        try {
          $collection = new UserCollection($_GET['col_id']);
          $collection->delete();
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

  $template->assign('U_CREATE',
    add_url_params(USER_COLLEC_PUBLIC, array('action'=>'new','col_id'=>'0'))
    );

  $template->set_prefilter('index_category_thumbnails', 'user_collections_categories_list');

  include(USER_COLLEC_PATH . '/include/display_collections.inc.php');

  break;
}

// +-----------------------------------------------------------------------+
// | Edit collection                                                       |
// +-----------------------------------------------------------------------+
case 'edit':
{
  // security
  if (empty($page['col_id']))
  {
    $_SESSION['page_errors'][] = l10n('Invalid collection');
    redirect(USER_COLLEC_PUBLIC);
  }

  $self_url = USER_COLLEC_PUBLIC . 'edit/' . $page['col_id'];

  $template->assign(array(
    'U_LIST' => USER_COLLEC_PUBLIC,
    'UC_IN_EDIT' => true,
    ));

  try {
    $collection = new UserCollection($page['col_id']);
    $collection->checkUser();

    // save properties
    if (isset($_POST['save_col']))
    {
      if (empty($_POST['name']))
      {
        $page['errors'][] = l10n('Please give a name');
      }
      else
      {
        $collection->updateParam('name', stripslashes($_POST['name']));
      }
      $collection->updateParam('comment', stripslashes(@$_POST['comment']));
    }

    // add key
    if ($conf['user_collections']['allow_public'])
    {
      if (isset($_POST['add_share']))
      {
        $share = array(
          'share_key' => trim($_POST['share_key']),
          'password' =>  isset($_POST['use_share_password']) ? trim($_POST['share_password']) : '',
          'deadline' =>  isset($_POST['use_share_deadline']) ? trim($_POST['share_deadline']) : '',
          );

        if (!verify_ephemeral_key(@$_POST['key']))
        {
          $result = array(l10n('Invalid key'));
        }
        else
        {
          $result = $collection->addShare($share);
        }
        if (is_array($result))
        {
          $share['errors'] = $result;
        }
        else
        {
          $share = array();
          $share['infos'][] = l10n('New share added: <a href="%s">%s</a>', $result, $result);
        }
        $share['open'] = true;
      }
      else if (isset($_GET['delete_share']))
      {
        if ($collection->deleteShare($_GET['delete_share']))
        {
          $share['infos'][] = l10n('Share deleted');
        }
        $share['open'] = true;
      }

      if (!isset($share['share_key']))
      {
        $share['share_key'] = get_random_key(16);
        $share['password'] =  null;
        $share['deadline'] =  null;
      }

      $template->assign('share', $share);
    }

    // send mail
    if (($conf['user_collections']['allow_public'] && $conf['user_collections']['allow_mails'])
        || $conf['user_collections']['allow_send_admin'])
    {
      if (isset($_POST['send_mail']))
      {
        $contact = array(
          'sender_email' =>     trim($_POST['sender_email']),
          'sender_name' =>      trim($_POST['sender_name']),
          'to' =>               $_POST['to'],
          'recipient_email' =>  trim($_POST['recipient_email']),
          'recipient_name' =>   trim($_POST['recipient_name']),
          'nb_images' =>        $_POST['nb_images'],
          'message' =>          $_POST['message'],
          );

        if (!verify_ephemeral_key(@$_POST['key']))
        {
          $result = array(l10n('Invalid key'));
        }
        else
        {
          $result = $collection->sendEmail($contact);
        }
        if (is_array($result))
        {
          $contact['errors'] = $result;
          $contact['open'] = true;
        }
        else
        {
          $contact = array();
          $page['infos'][] = l10n('E-mail sent successfully');
        }
      }

      if (!isset($contact['sender_email']))
      {
        $contact['sender_name'] =     $user['username'];
        $contact['sender_email'] =    $user['email'];
        $contact['recipient_name'] =  null;
        $contact['recipient_email'] = null;
        $contact['nb_images'] =       4;
        $contact['message'] =         null;
      }

      $template->assign('contact', $contact);
    }

    // clear
    if (isset($_GET['action']) && $_GET['action'] == 'clear')
    {
      $collection->clearImages();
    }


    // add remove item links
    $template->set_prefilter('index_thumbnails', 'user_collections_add_colorbox');
    
    // thumbnails
    include(USER_COLLEC_PATH . '/include/display_thumbnails.inc.php');


    // collection properties
    $infos = $collection->getCollectionInfo();
    $infos['DATE_CREATION'] = format_date($infos['DATE_CREATION'], true);
    $infos['SHARES'] = $collection->getShares();
    $template->assign('collection', $infos);


    // toolbar buttons
    if (!empty($page['items']))
    {
      if ($conf['user_collections']['allow_public'])
      {
        user_collections_add_button('share', 'U_SHARE',
          USER_COLLEC_PUBLIC . 'view/' . $page['col_id'] .'-'
          );
      }
      
      if (($conf['user_collections']['allow_public'] && $conf['user_collections']['allow_mails'])
        || $conf['user_collections']['allow_send_admin'])
      {
        user_collections_add_button('mail', 'U_MAIL', true);
      }

      user_collections_add_button('edit', 'U_COLL_EDIT', $self_url);

      user_collections_add_button('clear', 'U_CLEAR',
        add_url_params($self_url, array('action'=>'clear'))
        );
      
      user_collections_add_button('delete', 'U_DELETE',
        add_url_params(USER_COLLEC_PUBLIC, array('action'=>'delete','col_id'=>$page['col_id']))
        );
    } else {
      $template->assign('U_DELETE', add_url_params(USER_COLLEC_PUBLIC, array('action'=>'delete','col_id'=>$page['col_id'])));
    }
    

    $template->assign(array(
      'UC_TKEY' => get_ephemeral_key(3),
      'UC_CONFIG' => $conf['user_collections']
      ));

    // modify page title
    $template->concat('TITLE',
      $conf['level_separator'] . trigger_change('render_category_name', $infos['NAME'])
      );

    // render description
    $template->assign('CONTENT_DESCRIPTION',
      trigger_change('render_category_description', nl2br($infos['COMMENT']))
      );
  }
  catch (Exception $e)
  {
    $page['errors'][] = $e->getMessage();
  }

  $template->set_filename('uc_page', 'collection.tpl');

  $template->set_filename('uc_edit', 'collection_edit.tpl');
  $template->set_filename('uc_mail', 'collection_mail.tpl');
  $template->set_filename('uc_share', 'collection_share.tpl');
  $template->set_filename('uc_js', 'collection_js.tpl');

  $template->assign_var_from_handle('UC_EDIT', 'uc_edit');
  $template->assign_var_from_handle('UC_MAIL', 'uc_mail');
  $template->assign_var_from_handle('UC_SHARE', 'uc_share');
  $template->assign_var_from_handle('UC_JS', 'uc_js');

  break;
}

// +-----------------------------------------------------------------------+
// | View collection                                                       |
// +-----------------------------------------------------------------------+
case 'view':
{
  $page['col_key'] = $page['col_id'];

  if (!$conf['user_collections']['allow_public'])
  {
    page_forbidden('');
  }
  if (empty($page['col_key']))
  {
    bad_request('');
  }

  $query = '
SELECT col_id, params
  FROM '.COLLECTION_SHARES_TABLE.'
  WHERE share_key = "'.$page['col_key'].'"
;';
  $result = pwg_query($query);

  if (!pwg_db_num_rows($result))
  {
    page_not_found(l10n('Collection not found'));
  }

  list($page['col_id'], $share_params) = pwg_db_fetch_row($result);
  $share_params = unserialize($share_params);

  // deadline check
  if (!empty($share_params['deadline']) && strtotime($share_params['deadline'])<time())
  {
    page_not_found(l10n('This link expired'));
  }

  $self_url = USER_COLLEC_PUBLIC . 'view/' . $page['col_key'];

  $template->set_filename('uc_page', 'collection_view.tpl');

  try {
    $collection = new UserCollection($page['col_id']);
    $col = $collection->getCollectionInfo();

    $mode = 'view';

    // password check
    if (!empty($share_params['password']))
    {
      if (isset($_POST['uc_password']))
      {
        $hash = sha1($conf['secret_key'].$_POST['uc_password'].$page['col_key']);
        if ($hash == $share_params['password'])
        {
          pwg_set_session_var('uc_key_'.$page['col_key'], get_ephemeral_key(0, $share_params['password']));
        }
        else
        {
          $page['errors'][] = l10n('Invalid password!');
          $mode = 'password';
        }
      }
      else if (($var = pwg_get_session_var('uc_key_'.$page['col_key'])) !== null)
      {
        if (!verify_ephemeral_key($var, $share_params['password']))
        {
          pwg_unset_session_var('uc_key_'.$page['col_key']);
          $mode = 'password';
        }
      }
      else
      {
        $mode = 'password';
      }
    }

    if ($mode == 'view')
    {
      $template->set_prefilter('index_thumbnails', 'user_collections_add_colorbox');

      // thumbnails
      include(USER_COLLEC_PATH . '/include/display_thumbnails.inc.php');

      // render description
      $template->assign('CONTENT_DESCRIPTION',
        trigger_change('render_category_description', nl2br($col['COMMENT']))
        );
    }

    // add username in title
    include_once(PHPWG_ROOT_PATH.'admin/include/functions.php');

    $template->concat('TITLE',
      $conf['level_separator'] . trigger_change('render_category_name', $col['NAME']) .
      ' (' . l10n('by %s', get_username($collection->getParam('user_id'))) . ')'
      );

    $template->assign('UC_MODE', $mode);
  }
  catch (Exception $e)
  {
    access_denied();
  }

  break;
}
}

$template->assign_var_from_handle('CONTENT', 'uc_page');


// modification on mainpage_categories.tpl
function user_collections_categories_list($content)
{
  $search = '<div class="thumbnailCategory">';

  $replace = $search . file_get_contents(realpath(USER_COLLEC_PATH . 'template/thumbnail_collection.tpl'));

  return str_replace($search, $replace, $content);
}

// colorbox
function user_collections_add_colorbox($content)
{
  $search = '<a href="{$thumbnail.URL}"';
  $replace = $search.' class="preview-box" data-src="{$thumbnail.FILE_SRC}" data-id="{$thumbnail.id}"';

  return str_replace($search, $replace, $content);
}

// add special buttons
function user_collections_add_button($tpl_file, $tpl_var, $value)
{
  global $template;

  $template->assign($tpl_var, $value);
  $template->set_filename('uc_button_'.$tpl_file, 'button_user_collections_'. $tpl_file .'.tpl');
  $template->add_index_button($template->parse('uc_button_'.$tpl_file, true));
}