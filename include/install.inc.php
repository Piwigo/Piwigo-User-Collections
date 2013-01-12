<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

function user_collections_install() 
{
  global $conf, $prefixeTable;
  
  if (empty($conf['user_collections']))
  {
    $default_config = serialize(array(
      'allow_mails' => true,
      'allow_public' => true,
      ));
      
    conf_update_param('user_collections', $default_config);
    $conf['user_collections'] = $default_config;
  }
  
  // create tables
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
  `add_date` DATETIME NULL,
  UNIQUE KEY `UNIQUE` (`col_id`,`image_id`)
) DEFAULT CHARSET=utf8
;';
  pwg_query($query);
  
  $result = pwg_query('SHOW COLUMNS FROM `'.$prefixeTable.'collection_images` LIKE "add_date";');
  if (!pwg_db_num_rows($result))
  {
    pwg_query('ALTER TABLE `'.$prefixeTable.'collection_images` ADD `add_date` DATETIME NULL;');
  }
}

?>