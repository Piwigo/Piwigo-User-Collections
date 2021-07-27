{footer_script require='jquery'}

{* Pass data to JS*}

var uc_share_open = {if isset($share.open)}true{else}false{/if};

var uc_mail_open = {if isset($contact.open)}true{else}false{/if};

var uc_remove_action = true;

var user_theme = '{$USER_THEME}'; 

{* Pass HTML form *}

var uc_mail_form = `{$UC_MAIL}`;
var uc_share_form = `{$UC_SHARE}`;
var uc_edit_form = `{$UC_EDIT}`;

{* Language variable *}

var str_char_constraint = '{'The key must be at least 8 characters long'|translate|escape:javascript}';
var str_mail_title = '{'Send this collection by mail'|translate}';
var str_share_title = '{'Share this collection'|translate}';
var str_send = '{'Send'|translate}';
var str_cancel = '{'Cancel'|translate}';
var str_close = '{'Close'|translate}';
var str_add = '{'Add'|translate}';
var str_delete_col = '{'Delete this collection'|translate}';
var str_clear_col = '{'Clear this collection'|translate}';
var str_are_you_sure = '{'Are you sure?'|translate}';
var str_remove_from_col = '{'Remove from collection'|translate}';
var str_jump_to_photo = '{'jump to photo'|translate|escape:javascript}';
var str_save = '{'Save'|translate|escape:javascript}';
var str_edit_col = '{'Edit this collection'|translate|escape:javascript}';

{/footer_script}

{combine_script id='uc_collection_common' require='jquery' load='footer' path='plugins/UserCollections/template/js/collectionCommon.js'}

{if isset($U_MAIL)}
    {combine_script id='uc_collection_mail' require='jquery' load='footer' path='plugins/UserCollections/template/js/collectionMail.js'}
{/if}

{if isset($U_SHARE)}
    {combine_script id='uc_collection_share' require='jquery' load='footer' path='plugins/UserCollections/template/js/collectionShare.js'}
{/if}

{combine_script id='uc_collection_zoom' require='jquery' load='footer' path='plugins/UserCollections/template/js/collectionZoom.js'}

{combine_script id='jquery.confirm' load='footer' require='jquery' path='themes/default/js/plugins/jquery-confirm.min.js'}
{combine_css path="themes/default/js/plugins/jquery-confirm.min.css"}
{combine_script id='jquery.colorbox' load='footer' require='jquery' path='themes/default/js/plugins/jquery.colorbox.min.js'}
{combine_css id='colorbox' path='themes/default/js/plugins/colorbox/style2/colorbox.css'}