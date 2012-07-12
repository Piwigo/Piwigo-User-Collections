<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

function plugin_install() 
{
  global $conf, $prefixeTable;
  
  $query = '
CREATE TABLE IF NOT EXISTS `'.$prefixeTable.'collections` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `user_id` smallint(5) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `date_creation` datetime NOT NULL,
  `nb_images` mediumint(8) NOT NULL DEFAULT 0,
  `active` tinyint(1) DEFAULT 0,
  `public` tinyint(1) DEFAULT 0,
  `public_id` varchar(10) NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
;';
  pwg_query($query);
  
  $query = '
CREATE TABLE IF NOT EXISTS `'.$prefixeTable.'collection_images` (
  `col_id` mediumint(8) NOT NULL,
  `image_id` mediumint(8) NOT NULL,
  UNIQUE KEY `UNIQUE` (`col_id`,`image_id`)
) DEFAULT CHARSET=utf8
;';
  pwg_query($query);
}

function plugin_activate()
{
  global $prefixeTable;
  
  // new collumn in beta2
  $query = 'SHOW COLUMNS FROM `'.$prefixeTable.'collections`;';
  $columns = array_from_query($query, 'Field');
  if (!in_array('public_id', $columns))
  {
    pwg_query('ALTER TABLE `'.$prefixeTable.'collections` ADD `public_id` varchar(10) NULL;');
  }
}

function plugin_uninstall() 
{
  global $prefixeTable;
  
  pwg_query('DELETE FROM `'. CONFIG_TABLE .'` WHERE param = "user_collections" LIMIT 1;');
  pwg_query('DROP TABLE IF EXISTS `'.$prefixeTable.'collections`;');
  pwg_query('DROP TABLE IF EXISTS `'.$prefixeTable.'collection_images`;');
}

?>