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

define('USER_COLLEC_PATH',       PHPWG_PLUGINS_PATH . basename(dirname(__FILE__)) . '/');
define('COLLECTIONS_TABLE',      $prefixeTable.'collections');
define('COLLECTION_IMAGES_TABLE',$prefixeTable.'collection_images');
define('USER_COLLEC_ADMIN',      get_root_url() . 'admin.php?page=plugin-' . basename(dirname(__FILE__)));
define('USER_COLLEC_PUBLIC',     get_absolute_root_url() . make_index_url(array('section' => 'collections')) . '/');

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

?>