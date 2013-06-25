<?php
if (!defined('USER_COLLEC_PATH')) die('Hacking attempt!');

global $template, $page, $conf;


// tabsheet
include_once(PHPWG_ROOT_PATH.'admin/include/tabsheet.class.php');
$page['tab'] = (isset($_GET['tab'])) ? $_GET['tab'] : 'sets';
  
$tabsheet = new tabsheet();
$tabsheet->add('sets', l10n('Collections'), USER_COLLEC_ADMIN . '-sets');
if ($page['tab'] == 'export')
{
  if (isset($_GET['col_id']))
  {
    $tabsheet->add('export', l10n('Export image list'), USER_COLLEC_ADMIN . '-export&amp;col_id='.$_GET['col_id']);
  }
  else
  {
    $page['tab'] = 'sets';
  }
}
$tabsheet->add('config', l10n('Configuration'), USER_COLLEC_ADMIN . '-config');
$tabsheet->select($page['tab']);
$tabsheet->assign();

// include page
include(USER_COLLEC_PATH . 'admin/' . $page['tab'] . '.php');

// template
$template->assign(array(
  'USER_COLLEC_PATH' => USER_COLLEC_PATH,
  'USER_COLLEC_ADMIN' => USER_COLLEC_ADMIN,
  ));
  
$template->assign_var_from_handle('ADMIN_CONTENT', 'user_collections');

?>