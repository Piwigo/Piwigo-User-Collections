<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

include_once(PHPWG_PLUGINS_PATH . 'UserCollections/include/install.inc.php');

function plugin_install() 
{
  user_collections_install();
  
  define('user_collections_installed', true);
}

function plugin_activate()
{
  if (!defined('user_collections_intalled'))
  {
    user_collections_install();
  }
}

function plugin_uninstall() 
{
  global $prefixeTable;
  
  pwg_query('DROP TABLE IF EXISTS `'.$prefixeTable.'collections`;');
  pwg_query('DROP TABLE IF EXISTS `'.$prefixeTable.'collection_images`;');
}

?>