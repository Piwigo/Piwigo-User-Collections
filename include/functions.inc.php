<?php
defined('USER_COLLEC_PATH') or die('Hacking attempt!');

function get_current_collection_id($create=true)
{
  global $user;
  
  // active in db
  $query = '
SELECT id
  FROM '.COLLECTIONS_TABLE.'
  WHERE
    active = 1
    AND user_id = '.$user['id'].'
;';
  $result = pwg_query($query);
  
  if (pwg_db_num_rows($result))
  {
    list($col_id) = pwg_db_fetch_row($result);
    return $col_id;
  }
  
  // new one
  if ($create)
  {
    $UserCollection = new UserCollection('new', array(), 'temp', 1);
    return $UserCollection->getParam('id');
  }
  
  return false;
}

?>