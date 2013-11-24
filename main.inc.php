<?php
/*
Plugin Name: User Collections
Version: auto
Description: Registered users can select pictures from the gallery and save them into collections, like advanced favorites.
Plugin URI: auto
Author: Mistic
Author URI: http://www.strangeplanet.fr
*/

defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

if (mobile_theme())
{
  return;
}

global $conf, $prefixeTable;

define('USER_COLLEC_ID',          basename(dirname(__FILE__)));
define('USER_COLLEC_PATH',        PHPWG_PLUGINS_PATH . USER_COLLEC_ID . '/');
define('COLLECTIONS_TABLE',       $prefixeTable.'collections');
define('COLLECTION_IMAGES_TABLE', $prefixeTable.'collection_images');
define('COLLECTION_SHARES_TABLE', $prefixeTable.'collection_shares');
define('USER_COLLEC_ADMIN',       get_root_url() . 'admin.php?page=plugin-' . USER_COLLEC_ID);
define('USER_COLLEC_PUBLIC',      get_absolute_root_url() . make_index_url(array('section' => 'collections')) . '/');
define('USER_COLLEC_VERSION',    'auto');

add_event_handler('init', 'user_collections_init');

add_event_handler('ws_add_methods', 'user_collections_ws_add_methods');

if (defined('IN_ADMIN'))
{
  add_event_handler('get_admin_plugin_menu_links', 'user_collections_admin_menu');
}
else
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

require_once(USER_COLLEC_PATH . 'include/ws_functions.inc.php');
require_once(USER_COLLEC_PATH . 'include/functions.inc.php');
require_once(USER_COLLEC_PATH . 'include/UserCollection.class.php');
require_once(USER_COLLEC_PATH . 'include/events.inc.php');


/**
 * update plugin & load language
 */
function user_collections_init()
{
  global $conf;

  include_once(USER_COLLEC_PATH . 'maintain.inc.php');
  $maintain = new UserCollections_maintain(USER_COLLEC_ID);
  $maintain->autoUpdate(USER_COLLEC_VERSION, 'install');

  load_language('plugin.lang', USER_COLLEC_PATH);

  $conf['user_collections'] = unserialize($conf['user_collections']);
}

/**
 * admin plugins menu
 */
function user_collections_admin_menu($menu)
{
  $menu[] = array(
    'NAME' => 'User Collections',
    'URL' => USER_COLLEC_ADMIN,
    );

  return $menu;
}
