{strip}
{combine_css path=$USER_COLLEC_PATH|cat:'template/style_collections.css'}
{combine_css path=$USER_COLLEC_PATH|cat:'template/fontello/css/fontello.css'}

{*<!-- datepicker and timepicker -->*}
{combine_script id='jquery.ui.datepicker' load='footer' path='themes/default/js/ui/jquery.ui.datepicker.js'}
{combine_script id='jquery.ui.timepicker' load='footer' require='jquery.ui.slider' path=$USER_COLLEC_PATH|cat:'template/resources/jquery-timepicker/jquery-ui-timepicker-addon.js'}

{assign var=datepicker_language value='themes/default/js/ui/i18n/jquery.ui.datepicker-'|cat:$lang_info.code|cat:'.js'}
{if 'PHPWG_ROOT_PATH'|constant|cat:$datepicker_language|file_exists}
{combine_script id='jquery.ui.datepicker-'|cat:$lang_info.code load='footer' require='jquery.ui.datepicker' path=$datepicker_language}
{/if}

{assign var=timepicker_language value=$USER_COLLEC_PATH|cat:'template/resources/jquery-timepicker/i18n/jquery-ui-timepicker-'|cat:$lang_info.code|cat:'.js'}
{if 'PHPWG_ROOT_PATH'|constant|cat:$timepicker_language|file_exists}
{combine_script id='jquery.ui.timepicker-'|cat:$lang_info.code load='footer' require='jquery.ui.timepicker' path=$timepicker_language}
{/if}

{combine_script id='uc_collection_edit' require='jquery' load='footer' path='plugins/UserCollections/template/js/collectionEdit.js'}

{combine_css path='themes/default/js/ui/theme/jquery.ui.core.css'}
{combine_css path='themes/default/js/ui/theme/jquery.ui.theme.css'}
{combine_css path='themes/default/js/ui/theme/jquery.ui.datepicker.css'}
{combine_css path='themes/default/js/ui/theme/jquery.ui.slider.css'}
{combine_css path=$USER_COLLEC_PATH|cat:'template/resources/jquery-timepicker/jquery-ui-timepicker-addon.css'}
{/strip}

{$UC_JS}

{if empty($THUMBNAILS)}
<div class="uc-no-photo">
  <p>
    <a href="#edit_form" title="{'Edit'|translate}" class="edit_popin_open">
      <i class="uc-icon-pencil"></i>
      <span>{'Edit'|translate}</span>
    </a>
    {'or'|translate}
    <a title="{'Delete this collection'|translate}" 
      href="{$U_DELETE}"
      class="uc-confirm-link" 
      data-icon="uc-icon-trash" 
      data-validate="{'Delete'|translate}" 
      data-content="{'Are you sure?'|translate}" 
      data-cancel="{'Cancel'|translate}" 
      rel="nofollow"
    >
      <i class="uc-icon-trash"></i>
      <span>{'Delete'|translate}</span>
    </a>
  </p>

  <p>
    <i>{'This collection is empty'|translate}</i>
  </p>
</div>
{/if}
