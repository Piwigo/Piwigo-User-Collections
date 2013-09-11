<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

defined('USER_COLLEC_ID') or define('USER_COLLEC_ID', basename(dirname(__FILE__)));
include_once(PHPWG_PLUGINS_PATH . USER_COLLEC_ID . '/include/install.inc.php');

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
  global $prefixeTable, $conf;
  
  pwg_query('DELETE FROM `'. CONFIG_TABLE .'` WHERE param = "user_collections";');
  pwg_query('DROP TABLE IF EXISTS `'.$prefixeTable.'collections`;');
  pwg_query('DROP TABLE IF EXISTS `'.$prefixeTable.'collection_images`;');
  pwg_query('DROP TABLE IF EXISTS `'.$prefixeTable.'collection_shares`;');
  
  unset($conf['user_collections']);
}

?>