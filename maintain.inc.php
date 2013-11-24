<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

class UserCollections_maintain extends PluginMaintain
{
  private $installed = false;

  function install($plugin_version, &$errors=array())
  {
    global $conf, $prefixeTable;

    if (empty($conf['user_collections']))
    {
      $conf['user_collections'] = serialize(array(
        'allow_mails' => true,
        'allow_public' => true,
        ));

      conf_update_param('user_collections', $conf['user_collections']);
    }

    // create tables
    $query = '
CREATE TABLE IF NOT EXISTS `'.$prefixeTable.'collections` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `user_id` smallint(5) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `date_creation` datetime NOT NULL,
  `comment` text NULL,
  `nb_images` mediumint(8) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
;';
    pwg_query($query);

    $query = '
CREATE TABLE IF NOT EXISTS `'.$prefixeTable.'collection_images` (
  `col_id` mediumint(8) NOT NULL,
  `image_id` mediumint(8) NOT NULL,
  `add_date` datetime NULL,
  UNIQUE KEY `UNIQUE` (`col_id`,`image_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
;';
    pwg_query($query);

    $query = '
CREATE TABLE IF NOT EXISTS `'.$prefixeTable.'collection_shares` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `col_id` mediumint(8) NOT NULL,
  `share_key` varchar(64) NOT NULL,
  `params` text NULL,
  `add_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `share_key` (`share_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
;';
    pwg_query($query);


    // version 2.0.0
    $result = pwg_query('SHOW COLUMNS FROM `'.$prefixeTable.'collection_images` LIKE "add_date";');
    if (!pwg_db_num_rows($result))
    {
      pwg_query('ALTER TABLE `'.$prefixeTable.'collection_images` ADD `add_date` datetime NULL;');
    }

    $result = pwg_query('SHOW COLUMNS FROM `'.$prefixeTable.'collections` LIKE "comment";');
    if (!pwg_db_num_rows($result))
    {
      pwg_query('ALTER TABLE `'.$prefixeTable.'collections` ADD `comment` text NULL;');
      pwg_query('ALTER TABLE `'.$prefixeTable.'collections` DROP `active`;');
    }

    // version 2.1.0
    $result = pwg_query('SHOW COLUMNS FROM `'.$prefixeTable.'collections` LIKE "public";');
    if (pwg_db_num_rows($result))
    {
      $now = date('Y-m-d H:i:s');

      $query = '
SELECT id, public_id
  FROM `'.$prefixeTable.'collections`
  WHERE public = 1
;';
      $result = pwg_query($query);

      $inserts = array();
      while ($row = pwg_db_fetch_assoc($result))
      {
        $inserts[] = array(
          'col_id' => $row['id'],
          'share_key' => $row['public_id'],
          'params' => serialize(array('password'=>'','deadline'=>'')),
          'add_date' => $now,
          );
      }

      mass_inserts($prefixeTable.'collection_shares',
        array('col_id','share_key','params','add_date'),
        $inserts
        );

      pwg_query('ALTER TABLE `'.$prefixeTable.'collections` DROP `public`, DROP `public_id`;');
    }
  }

  function activate($plugin_version, &$errors=array())
  {
    if (!$this->installed)
    {
      $this->install($plugin_version, $errors);
    }
  }

  function deactivate(){}

  function uninstall()
  {
    global $prefixeTable;

    conf_delete_param('user_collections');

    pwg_query('DROP TABLE IF EXISTS `'.$prefixeTable.'collections`;');
    pwg_query('DROP TABLE IF EXISTS `'.$prefixeTable.'collection_images`;');
    pwg_query('DROP TABLE IF EXISTS `'.$prefixeTable.'collection_shares`;');
  }
}
