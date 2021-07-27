{combine_css path=$USER_COLLEC_PATH|cat:'template/style_collections.css'}

{combine_script id='uc_collection_common' require='jquery' load='footer' path='plugins/UserCollections/template/js/collectionCommon.js'}
{combine_script id='uc_collection_add' require='jquery' load='footer' path='plugins/UserCollections/template/js/collectionAdd.js'}

{combine_script id='jquery.confirm' load='footer' require='jquery' path='themes/default/js/plugins/jquery-confirm.min.js'}
{combine_css path="themes/default/js/plugins/jquery-confirm.min.css"}
{combine_css path=$USER_COLLEC_PATH|cat:'template/fontello/css/fontello.css'}


<p style="text-align:left;font-weight:bold;margin:20px;">
  <a href="{$U_CREATE}" 
    title="{'Create a new collection'|translate}" 
    class="new_col" 
    data-label='{'Collection name'|translate}'
    data-btn-validate='{'Add'|translate}'
    data-btn-cancel='{'Cancel'|translate}'
  >
  <i class="uc-icon-plus"></i>
  {'Create a new collection'|translate}
  </a>
</p>

{if empty($CATEGORIES)}
{'You have no collection'|translate}
{/if}