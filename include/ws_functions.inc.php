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
      'comment' => array('default' => null),
      'user_id' => array(
        'type'=>WS_TYPE_ID,
        'default' => null,
        'info'=>'Admin parameter, default is current user'
        ),
      ),
    'Create a new User Collection.'
    );

  $service->addMethod(
    'pwg.collections.delete',
    'ws_collections_delete',
    array(
      'col_id' => array(
        'type'=>WS_TYPE_ID,
        'info'=>'The current user must be admin or owner of the collection'
        ),
      ),
    'Delete a User Collection.'
    );

  $service->addMethod(
    'pwg.collections.getList',
    'ws_collections_getList',
    array(
      'user_id' => array(
        'type'=>WS_TYPE_ID,
        'default' => null,
        'info'=>'Admin parameter, default is current user'
      ),
      'name' => array('default' => null),
      'per_page' => array(
        'type'=>WS_TYPE_INT, 
        'default'=>min(100,ceil($conf['ws_max_images_per_page']/10)),
        'maxValue'=>ceil($conf['ws_max_images_per_page']/10)
      ),
      'page' => array(
        'type'=>WS_TYPE_INT,
        'default'=>0
        ),
      'order' => array('default'=>'username ASC, name ASC'),
      ),
    'Returns a list of collections.'
    );

  $service->addMethod(
    'pwg.collections.addImages',
    'ws_collections_addImages',
    array(
      'col_id' => array(
        'type'=>WS_TYPE_ID,
        'info'=>'The current user must be admin or owner of the collection'
        ),
      'image_ids' => array(
        'type'=>WS_TYPE_ID,
        'flags'=>WS_PARAM_FORCE_ARRAY
        ),
      ),
    'Add images to a collection.'
    );

  $service->addMethod(
    'pwg.collections.removeImages',
    'ws_collections_removeImages',
    array(
      'col_id' => array(
        'type'=>WS_TYPE_ID,
        'info'=>'The current user must be admin or owner of the collection'
        ),
      'image_ids' => array(
        'type'=>WS_TYPE_ID,
        'flags'=>WS_PARAM_FORCE_ARRAY
        ),
      ),
    'Remove images from a collection.'
    );

  $service->addMethod(
    'pwg.collections.addAlbum',
    'ws_collections_addAlbum',
    array(
      'col_id' => array(
        'type'=>WS_TYPE_ID,
        'info'=>'The current user must be admin or owner of the collection'
        ),
      'album_id' => array('type'=>WS_TYPE_ID),
      ),
    'Add all images of an album to a collection.'
    );

  $service->addMethod(
    'pwg.collections.getImages',
    'ws_collections_getImages',
    array(
      'col_id' => array('type'=>WS_TYPE_ID),
      'per_page' => array(
        'type'=>WS_TYPE_INT,
        'default'=>min(100,$conf['ws_max_images_per_page']),
        'maxValue'=>$conf['ws_max_images_per_page']
        ),
      'page' => array(
        'type'=>WS_TYPE_INT,
        'default'=>0
        ),
      'order' => array('default'=>null),
      ),
    'Returns elements for the corresponding  collection.'
    );

  $service->addMethod(
    'pwg.collections.getSerialized',
    'ws_collections_getSerialized',
    array(
      'col_id' => array('type'=>WS_TYPE_ID),
      'content' => array(
        'default'=>array('id','name','url','path'),
        'flags'=>WS_PARAM_FORCE_ARRAY,
        'info'=>'Available options are: id, file, name, url, path, date_creation, collection_add_date, filesize, width, height'
        ),
      ),
    'Returns a serialized version of the collection in CSV.<br>The return type is plain/text whatever you select as response format.'
    );

  $service->addMethod(
    'pwg.collections.getInfo',
    'ws_collections_getInfo',
    array(
      'col_id' => array(
        'type'=>WS_TYPE_ID,
        'info'=>'The current user must be admin or owner of the collection'
        ),
      ),
    'Returns basic info about a collection.'
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

  $collection = new UserCollection('new', $params['name'], $params['comment'], $params['user_id']);

  return array_change_key_case($collection->getCollectionInfo(), CASE_LOWER);
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

  try {
    $collection = new UserCollection($params['col_id']);
    $collection->checkUser();

    $collection->delete();
  }
  catch (Exception $e)
  {
    return new PwgError($e->getCode(), $e->getMessage());
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

  // search
  $where_clauses = array('1=1');
  if (!empty($params['user_id']))
  {
    $where_clauses[] = 'user_id = '.$params['user_id'];
  }
  if (!empty($params['name']))
  {
    $where_clauses[] = 'name LIKE("%'.pwg_db_real_escape_string($params['name']).'%")';
  }

  $order_by = !empty($params['order']) ? $params['order'] : 'username ASC, name ASC';

  $query = '
SELECT
    c.*,
    u.'.$conf['user_fields']['username'].' AS username
  FROM '.COLLECTIONS_TABLE.' AS c
    INNER JOIN '.USERS_TABLE.' AS u
    ON c.user_id = u.'.$conf['user_fields']['id'].'
  WHERE
    '.implode("\n    AND ", $where_clauses).'
  ORDER BY '.$order_by.'
  LIMIT '.(int)$params['per_page'].' OFFSET '.(int)($params['per_page']*$params['page']).'
;';
  $sets = hash_from_query($query, 'id');

  $data = array();
  foreach ($sets as $row)
  {
    $data[] = array(
      'id' => $row['id'],
      'name' => $row['name'],
      'comment' => $row['comment'],
      'nb_images' => $row['nb_images'],
      'date_creation' => $row['date_creation'],
      'is_temp' => $row['name'] == 'temp',
      'user_id' => $row['user_id'],
      'username' => $row['username'],
      );
  }

  return array(
    'paging' => new PwgNamedStruct(array(
        'page' => $params['page'],
        'per_page' => $params['per_page'],
        'count' => count($data)
        )),
    'collections' => new PwgNamedArray(
      $data,
      'collection'
      )
    );

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

  try {
    $collection = new UserCollection($params['col_id']);
    $collection->checkUser();

    $collection->addImages($params['image_ids']);

    return array('nb_images' => $collection->getParam('nb_images'));
  }
  catch (Exception $e)
  {
    return new PwgError($e->getCode(), $e->getMessage());
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

  try {
    $collection = new UserCollection($params['col_id']);
    $collection->checkUser();

    $collection->removeImages($params['image_ids']);

    return array('nb_images' => $collection->getParam('nb_images'));
  }
  catch (Exception $e)
  {
    return new PwgError($e->getCode(), $e->getMessage());
  }
}

/**
 * add album to a collection
 */
function ws_collections_addAlbum($params, &$service)
{
  global $conf, $user;

  // check status
  if (is_a_guest() && !$conf['UserCollections']['allow_add_albums'])
  {
    return new PwgError(403, 'Forbidden');
  }

  try {
    $collection = new UserCollection($params['col_id']);
    $collection->checkUser();
    
    $query = '
SELECT DISTINCT(image_id)
  FROM '.IMAGE_CATEGORY_TABLE.'
    INNER JOIN '.IMAGES_TABLE.' ON id = image_id
  WHERE
    category_id = '.$params['album_id'].'
'.get_sql_condition_FandF(
      array(
        'forbidden_categories' => 'category_id',
        'visible_categories' => 'category_id',
        'visible_images' => 'id'
        ),
      'AND'
).'
;';

    $images = query2array($query, null, 'image_id');
    $collection->addImages($images);

    return array('nb_images' => $collection->getParam('nb_images'));
  }
  catch (Exception $e)
  {
    return new PwgError($e->getCode(), $e->getMessage());
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

  try {
    $collection = new UserCollection($params['col_id']);
    $collection->checkUser();

    $image_ids = $collection->getImages();
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
  LIMIT '.(int)$params['per_page'].' OFFSET '.(int)($params['per_page']*$params['page']).'
;';

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

    return array(
      'paging' => new PwgNamedStruct(array(
          'page' => $params['page'],
          'per_page' => $params['per_page'],
          'count' => count($images)
          )),
      'images' => new PwgNamedArray(
        $images,
        'image',
        ws_std_get_image_xml_attributes()
        )
      );
  }
  catch (Exception $e)
  {
    return new PwgError($e->getCode(), $e->getMessage());
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

  try {
    $collection = new UserCollection($params['col_id']);
    $collection->checkUser();

    // change encoder to plain text
    include_once(USER_COLLEC_PATH.'include/plain_encoder.php');
    $encoder = new PwgPlainEncoder();
    $service->setEncoder('plain', $encoder);

    return $collection->serialize($params['content']);
  }
  catch (Exception $e)
  {
    return new PwgError($e->getCode(), $e->getMessage());
  }
}

/**
 * get info about a collection
 */
function ws_collections_getInfo($params, &$service)
{
  global $conf, $user;

  // check status
  if (is_a_guest())
  {
    return new PwgError(403, 'Forbidden');
  }

  try {
    $collection = new UserCollection($params['col_id']);
    $collection->checkUser();

    return array_change_key_case($collection->getCollectionInfo(), CASE_LOWER);
  }
  catch (Exception $e)
  {
    return new PwgError($e->getCode(), $e->getMessage());
  }
}
