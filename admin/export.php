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


if (isset($_POST['download']))
{
  $content = $UserCollection->serialize($_POST['content']);
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

$template->set_filename('user_collections', dirname(__FILE__) . '/template/export.tpl');

?>