<?php
defined('USER_COLLEC_PATH') or die('Hacking attempt!');

function user_collections_ws_add_methods($arr)
{
  $service = &$arr[0];
  global $conf;
  
  $service->addMethod(
    'pwg.collections.create',
    'ws_collections_create',
    array(
      'name' => array(),
      'user_id' => array('default' => null),
      'active' => array('default' => 0),
      'public' => array('default' => 0),
      ),
    'Create a new User Collection. If "user_id" is empty, the collection is created for the current user.'
    );
    
  $service->addMethod(
    'pwg.collections.delete',
    'ws_collections_delete',
    array(
      'col_id' => array(),
      ),
    'Delete a User Collection. The current user must be admin or owner of the collection.'
    );
    
  $service->addMethod(
    'pwg.collections.getList',
    'ws_collections_getList',
    array(
      'col_id' => array('default' => null),
      'user_id' => array('default' => null),
      'name' => array('default' => null),
      'public' => array('default' => null),
      'active' => array('default' => null),
      ),
    'Retrive a list of collections.'
    );
    
  $service->addMethod(
    'pwg.collections.addImages',
    'ws_collections_addImages',
    array(
      'col_id' => array(),
      'image_ids' => array('flags'=>WS_PARAM_FORCE_ARRAY),
      ),
    'Add images to a collection. The current user must be admin or owner of the collection.'
    );
    
  $service->addMethod(
    'pwg.collections.removeImages',
    'ws_collections_removeImages',
    array(
      'col_id' => array(),
      'image_ids' => array('flags'=>WS_PARAM_FORCE_ARRAY),
      ),
    'Remove images from a collection. The current user must be admin or owner of the collection.'
    );
    
  $service->addMethod(
    'pwg.collections.getImages',
    'ws_collections_getImages',
    array(
      'col_id' => array(),
      'per_page' => array('default'=>100, 'maxValue'=>$conf['ws_max_images_per_page']),
      'page' => array('default'=>0),
      'order' => array('default'=>null),
      ),
    'Returns elements for the corresponding  collection.'
    );
    
  $service->addMethod(
    'pwg.collections.getSerialized',
    'ws_collections_getSerialized',
    array(
      'col_id' => array(),
      'content' => array('default'=>array('id','name','url','path'), 'flags'=>WS_PARAM_FORCE_ARRAY),
      ),
    'Returns a serialized version of the collection in CSV.<br>Available options for "content" are : id, file, name, url, path.<br>The return type is plain/text whatever you select as response format.'
    );
}

/**
 * create a new collection
 */
function ws_collections_create($params, &$service)
{
  global $conf, $user;
  
  // check status
  if (is_a_guest())
  {
    return new PwgError(403, 'Forbidden');
  }
  
  // check name
  if (empty($params['name']))
  {
    return new PwgError(WS_ERR_MISSING_PARAM, 'Empty collection name');
  }
  
  // check user id
  if (!empty($params['user_id']))
  {
    if (!is_admin() and $params['user_id'] != $user['id'])
    {
      return new PwgError(403, 'Forbidden');
    }
    include_once(PHPWG_ROOT_PATH . 'admin/include/functions.php');
    if (get_username($params['user_id']) === false)
    {
      return new PwgError(WS_ERR_INVALID_PARAM, 'Invalid user id');
    }
  }
  else
  {
    $params['user_id'] = $user['id'];
  }
  
  // check public
  if ($params['public'] != 0 and $params['public'] != 1)
  {
    return new PwgError(WS_ERR_INVALID_PARAM, 'Invalid "public" value, 0 or 1.');
  }
  if (!$conf['user_collections']['allow_public'])
  {
    $params['public'] = 0;
  }
  
  // check active
  if ($params['active'] != 0 and $params['active'] != 1)
  {
    return new PwgError(WS_ERR_INVALID_PARAM, 'Invalid "active" value, 0 or 1.');
  }
  
  $UserCollection = new UserCollection('new', array(), $params['name'], $params['active'], $params['public'], $params['user_id']);
  
  $infos = array_change_key_case($UserCollection->getCollectionInfo(), CASE_LOWER);
  
  return $infos;
}

/**
 * delete a collection
 */
