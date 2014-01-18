<?php
if (!defined('USER_COLLEC_PATH')) die('Hacking attempt!');

// actions
if (isset($_GET['delete']))
{
  pwg_query('DELETE FROM '.COLLECTIONS_TABLE.' WHERE id = '.$_GET['delete'].';');
  pwg_query('DELETE FROM '.COLLECTION_IMAGES_TABLE.' WHERE col_id = '.$_GET['delete'].';');
}

// filter
$where_clauses = array('1=1');
$order_by = 'date_creation DESC, name ASC';

if (isset($_POST['filter']))
{
  if (!empty($_POST['username']))
  {
    array_push($where_clauses, 'username LIKE "%'.$_POST['username'].'%"');
  }

  if (!empty($_POST['name']))
  {
    array_push($where_clauses, 'name LIKE "%'.$_POST['name'].'%"');
  }

  $order_by = $_POST['order_by'].' '.$_POST['direction'];
}


// get sets
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
;';
$sets = hash_from_query($query, 'id');

foreach ($sets as $row)
{
  $template->append('sets', array(
    'NAME' => trigger_event('render_category_name', $row['name']),
    'NB_IMAGES' => $row['nb_images'],
    'DATE_CREATION' => format_date($row['date_creation'], true),
    'USERNAME' => $row['username'],
    'U_EDIT' => USER_COLLEC_PUBLIC . 'edit/'.$row['id'],
    'U_EXPORT' => USER_COLLEC_ADMIN . '-export&amp;col_id='.$row['id'],
    'U_DELETE' => USER_COLLEC_ADMIN . '-sets&amp;delete='.$row['id'],
    ));
}


// filter options
$page['order_by_items'] = array(
  'date_creation' => l10n('Creation date'),
  'nb_images' => l10n('Number of images'),
  );

$page['direction_items'] = array(
  'DESC' => l10n('descending'),
  'ASC' => l10n('ascending'),
  );


$template->assign(array(
  'order_options' => $page['order_by_items'],
  'order_selected' => isset($_POST['order_by']) ? $_POST['order_by'] : '',
  'direction_options' => $page['direction_items'],
  'direction_selected' => isset($_POST['direction']) ? $_POST['direction'] : '',

  'F_USERNAME' => @htmlentities($_POST['username'], ENT_COMPAT, 'UTF-8'),
  'F_NAME' => @htmlentities($_POST['name'], ENT_COMPAT, 'UTF-8'),
  'F_FILTER_ACTION' => USER_COLLEC_ADMIN . '-sets',
  ));


$template->set_filename('user_collections', realpath(USER_COLLEC_PATH . 'admin/template/sets.tpl'));
