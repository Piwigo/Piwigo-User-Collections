{combine_css path=$USER_COLLEC_PATH|@cat:"template/style.css"}


{if $themeconf.name != "stripped" and $themeconf.parent != "stripped" and $themeconf.name != "simple-grey" and $themeconf.parent != "simple"}
  {$MENUBAR}
{else}
  {assign var="intern_menu" value="true"}
{/if}
<div id="content" class="content{if isset($MENUBAR)} contentWithMenu{/if}">
{if $intern_menu}{$MENUBAR}{/if}


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


</div>{* <!-- content --> *}