function ws_collections_delete($params, &$service)
{
  global $user;
  
  // check status
  if (is_a_guest())
  {
    return new PwgError(403, 'Forbidden');
  }
  
  // check collection id
  if (!preg_match('#^[0-9]+$#', $params['col_id']))
  {
    return new PwgError(WS_ERR_INVALID_PARAM, 'Invalid collection id');
  }
  
  $query = '
SELECT user_id
  FROM '.COLLECTIONS_TABLE.'
  WHERE id = '.$params['col_id'].'
;';
  $result = pwg_query($query);
  
  if (!pwg_db_num_rows($result))
  {
    return new PwgError(WS_ERR_INVALID_PARAM, 'Invalid collection id');
  }
  else
  {
    // check owner
    list($user_id) = pwg_db_fetch_row($result);
    
    if (!is_admin() and $user_id != $user['id'])
    {
      return new PwgError(403, 'Forbidden');
    }
    
    // delete
    $query = '
DELETE ci, c
  FROM '.COLLECTION_IMAGES_TABLE.' AS ci
    RIGHT JOIN '.COLLECTIONS_TABLE.' AS c 
    ON ci.col_id = c.id
  WHERE
    c.user_id = '.$user_id.'
    AND c.id = '.$params['col_id'].'
;';
    pwg_query($query);
  }
}

/**
 * get a list of collections
 */
function ws_collections_getList($params, &$service)
{
  global $user, $conf;
  
  // check status
  if (is_a_guest())
  {
    return new PwgError(403, 'Forbidden');
  }
  
  // check user_id
  if (!empty($params['user_id']))
  {
    if (!is_admin() and $params['user_id'] != $user['id'])
    {
      return new PwgError(403, 'Forbidden');
    }
    include_once(PHPWG_ROOT_PATH . 'admin/include/functions.php');
    if (get_username($params['user_id']) === false)
    {
      return new PwgError(WS_ERR_INVALID_PARAM, 'Invalid user id');
    }
  }
  else if (!is_admin())
  {
    $params['user_id'] = $user['id'];
  }
  
  // check collection id
  if ( !empty($params['col_id']) and !preg_match('#^[0-9]+$#', $params['col_id']) )
  {
    return new PwgError(WS_ERR_INVALID_PARAM, 'Invalid collection id');
  }
  
  // check public
  if ( !empty($params['public']) and $params['public'] != 0 and $params['public'] != 1 )
  {
    return new PwgError(WS_ERR_INVALID_PARAM, 'Invalid "public" value, 0 or 1.');
  }
  
  // check active
  if ( !empty($params['active']) and $params['active'] != 0 and $params['active'] != 1 )
  {
    return new PwgError(WS_ERR_INVALID_PARAM, 'Invalid "active" value, 0 or 1.');
  }
  
  // search
  $where_clauses = array('1=1');
  if (!empty($params['col_id']))
  {
    $where_clauses[] = 'c.id = '.$params['col_id'];
  }
  if (!empty($params['user_id']))
  {
    $where_clauses[] = 'user_id = '.$params['user_id'];
  }
  if (!empty($params['public']))
  {
    $where_clauses[] = 'public = '.$params['public'];
  }
  if (!empty($params['active']))
  {
    $where_clauses[] = 'active = '.$params['active'];
  }
  if (!empty($params['name']))
  {
    $where_clauses[] = 'name LIKE("%'.pwg_db_real_escape_string($params['name']).'%")';
  }
  
  $query = '
SELECT 
    c.*,
    u.'.$conf['user_fields']['username'].' AS username
  FROM '.COLLECTIONS_TABLE.' AS c
    INNER JOIN '.USERS_TABLE.' AS u
    ON c.user_id = u.'.$conf['user_fields']['id'].'
  WHERE
    '.implode("\n    AND ", $where_clauses).'
  ORDER BY username ASC, name ASC
;';
  $sets = hash_from_query($query, 'id');
  
  $ret = array();
  foreach ($sets as $row)
  {
    $ret[] = array(
      'id' => $row['id'],
      'name' => $row['name'],
      'nb_images' => $row['nb_images'],
      'active' => (bool)$row['active'],
      'public' => (bool)$row['public'],
      'date_creation' => $row['date_creation'],
      'is_temp' => $row['name'] == 'temp',
      'u_public' => USER_COLLEC_PUBLIC . 'view/'.$row['public_id'],
      'user_id' => $row['user_id'],
      'username' => $row['username'],
      );
  }
  
  return $ret;
}

/**
 * add images to a collection
 */
