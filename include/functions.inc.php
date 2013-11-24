<?php
defined('USER_COLLEC_PATH') or die('Hacking attempt!');

function get_random_key($length=32)
{
  $chars = '0123456789abcdefabcdef';
  for ($s=''; strlen($s)<$length; )
  {
    $s.= $chars[rand(0, strlen($chars) - 1)];
  }
  return $s;
}

function get_collection_preferred_image_orders()
{
  global $conf;

  return trigger_change('get_category_preferred_image_orders', array(
    array(l10n('Date added to collection, new &rarr; old'), 'add_date DESC', true),
    array(l10n('Date added to collection, old &rarr; new'), 'add_date ASC',  true),
    array(l10n('Photo title, A &rarr; Z'),        'name ASC',             true),
    array(l10n('Photo title, Z &rarr; A'),        'name DESC',            true),
    array(l10n('Date created, new &rarr; old'),   'date_creation DESC',   true),
    array(l10n('Date created, old &rarr; new'),   'date_creation ASC',    true),
    array(l10n('Date posted, new &rarr; old'),    'date_available DESC',  true),
    array(l10n('Date posted, old &rarr; new'),    'date_available ASC',   true),
    array(l10n('Rating score, high &rarr; low'),  'rating_score DESC',    $conf['rate']),
    array(l10n('Rating score, low &rarr; high'),  'rating_score ASC',     $conf['rate']),
    array(l10n('Visits, high &rarr; low'),        'hit DESC',             true),
    array(l10n('Visits, low &rarr; high'),        'hit ASC',              true),
    ));
}

function get_collections_preferred_orders()
{
  return array(
    array(l10n('Name, A &rarr; Z'),               'name ASC',           true),
    array(l10n('Name, Z &rarr; A'),               'name DESC',          true),
    array(l10n('Date created, new &rarr; old'),   'date_creation DESC', true),
    array(l10n('Date created, old &rarr; new'),   'date_creation ASC',  true),
    array(l10n('Photos number, high &rarr; low'), 'nb_images DESC',     true),
    array(l10n('Photos number, low &rarr; high'), 'nb_images ASC',      true),
    );
}
