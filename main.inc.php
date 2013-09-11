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

defined('USER_COLLEC_ID') or define('USER_COLLEC_ID', basename(dirname(__FILE__)));
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
  add_event_handler('loc_end_index_thumbnails', 'uc_anti_lightbox', 41);

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
  global $pwg_loaded_plugins, $conf;
  
  if (
    USER_COLLEC_VERSION == 'auto' or
    $pwg_loaded_plugins[USER_COLLEC_ID]['version'] == 'auto' or
    version_compare($pwg_loaded_plugins[USER_COLLEC_ID]['version'], USER_COLLEC_VERSION, '<')
  )
  {
    include_once(USER_COLLEC_PATH . 'include/install.inc.php');
    user_collections_install();
    
    if ( $pwg_loaded_plugins[USER_COLLEC_ID]['version'] != 'auto' and USER_COLLEC_VERSION != 'auto' )
    {
      $query = '
UPDATE '. PLUGINS_TABLE .'
SET version = "'. USER_COLLEC_VERSION .'"
WHERE id = "'. USER_COLLEC_ID .'"';
      pwg_query($query);
      
      $pwg_loaded_plugins[USER_COLLEC_ID]['version'] = USER_COLLEC_VERSION;
      
      if (defined('IN_ADMIN'))
      {
        $_SESSION['page_infos'][] = 'UserCollections updated to version '. USER_COLLEC_VERSION;
      }
    }
  }
  
  load_language('plugin.lang', USER_COLLEC_PATH);
  
  $conf['user_collections'] = unserialize($conf['user_collections']);
}

/**
 * admin plugins menu
 */
function user_collections_admin_menu($menu) 
{
  array_push($menu, array(
    'NAME' => 'User Collections',
    'URL' => USER_COLLEC_ADMIN,
  ));
  return $menu;
}

?>