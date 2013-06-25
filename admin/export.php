<?php
if (!defined('USER_COLLEC_PATH')) die('Hacking attempt!');

try {
  $UserCollection = new UserCollection($_GET['col_id']);
  
  $template->assign('COL_ID', $_GET['col_id']);
}
catch (Exception $e)
{
  array_push($page['errors'], $e->getMessage());
}

// pwg_unset_session_var('uc_export_active_fields');
// pwg_unset_session_var('uc_export_inactive_fields');

if (isset($_POST['download']))
{
  pwg_set_session_var('uc_export_active_fields', $_POST['active']);
  pwg_set_session_var('uc_export_inactive_fields', $_POST['inactive']);
  
  $content = $UserCollection->serialize($_POST['active']);
  $filename = 'collection_'.$_GET['col_id'].'_'.date('Ymd-Hi').'.csv';
  
  header('Content-Type: application/force-download; name="'.$filename.'"');
  header('Content-Disposition: attachment; filename="'.$filename.'"');
  header('Content-Description: File Transfer');
  header('Content-Transfer-Encoding: binary');
  header('Content-Length: '.strlen($content).'');

  header('Cache-Control: no-cache, must-revalidate');
  header('Pragma: no-cache');
  header('Expires: 0');
  
  echo $content;
  exit;
}

$default_active_fields = array(
  'id',
  'name',
  'path',
  );
$default_inactive_fields = array(
  'file',
  'url',
  'date_creation',
  'collection_add_date',
  'filesize',
  'width',
  'height',
  );

$template->assign('active_fields', pwg_get_session_var('uc_export_active_fields', $default_active_fields));
$template->assign('inactive_fields', pwg_get_session_var('uc_export_inactive_fields', $default_inactive_fields));

$template->set_filename('user_collections', realpath(USER_COLLEC_PATH . 'admin/template/export.tpl'));

?>