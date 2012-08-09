<?php
defined('USER_COLLEC_PATH') or die('Hacking attempt!');

class UserCollection
{
  private $data;
  private $images;
  
  /**
   * __construct
   * @param: mixed col id (##|'new'|'active')
   * @param: array images
   */
  function __construct($col_id, $images=array(), $name=null, $active=false, $public=false)
  {
    global $user;
    
    $this->data = array(
      'id' => 0,
      'user_id' => $user['id'],
      'name' => null,
      'date_creation' => '0000-00-00 00:00:00',
      'nb_images' => 0,
      'active' => false,
      'public' => false,
      'public_id' => null,
      );
    $this->images = array();
    
    // access from public id
    if ( strlen($col_id) == 10 and strpos($col_id, 'uc') === 0 )
    {
      $query = '
SELECT id
  FROM '.COLLECTIONS_TABLE.'
  WHERE public_id = "'.$col_id.'"
;';
      $result = pwg_query($query);
      
      if (!pwg_db_num_rows($result))
      {
        $col_id = 0;
      }
      else
      {
        list($col_id) = pwg_db_fetch_row($result);
      }
    }
    
    // load specific collection
    if (preg_match('#^[0-9]+$#', $col_id))
    {
      $query = '
SELECT
    id,
    user_id,
    name,
    date_creation,
    nb_images,
    active,
    public,
    public_id
  FROM '.COLLECTIONS_TABLE.'
  WHERE
    id = '.$col_id.'
    '.(!is_admin() ? 'AND (user_id = '.$this->data['user_id'].' OR public = 1)' : null).'
;';
      $result = pwg_query($query);
      
      if (pwg_db_num_rows($result))
      {
        $this->data = array_merge(
          $this->data,
          pwg_db_fetch_assoc($result)
          );
        
        // make sur all pictures of the collection exist
        $query = '
DELETE FROM '.COLLECTION_IMAGES_TABLE.'
  WHERE image_id NOT IN (
    SELECT id FROM '.IMAGES_TABLE.'
    )
;';
        pwg_query($query);
      
        $query = '
SELECT image_id
  FROM '.COLLECTION_IMAGES_TABLE.'
  WHERE col_id = '.$this->data['id'].'
;';
        $this->images = array_from_query($query, 'image_id');
        
        if ($this->data['nb_images'] != count($this->images))
        {
          $this->updateParam('nb_images', count($this->images));
        }
      }
      else
      {
        throw new Exception(l10n('Invalid collection'));
      }
    }
    // create a new collection
    else if ($col_id == 'new')
    {
      $this->data['name'] = $name;
      $this->data['active'] = $active;
      $this->data['public'] = $public;
      $this->data['public_id'] = 'uc'.hash('crc32', uniqid(serialize($this->data), true));
      
      $query = '
INSERT INTO '.COLLECTIONS_TABLE.'(
    user_id,
    name,
    date_creation,
    active,
    public,
    public_id
  ) 
  VALUES(
    '.$this->data['user_id'].',
    "'.$this->data['name'].'",
    NOW(),
    '.(int)$this->data['active'].',
    '.(int)$this->data['public'].',
    "'.$this->data['public_id'].'"
  )
;';
      pwg_query($query);
      $this->data['id'] = pwg_db_insert_id();
      
      $date = pwg_query('SELECT FROM_UNIXTIME(NOW());');
      list($this->data['date_creation']) = pwg_db_fetch_row($date);
      
      if (!empty($images))
      {
        $this->addImages($images);
      }
      
      // only one active collection allowed
      if ($this->data['active'])
      {
        $query = '
UPDATE '.COLLECTIONS_TABLE.'
  SET active = 0
  WHERE
    user_id = '.$this->data['user_id'].'
    AND id != '.$this->data['id'].'
;';
        pwg_query($query);
      }
    }
    else
    {
      trigger_error('UserCollection::__construct, invalid input parameter', E_USER_ERROR);
    }
  }
  
  /**
   * updateParam
   * @param: string param name
   * @param: mixed param value
   */
  function updateParam($name, $value)
  {
    $this->data[$name] = $value;
    pwg_query('UPDATE '.COLLECTIONS_TABLE.' SET '.$name.' = "'.$value.'" WHERE id = '.$this->data['id'].';');
  }
  
  /**
   * getParam
   * @param: string param name
   * @return: mixed param value
   */
  function getParam($name)
  {
    return $this->data[$name];
  }
  
  /**
   * getImages
   * @return: array
   */
  function getImages()
  {
    return $this->images;
  }
  
  /**
   * isInSet
   * @param: int image id
   * @return: bool
   */
  function isInSet($image_id)
  {
    return in_array($image_id, $this->images);
  }
  
  /**
   * removeImages
   * @param: array image ids
   */
  function removeImages($image_ids)
  {
    if (empty($image_ids) or !is_array($image_ids)) return;
    
    foreach ($image_ids as $image_id)
    {
      unset($this->images[ array_search($image_id, $this->images) ]);
    }
    
    $query = '
DELETE FROM '.COLLECTION_IMAGES_TABLE.'
  WHERE 
    col_id = '.$this->data['id'].'
    AND image_id IN('.implode(',', $image_ids).')
;';
    pwg_query($query);
    
    $this->updateParam('nb_images', count($this->images));
  }
  
  /**
   * addImages
   * @param: array image ids
   */
  function addImages($image_ids)
  {
    if (empty($image_ids) or !is_array($image_ids)) return;
    
    $image_ids = array_unique($image_ids);
    $inserts = array();
    
    foreach ($image_ids as $image_id)
    {
      if ($this->isInSet($image_id)) continue;
      
      array_push($this->images, $image_id);
      array_push($inserts, array('col_id'=>$this->data['id'], 'image_id'=>$image_id));
    }
    
    mass_inserts(
      COLLECTION_IMAGES_TABLE,
      array('col_id', 'image_id'),
      $inserts
      );
      
    $this->updateParam('nb_images', count($this->images));
  }
  
  /**
   * toggleImage
   * @param: int image id
   */
  function toggleImage($image_id)
  {
    if ($this->isInSet($image_id))
    {
      $this->removeImages(array($image_id));
    }
    else
    {
      $this->addImages(array($image_id));
    }
  }
  
  /**
   * clearImages
   */
  function clearImages()
  {
    $this->images = array();
    $this->updateParam('nb_images', 0);
    
    $query = '
DELETE FROM '.COLLECTION_IMAGES_TABLE.'
  WHERE col_id = '.$this->data['id'].'
;';
    pwg_query($query);
  }
  
  /**
   * getCollectionInfo
   * @return: array
   */
  function getCollectionInfo()
  {
    $set = array(
      'NAME' => $this->data['name'],
      'NB_IMAGES' => $this->data['nb_images'],
      'ACTIVE' => (bool)$this->data['active'],
      'PUBLIC' => (bool)$this->data['public'],
      'DATE_CREATION' => format_date($this->data['date_creation'], true),
      'U_PUBLIC' => USER_COLLEC_PUBLIC . 'view/'.$this->data['public_id'],
      'IS_TEMP' =>  $this->data['name'] == 'temp',
      );
    
    return $set;
  }
}

?>