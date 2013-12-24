User Collections

AVAILABLE TEMPLATE VARIABLES
=====================================================================
  File: thumbnails.tpl (index_thumbnails)
  Trigger: loc_end_index_thumbnails
  See: include/events.inc.php (user_collections_thumbnails_list)
  
  $USER_COLLEC_PATH - path from root to UserCollections directory
  
  $COLLECTIONS - all collections of the current user
    * id
    * name
    * nb_images
    
  $thumbnails - The following attributes are added for each element
    * COLLECTIONS - comma separated ids of collections containing the element
    
---------------------------------------------------------------------
  File: thumbnails.tpl (index_thumbnails)
  Trigger: loc_end_index_thumbnails
  See: include/display_thumbnails.inc.php (user_collections_thumbnails_in_collection)
  
  Only applied when viewing thumbnails of a collection.
  
  $thumbnails - The following attributes are added for each element
    * FILE_SRC - direct link to large image
    
---------------------------------------------------------------------
  File: mainpage_categories.tpl (index_category_thumbnails)
  Trigger: loc_end_index
  See: include/diplay_collections.inc.php
  
  Only applied when viewing collections list.
  
  $category_thumbnails - The following attributes are added for each element
    * U_DELETE



PREFILTERS
=====================================================================
  File: thumbnails.tpl (index_thumbnails)
  Searches: (<li>|<li class="gthumb">)
  See: include/events.inc.php (user_collections_thumbnails_list_button)
  
  This prefilter tries to add the "Add to collection" button for every element.
  The button must have class="addCollection" and have data-id="{$thumbnail.id}" 
  and data-cols="[{$thumbnail.COLLECTIONS}]".
  
---------------------------------------------------------------------
  File: thumbnails.tpl (index_thumbnails)
  Searches: <a href="{$thumbnail.URL}"
  See: include/collections.inc.php (user_collections_add_colorbox)
  
  This prefilter tries to add metadata for Colorbox interactions on collections
  view and edit pages.
  
---------------------------------------------------------------------
  File: mainpage_categories.tpl (index_category_thumbnails)
  Searches: <div class="thumbnailCategory">
  See: include/collections.inc.php (user_collections_categories_list)
  
  This prefilter tries to add "Edit" and "Delete" links on categories list,
  which uses the same template as albums.
  