function ws_collections_addImages($params, &$service)
{
  global $conf, $user;
  
  // check status
  if (is_a_guest())
  {
    return new PwgError(403, 'Forbidden');
  }
  
  // check collection id
  if (!preg_match('#^[0-9]+$#', $params['col_id']))
  {
    return new PwgError(WS_ERR_INVALID_PARAM, 'Invalid collection id');
  }

  try {
    $UserCollection = new UserCollection($params['col_id']);
    
    $UserCollection->addImages($params['image_ids']);
    
    return array('nb_images' => $UserCollection->getParam('nb_images'));
  }
  catch (Exception $e)
  {
    return new PwgError(WS_ERR_INVALID_PARAM, 'Invalid collection id');
  }
}

/**
 * remove images from a collection
 */
function ws_collections_removeImages($params, &$service)
{
  global $conf, $user;
  
  // check status
  if (is_a_guest())
  {
    return new PwgError(403, 'Forbidden');
  }
  
  // check collection id
  if (!preg_match('#^[0-9]+$#', $params['col_id']))
  {
    return new PwgError(WS_ERR_INVALID_PARAM, 'Invalid collection id');
  }

  try {
    $UserCollection = new UserCollection($params['col_id']);
    
    $UserCollection->removeImages($params['image_ids']);
    
    return array('nb_images' => $UserCollection->getParam('nb_images'));
  }
  catch (Exception $e)
  {
    return new PwgError(WS_ERR_INVALID_PARAM, 'Invalid collection id');
  }
}

/**
 * get images from a collection
 */
function ws_collections_getImages($params, &$service)
{
  global $conf, $user;
  
  // check status
  if (is_a_guest())
  {
    return new PwgError(403, 'Forbidden');
  }
  
  // check collection id
  if (!preg_match('#^[0-9]+$#', $params['col_id']))
  {
    return new PwgError(WS_ERR_INVALID_PARAM, 'Invalid collection id');
  }

  try {
    $UserCollection = new UserCollection($params['col_id']);
    
    $image_ids = $UserCollection->getImages();
    $images = array();
    
    if (!empty($image_ids))
    {
      $where_clauses = array();
      $where_clauses[] = 'i.id IN ('.implode(',', $image_ids ).')';
      $where_clauses[] = get_sql_condition_FandF( array(
            'visible_images' => 'i.id'
          ), null, true
        );

      $order_by = ws_std_image_sql_order($params, 'i.');
      $order_by = empty($order_by) ? $conf['order_by'] : 'ORDER BY '.$order_by;

      $query = '
SELECT i.*
  FROM '.IMAGES_TABLE.' i
  WHERE 
    '. implode("\n AND ", $where_clauses).'
GROUP BY i.id
'.$order_by.'
LIMIT '.(int)$params['per_page'].' OFFSET '.(int)($params['per_page']*$params['page']);

    $result = pwg_query($query);
    while ($row = pwg_db_fetch_assoc($result))
    {
      $image = array();
      foreach (array('id', 'width', 'height', 'hit') as $k)
      {
        if (isset($row[$k]))
        {
          $image[$k] = (int)$row[$k];
        }
      }
      foreach (array('file', 'name', 'comment', 'date_creation', 'date_available') as $k)
      {
        $image[$k] = $row[$k];
      }
      $image = array_merge($image, ws_std_get_urls($row));
      
      array_push($images, $image);
    }
  }

  return array('images' => array(
    WS_XML_ATTRIBUTES => array(
      'page' => $params['page'],
      'per_page' => $params['per_page'],
      'count' => count($images)
      ),
    WS_XML_CONTENT => new PwgNamedArray(
      $images, 
      'image', 
      ws_std_get_image_xml_attributes()
      ),
    ));
  }
  catch (Exception $e)
  {
    return new PwgError(WS_ERR_INVALID_PARAM, 'Invalid collection id');
  }
}

/**
 * get serialised collection
 */
function ws_collections_getSerialized($params, &$service)
{
  global $conf, $user;
  
  // check status
  if (is_a_guest())
  {
    return new PwgError(403, 'Forbidden');
  }
  
  // check collection id
  if (!preg_match('#^[0-9]+$#', $params['col_id']))
  {
    return new PwgError(WS_ERR_INVALID_PARAM, 'Invalid collection id');
  }

  try {
    $UserCollection = new UserCollection($params['col_id']);
    
    // change encoder to plain text
    include_once(USER_COLLEC_PATH.'include/plain_encoder.php');
    $encoder = new PwgPlainEncoder();
    $service->setEncoder('plain', $encoder);
  
    return $UserCollection->serialize($params['content']);
  }
  catch (Exception $e)
  {
    return new PwgError(WS_ERR_INVALID_PARAM, 'Invalid collection id');
  }
}
?>