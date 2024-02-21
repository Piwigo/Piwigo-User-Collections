{combine_css path=$USER_COLLEC_PATH|cat:'admin/template/style.css'}
{combine_css path=$USER_COLLEC_PATH|cat:'template/fontello/css/fontello.css'}
{combine_script id='uc_collection_common' require='jquery' load='footer' path='plugins/UserCollections/template/js/collectionCommon.js'}
{combine_script id='jquery.confirm' load='footer' require='jquery' path='themes/default/js/plugins/jquery-confirm.min.js'}
{combine_css path="themes/default/js/plugins/jquery-confirm.min.css"}

<div class="titrePage">
	<h2>User Collections</h2>
</div>

<form class="filter" method="post" name="filter" action="{$F_FILTER_ACTION}">
<fieldset>
  <legend>{'Filter'|translate}</legend>
  <input type="hidden" name="page" value="user_list">

  <label>{'Name'|translate} <input type="text" name="name" value="{$F_NAME}"></label>

  <label>{'Username'|translate} <input type="text" name="username" value="{$F_USERNAME}"></label>

  <label>
  {'Sort by'|translate}
  {html_options name=order_by options=$order_options selected=$order_selected}
  </label>

  <label>
  {'Sort order'|translate}
  {html_options name=direction options=$direction_options selected=$direction_selected}
  </label>

  <label>
  &nbsp;
  <span><input class="submit" type="submit" name="filter" value="{'Submit'|translate}"> <a href="{$F_FILTER_ACTION}">{'Reset'|translate}</a></span>
  </label>

</fieldset>

</form>

<table class="table2" width="97%">
  <thead>
    <tr class="throw">
      <td class="name">{'Name'|translate}</td>
      <td class="user">{'Username'|translate}</td>
      <td class="date">{'Creation date'|translate}</td>
      <td class="images">{'Number of images'|translate}</td>
      <td class="action">{'Actions'|translate}</td>
    </tr>
  </thead>
{if isset($sets)}
  {foreach from=$sets item=set name=sets_loop}
  <tr class="{if $smarty.foreach.sets_loop.index is odd}row1{else}row2{/if}">
    <td>
      <a href="{$set.U_EDIT}">{$set.NAME}</a>
    </td>
    <td>{$set.USERNAME}</td>
    <td style="text-align:center;">{$set.DATE_CREATION}</td>
    <td>{$set.NB_IMAGES}</td>
    <td style="padding-left:25px;">
      <a href="{$set.U_EDIT}" title="{'Edit this collection'|translate}"><i class="uc-icon-pencil"></i></a>
      <a href="{$set.U_EXPORT}" title="{'Export image list'|translate}"><i class="icon-upload"></i></a>
      <a href="{$set.U_DELETE}" title="{'Delete this collection'|translate}"
        class="uc-confirm-link" 
        data-icon="uc-icon-cancel" 
        data-validate="{'Delete'|translate}"
        data-content="{'Are you sure?'|translate}" 
        data-cancel="{'Cancel'|translate}" 
        rel="nofollow"
      ><i class="uc-icon-cancel"></i></a>
    </td>
  </tr>
  {/foreach}

  {else}
  <tr class="row2">
    <td colspan="8" style="text-align:center;font-style:italic;">{'No results'|translate}</td>
  </tr>
  {/if}
</table>