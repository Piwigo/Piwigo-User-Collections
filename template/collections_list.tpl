{combine_css path=$USER_COLLEC_PATH|cat:'template/style_collections.css'}

{footer_script require='jquery'}
jQuery(".new_col").click(function() {ldelim}
  var name = prompt("{'Collection name:'|@translate}");
  if (name != null) {ldelim}
    $(this).attr("href",  $(this).attr("href") +"&name="+ name);
    return true;
  } else {ldelim}
    return false;
  }
});

jQuery(".titrePage h2").append(" [{$COLLECTIONS_COUNT}]");
{/footer_script}

{* <!-- Menubar & titrePage --> *}
{if $themeconf.name == "stripped" or $themeconf.parent == "stripped"}
  {include file=$USER_COLLEC_ABS_PATH|@cat:'template/themes/stripped.tpl'}
  {assign var="clear" value="true"}
{elseif $themeconf.name == "simple-grey" or $themeconf.parent == "simple"}
  {include file=$USER_COLLEC_ABS_PATH|@cat:'template/themes/simple.tpl'}
  {assign var="clear" value="true"}
{else}
  {include file=$USER_COLLEC_ABS_PATH|@cat:'template/themes/default.tpl'}
{/if}

{if isset($errors) or not empty($infos)}
{include file='infos_errors.tpl'}
{/if}


<p style="text-align:left;font-weight:bold;margin:20px;"><a href="{$U_CREATE}" class="new_col">{'Create a new collection'|@translate}</a></p>

{if !empty($CATEGORIES)}
{$CATEGORIES}
{else}
{'You have no collection'|@translate}
{/if}

{if isset($clear)}<div style="clear: both;"></div>
</div>{/if}
</div>{* <!-- content --> *}