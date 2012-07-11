<?php
define('PHPWG_ROOT_PATH', '../../');
include(PHPWG_ROOT_PATH.'include/common.inc.php');

check_status(ACCESS_CLASSIC);

if (isset($_POST['toggle_id']))
{
  try
  {
    $col_id = !empty($_POST['col_id']) ? $_POST['col_id'] : get_current_collection_id(true);
    $UserCollection = new UserCollection($col_id);
    $UserCollection->toggleImage($_POST['toggle_id']);
    echo boolean_to_string($UserCollection->isInSet($_POST['toggle_id']));
  }
  catch (Exception $e)
  {
    echo 'error';
  }
}
else
{
  echo 'error';
}

?>