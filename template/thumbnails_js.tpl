{combine_css path=$USER_COLLEC_PATH|cat:'template/style_thumbnails.css'}
{combine_css path=$USER_COLLEC_PATH|cat:'template/fontello/css/fontello.css'}

{footer_script}

{* Data *}

var collectionImage = {if isset($IMAGES_COLLECTIONS)}JSON.parse('{json_encode($IMAGES_COLLECTIONS)}'){else}null{/if};
var htmlThumbnailAction = {if isset($UC_THUMBNAIL_ACTION)}'{$UC_THUMBNAIL_ACTION}'{else}null{/if};
var rootUrl = '{$ROOT_URL}';

{* Language variable *}

var str_error = '{'An unknown error occured'|translate}';

{* Page variable *}

var editCollectionPage = {if isset($UC_IN_EDIT)}true{else}false{/if};
var collectionId = {if isset($UC_IN_EDIT)} {$collection.ID} {else}-1{/if};
var picturePage = {if isset($IN_PICTURE)}true{else}false{/if};

{* Theme variable *}

var thumbnailsActions = 'ul#thumbnails li';
var findThumbnailToHide = 'li';
var thumbnailAction = '#thumbnails .addCollection';
var collectionMenuNbImages = '.nbImagesCollec-%id%';
var collectionMenuTemplate = '.mbUCItem-coll_template'
var mbUserCollection = '#mbUserCollection ul';

{if ($USER_THEME=='bootstrap_darkroom') }

{if isset($GTHUMB_ACTIVE) && (!$GTHUMB_ACTIVE)}
thumbnailsActions = '#thumbnails .card';
findThumbnailToHide = '.col-outer';
thumbnailAction = '#thumbnails .addCollection';
{/if}
collectionMenuTemplate = '#menu-info-coll-coll_template';
collectionMenuNbImages = "#menu-info-coll-%id% .badge";
mbUserCollection = '#menu-info-coll-container';

{/if}

{/footer_script}

{combine_script id='uc_thumbnails' require='jquery' load='footer' path='plugins/UserCollections/template/js/thumbnails.js'}
{combine_css path="plugins/UserCollections/template/fontello/css/animation.css" order=10}