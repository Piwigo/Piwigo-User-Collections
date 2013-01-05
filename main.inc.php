<?php 
/*
Plugin Name: User Collections
Version: auto
Description: Registered users can select pictures from the gallery and save them into collections, like advanced favorites.
Plugin URI: http://piwigo.org/ext/extension_view.php?eid=615
Author: Mistic
Author URI: http://www.strangeplanet.fr
*/

defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

global $conf, $prefixeTable;

define('USER_COLLEC_PATH',       PHPWG_PLUGINS_PATH . 'UserCollections/');
define('COLLECTIONS_TABLE',      $prefixeTable.'collections');
define('COLLECTION_IMAGES_TABLE',$prefixeTable.'collection_images');
define('USER_COLLEC_ADMIN',      get_root_url() . 'admin.php?page=plugin-UserCollections');
define('USER_COLLEC_PUBLIC',     get_absolute_root_url() . make_index_url(array('section' => 'collections')) . '/');
define('USER_COLLEC_VERSION',    'auto');


add_event_handler('init', 'user_collections_init');

add_event_handler('loc_end_section_init', 'user_collections_section_init');
add_event_handler('loc_end_index', 'user_collections_page', EVENT_HANDLER_PRIORITY_NEUTRAL-10);

add_event_handler('loc_end_index', 'user_collections_index_actions');
add_event_handler('loc_end_index_thumbnails', 'user_collections_thumbnails_list', EVENT_HANDLER_PRIORITY_NEUTRAL, 2);

add_event_handler('loc_end_picture', 'user_collections_picture_page');

add_event_handler('blockmanager_register_blocks', 'user_collections_add_menublock');
add_event_handler('blockmanager_apply', 'user_collections_applymenu');

require(USER_COLLEC_PATH . 'include/functions.inc.php');
require(USER_COLLEC_PATH . 'include/UserCollection.class.php');
require(USER_COLLEC_PATH . 'include/events.inc.php');


/**
 * update plugin & load language
 */
function user_collections_init()
{
  global $pwg_loaded_plugins;
  
  if (
    USER_COLLEC_VERSION == 'auto' or
    $pwg_loaded_plugins['UserCollections']['version'] == 'auto' or
    version_compare($pwg_loaded_plugins['UserCollections']['version'], USER_COLLEC_VERSION, '<')
  )
  {
    include_once(USER_COLLEC_PATH . 'include/install.inc.php');
    user_collections_install();
    
    if ( $pwg_loaded_plugins['UserCollections']['version'] != 'auto' and USER_COLLEC_VERSION != 'auto' )
    {
      $query = '
UPDATE '. PLUGINS_TABLE .'
SET version = "'. USER_COLLEC_VERSION .'"
WHERE id = "UserCollections"';
      pwg_query($query);
      
      $pwg_loaded_plugins['UserCollections']['version'] = USER_COLLEC_VERSION;
      
      if (defined('IN_ADMIN'))
      {
        $_SESSION['page_infos'][] = 'UserCollections updated to version '. USER_COLLEC_VERSION;
      }
    }
  }
  
  load_language('plugin.lang', USER_COLLEC_PATH);
}

?>