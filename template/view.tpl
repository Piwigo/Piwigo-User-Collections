{combine_css path=$USER_COLLEC_PATH|@cat:"template/style.css"}

{$MENUBAR}

<div id="content" class="content{if isset($MENUBAR)} contentWithMenu{/if}">
<div class="titrePage">
  <ul class="categoryActions">
  {if !empty($COLLECTION_ACTIONS)}{$COLLECTION_ACTIONS}{/if}
  </ul>
  <h2>{$TITLE}</h2>
</div>{* <!-- titrePage --> *}

{if isset($errors) or not empty($infos)}
{include file='infos_errors.tpl'}
{/if}

{if !empty($THUMBNAILS)}
<ul class="thumbnails" id="thumbnails">
{$THUMBNAILS}
</ul>
{/if}

{if !empty($navbar)}{include file='navigation_bar.tpl'|@get_extent:'navbar'}{/if}

{if $U_LIST}<p style="text-align:center;font-weight:bold;margin:20px;"><a href="{$U_LIST}" rel="nofollow">{'Return to collections list'|@translate}</a></p>{/if}

</div>{* <!-- content --> *}