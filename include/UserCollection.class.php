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
  function __construct($col_id, $images=array(), $name=null, $active=false, $public=false, $user_id=null)
  {
    global $user;
    
    if (empty($user_id)) {
      $user_id = $user['id'];
    }
    
    $this->data = array(
      'id' => 0,
      'user_id' => $user_id,
      'name' => null,
      'date_creation' => '0000-00-00 00:00:00',
      'nb_images' => 0,
      'active' => false,
      'public' => false,
      'public_id' => null,
      );
    $this->images = array();
    
    // access from public id (access permission is checked line 66)
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
      
        // select images of the collection
        $query = '
SELECT image_id
  FROM '.COLLECTION_IMAGES_TABLE.'
  WHERE col_id = '.$this->data['id'].'
;';
        $this->images = array_from_query($query, 'image_id');
        
        $this->updateParam('nb_images', count($this->images));
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
      
      $date = pwg_query('SELECT NOW();');
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
    if ($value != $this->data[$name])
    {
      $this->data[$name] = $value;
      pwg_query('UPDATE '.COLLECTIONS_TABLE.' SET '.$name.' = "'.pwg_db_real_escape_string($value).'" WHERE id = '.$this->data['id'].';');
    }
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
      
    $query = '
UPDATE '.COLLECTION_IMAGES_TABLE.'
  SET add_date = NOW()
  WHERE
    col_id = '.$this->data['id'].'
    AND image_id IN ('.implode(',', $image_ids).')
    AND add_date IS NULL
';
    pwg_query($query);
      
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
      'DATE_CREATION' => $this->data['date_creation'],
      'U_PUBLIC' => USER_COLLEC_PUBLIC . 'view/'.$this->data['public_id'],
      'IS_TEMP' =>  $this->data['name'] == 'temp',
      );
    
    return $set;
  }
  
  /**
   * Send the collection by email
   * @param: array
   *          - sender_name
   *          - sender_email
   *          - recipient_email
   *          - recipient_name
   *          - nb_images
   *          - message
   * @return: array errors
   */
  function sendEmail($comm, $key)
  {
    global $conf, $page, $template;
    
    $errors = array();
    
    $comm = array_map('stripslashes', $comm);

    $comment_action='validate';
    
    // check key
    if (!verify_ephemeral_key(@$key))
    {
      array_push($errors, l10n('Invalid or expired security key'));
      $comment_action='reject';
    }

    // check author
    if (empty($comm['sender_name']))
    {
      array_push($errors, l10n('Please enter your name'));
      $comment_action='reject';
    }      
    if (empty($comm['recipient_name']))
    {
      array_push($errors, l10n('Please enter the recipient name'));
      $comment_action='reject';
    }
    
    // check email
    if (empty($comm['sender_email']))
    {
      array_push($errors, l10n('Please enter your e-mail'));
      $comment_action='reject';
    }
    else if ( !empty($comm['sender_email']) and !uc_check_email_validity($comm['sender_email']) )
    {
      array_push($errors, l10n('mail address must be like xxx@yyy.eee (example : jack@altern.org)'));
      $comment_action='reject';
    }
    if (empty($comm['recipient_email']))
    {
      array_push($errors, l10n('Please enter the recipient e-mail'));
      $comment_action='reject';
    }
    else if ( !empty($comm['recipient_email']) and !uc_check_email_validity($comm['recipient_email']) )
    {
      array_push($errors, l10n('mail address must be like xxx@yyy.eee (example : jack@altern.org)'));
      $comment_action='reject';
    }
     
    // check content
    if (!empty($comm['message']))
    {
      $comm['message'] = nl2br($comm['message']);
    }
    
    include_once(PHPWG_ROOT_PATH.'include/functions_mail.inc.php');
    
    if ($comment_action == 'validate')
    {
      // format subject
      $subject = '['.$conf['gallery_title'].'] '.sprintf(l10n('A photo collection by %s'), $comm['sender_name']);
      $subject = encode_mime_header($subject);
            
      // format expeditor
      $args['from'] = format_email($comm['sender_name'], $comm['sender_email']);
      $args['to'] = format_email($comm['recipient_name'], $comm['recipient_email']);
      
      // hearders
      $headers = 'From: '.$args['from']."\n";  
      $headers.= 'MIME-Version: 1.0'."\n";
      $headers.= 'X-Mailer: Piwigo Mailer'."\n";
      $headers.= 'Content-Transfer-Encoding: 8bit'."\n";
      $headers.= 'Content-Type: text/html; charset="'.get_pwg_charset().'";'."\n";
            
      // mail content
      $content = $this->getMailContent($comm);
      $content = wordwrap($content, 70, "\n", true);
      
      // send mail
      $result =
        trigger_event('send_mail',
          false, /* Result */
          trigger_event('send_mail_to', $args['to']),
          trigger_event('send_mail_subject', $subject),
          trigger_event('send_mail_content', $content),
          trigger_event('send_mail_headers', $headers),
          $args
        );
      
      if ($result == false)
      {
        array_push($errors, l10n('Error while sending e-mail'));
      }
    }
    
    return $errors;
  }
  
  /**
   * get mail content for sendMail()
   */
  function getMailContent($params)
  {
    global $user, $conf, $template;
    
    // switch to guest user
    $user_save = $user;
    $user = build_user($conf['guest_id'], true);
    
    // get pictures
    $query = '
SELECT
    id,
    file,
    name,
    path
  FROM '.IMAGES_TABLE.' AS i
    JOIN '.IMAGE_CATEGORY_TABLE.' AS ci ON ci.image_id = i.id
  WHERE id IN ('.implode(',', $this->images).')
    '.get_sql_condition_FandF(array(
                'forbidden_categories' => 'category_id',
                'forbidden_images' => 'id'
                ),
              'AND'
              ).'
  GROUP BY i.id
  ORDER BY '.DB_RANDOM_FUNCTION.'()
  LIMIT '.$params['nb_images'].'
