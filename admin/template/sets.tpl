{combine_css path=$USER_COLLEC_PATH|@cat:"admin/template/style.css"}

<div class="titrePage">
	<h2>User Collections</h2>
</div>

<form class="filter" method="post" name="filter" action="{$F_FILTER_ACTION}">
<fieldset>
  <legend>{'Filter'|@translate}</legend>
  <input type="hidden" name="page" value="user_list">

  <label>{'Name'|@translate} <input type="text" name="name" value="{$F_NAME}"></label>
  
  <label>{'Username'|@translate} <input type="text" name="username" value="{$F_USERNAME}"></label>

  <label>
  {'Sort by'|@translate}
  {html_options name=order_by options=$order_options selected=$order_selected}
  </label>

  <label>
  {'Sort order'|@translate}
  {html_options name=direction options=$direction_options selected=$direction_selected}
  </label>

  <label>
  &nbsp;
  <span><input class="submit" type="submit" name="filter" value="{'Submit'|@translate}"> <a href="{$F_FILTER_ACTION}">{'Reset'|@translate}</a></span>
  </label>

</fieldset>

</form>

<table class="table2" width="97%">
  <thead>
    <tr class="throw">
      <td class="name">{'Name'|@translate}</td>
      <td class="user">{'Username'|@translate}</td>
      <td class="date">{'Creation date'|@translate}</td>
      <td class="images">{'Number of images'|@translate}</td>
      <td class="action">{'Actions'|@translate}</td>
    </tr>
  </thead>

  {foreach from=$sets item=set name=sets_loop}
  <tr class="{if $smarty.foreach.sets_loop.index is odd}row1{else}row2{/if}">
    <td>
      <a href="{$set.U_EDIT}">{$set.NAME}</a>
    </td>
    <td>{$set.USERNAME}</td>
    <td style="text-align:center;">{$set.DATE_CREATION}</td>
    <td>{$set.NB_IMAGES}</td>
    <td style="padding-left:25px;">
      <a href="{$set.U_EDIT}" title="{'Edit this collection'|@translate}"><img src="{$themeconf.admin_icon_dir}/edit_s.png"></a>
      <a href="{$set.U_EXPORT}" title="{'Export image list'|@translate}"><img src="{$themeconf.admin_icon_dir}/plug_install.png"><span class="icon-upload"></span></a> <!-- temp 2.5/2.6 -->
      <a href="{$set.U_DELETE}" title="{'Delete this collection'|@translate}" onClick="return confirm('{'Are you sure?'|@translate}');"><img src="{$themeconf.admin_icon_dir}/delete.png"></a>
    </td>
  </tr>
  {/foreach}
  
  {if not $sets}
  <tr class="row2">
    <td colspan="8" style="text-align:center;font-style:italic;">{'No results'|@translate}</td>
  </tr>
  {/if}
</table>