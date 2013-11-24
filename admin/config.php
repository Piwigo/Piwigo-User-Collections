<?php
if (!defined('USER_COLLEC_PATH')) die('Hacking attempt!');

if (isset($_POST['save_config']))
{
  $conf['user_collections'] = array(
    'allow_public'    => isset($_POST['allow_public']),
    'allow_mails'     => isset($_POST['allow_mails']) && isset($_POST['allow_public']),
    );

  conf_update_param('user_collections', serialize($conf['user_collections']));
}

$template->assign(array(
  'user_collections' => $conf['user_collections'],
  ));


$template->set_filename('user_collections', realpath(USER_COLLEC_PATH . 'admin/template/config.tpl'));