;';
    $pictures = hash_from_query($query, 'id');
    
    // switch back to current user
    $user = $user_save;
    unset($user_save);
  
    // picture sinfos
    set_make_full_url();
    $tpl_vars = array();
    foreach ($pictures as $row)
    {
      $name = render_element_name($row);
      
      $tpl_vars[] = array(
        'TN_ALT' => htmlspecialchars(strip_tags($name)),
        'NAME' => $name,
        'URL' => make_picture_url(array('image_id' => $row['id'])),
        'THUMB' => DerivativeImage::url(IMG_SQUARE, $row),
        );
    }
    
    // template
    $mail_css = file_get_contents(dirname(__FILE__).'/../template/mail.css');
    
    $template->assign(array(
      'GALLERY_URL' => get_gallery_home_url(),
      'PHPWG_URL' => PHPWG_URL,
      'UC_MAIL_CSS' => str_replace("\n", null, $mail_css),
      'MAIL_TITLE' => $this->getParam('name').' ('.sprintf(l10n('by %s'), $params['sender_name']).')',
      'COL_URL' => USER_COLLEC_PUBLIC . 'view/'.$this->data['public_id'],
      'PARAMS' => $params,
      'derivative_params' => ImageStdParams::get_by_type(IMG_SQUARE),
      'thumbnails' => $tpl_vars,
      ));
      
    $template->set_filename('uc_mail', dirname(__FILE__).'/../template/mail.tpl');
    $content = $template->parse('uc_mail', true);
  
    unset_make_full_url();
    
    return $content;
  }

  /**
   * generate a listing of the collection
   */
  function serialize($params)
  {
    $params = array_intersect($params, array('id','file','name','url','path'));
    
    $content = null;
     
    // get images infos
    $query = '
SELECT
    id,
    file,
    name,
    path
  FROM '.IMAGES_TABLE.'
  WHERE id IN('.implode(',', $this->images).')
  ORDER BY id
;';
    $pictures = hash_from_query($query, 'id');
    
    if (count($pictures))
    {
      // generate csv
      set_make_full_url();
      $root_url = get_root_url();
      
      $fp = fopen('php://temp', 'r+');
      fputcsv($fp, $params);
        
      foreach ($pictures as $row)
      {
        $element = array();
        foreach ($params as $field)
        {
          switch ($field)
          {
          case 'id':
            $element[] = $row['id']; break;
          case 'file':
            $element[] = $row['file']; break;
          case 'name':
            $element[] = render_element_name($row); break;
          case 'url':
            $element[] = make_picture_url(array('image_id'=>$row['id'], 'image_file'=>$row['file'])); break;
          case 'path':
            $element[] = $root_url.ltrim($row['path'], './'); break;
          }
        }
        if (!empty($element))
        {
          fputcsv($fp, $element);
        }
      }
      
      rewind($fp);
      $content = stream_get_contents($fp);
      fclose($fp);
      
      unset_make_full_url();
    }
    
    return $content;
  }
}

?>