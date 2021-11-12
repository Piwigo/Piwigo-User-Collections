<?php
/*
Plugin Name: User Collections
Version: auto
Description: Registered users can select pictures from the gallery and save them into collections, like advanced favorites.
Plugin URI: auto
Author: Mistic
Author URI: http://www.strangeplanet.fr
Has Settings: true
*/

defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

if (basename(dirname(__FILE__)) != 'UserCollections')
{
  add_event_handler('init', 'user_collections_error');
  function user_collections_error()
  {
    global $page;
    $page['errors'][] = 'User Collections folder name is incorrect, uninstall the plugin and rename it to "UserCollections"';
  }
  return;
}

global $conf, $prefixeTable;

define('USER_COLLEC_PATH',   PHPWG_PLUGINS_PATH . 'UserCollections/');
define('USER_COLLEC_ADMIN',  get_root_url() . 'admin.php?page=plugin-UserCollections');
define('USER_COLLEC_PUBLIC', get_absolute_root_url() . make_index_url(array('section' => 'collections')) . '/');
define('COLLECTIONS_TABLE',       $prefixeTable.'collections');
define('COLLECTION_IMAGES_TABLE', $prefixeTable.'collection_images');
define('COLLECTION_SHARES_TABLE', $prefixeTable.'collection_shares');

add_event_handler('init', 'user_collections_init');


/**
 * update plugin & load language
 */
function user_collections_init()
{
  if (mobile_theme())
  {
    return;
  }

  global $template;
  $template->set_template_dir(USER_COLLEC_PATH.'template/');

  load_language('plugin.lang', USER_COLLEC_PATH);
  load_language('lang', PHPWG_ROOT_PATH.PWG_LOCAL_DIR, array('no_fallback'=>true, 'local'=>true) );

  global $conf;
  $conf['user_collections'] = safe_unserialize($conf['user_collections']);
  
  require_once(USER_COLLEC_PATH . 'include/ws_functions.inc.php');
  require_once(USER_COLLEC_PATH . 'include/functions.inc.php');
  require_once(USER_COLLEC_PATH . 'include/UserCollection.class.php');
  require_once(USER_COLLEC_PATH . 'include/events.inc.php');
  
  add_event_handler('ws_add_methods', 'user_collections_ws_add_methods');

  if (!defined('IN_ADMIN'))
  {
    // collections page
    add_event_handler('loc_end_section_init', 'user_collections_section_init');
    add_event_handler('loc_end_index', 'user_collections_page', EVENT_HANDLER_PRIORITY_NEUTRAL-10);

    // thumbnails actions
    add_event_handler('loc_end_index_thumbnails', 'user_collections_thumbnails_list', EVENT_HANDLER_PRIORITY_NEUTRAL-10, 2);

    // picture action
    add_event_handler('loc_end_picture', 'user_collections_picture_page');
  }

  // menu
  add_event_handler('blockmanager_register_blocks', 'user_collections_add_menublock');
  add_event_handler('blockmanager_apply', 'user_collections_applymenu');
}
