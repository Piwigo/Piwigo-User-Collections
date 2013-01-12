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

function uc_check_email_validity($mail_address)
{
  if (function_exists('email_check_format'))
  {
    return email_check_format($mail_address); // Piwigo 2.5
  }
  else if (version_compare(PHP_VERSION, '5.2.0') >= 0)
  {
    return filter_var($mail_address, FILTER_VALIDATE_EMAIL)!==false;
  }
  else
  {
    $atom   = '[-a-z0-9!#$%&\'*+\\/=?^_`{|}~]';   // before  arobase
    $domain = '([a-z0-9]([-a-z0-9]*[a-z0-9]+)?)'; // domain name
    $regex = '/^' . $atom . '+' . '(\.' . $atom . '+)*' . '@' . '(' . $domain . '{1,63}\.)+' . $domain . '{2,63}$/i';

    return (bool)preg_match($regex, $mail_address);
  }
}

?>