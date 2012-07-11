{combine_css path=$USER_COLLEC_PATH|@cat:"template/style.css"}

{footer_script require='jquery'}
jQuery(".save_col").click(function() {ldelim}
  var name = prompt("{'Collection name:'|@translate}");
  if (name != null) {ldelim}
    $(this).attr("href",  $(this).attr("href") +"&name="+ name);
    return true;
  } else {ldelim}
    return false;
  }
});
{/footer_script}

{if $themeconf.name != "stripped" and $themeconf.parent != "stripped" and $themeconf.name != "simple-grey" and $themeconf.parent != "simple"}
  {$MENUBAR}
{else}
  {assign var="intern_menu" value="true"}
{/if}
<div id="content" class="content{if isset($MENUBAR)} contentWithMenu{/if}">
{if $intern_menu}{$MENUBAR}{/if}

<div class="titrePage">
  <ul class="categoryActions"></ul>
  <h2>{$TITLE}</h2>
</div>{* <!-- titrePage --> *}

{if isset($errors) or not empty($infos)}
{include file='infos_errors.tpl'}
{/if}

<p style="text-align:left;font-weight:bold;margin:20px;"><a href="{$U_CREATE}" class="save_col">{'Create a new collection'|@translate}</a></p>

{if $temp_col}
<fieldset>
  <legend>{'Unsaved collections'|@translate}</legend>
  
  <ul class="collecList">
  {foreach from=$temp_col item=col}
    <li {if $col.active}class="active"{/if}>
      <p class="collecTitle">
        <a href="{$col.U_EDIT}" rel="nofollow"><b>{$col.name}</b></a> 
        <i>{'%d photos'|@translate|@sprintf:$col.nb_images}</i>
      </p>
      <p class="collecActions">
        <a href="{$col.U_EDIT}" rel="nofollow">{'Edit'|@translate}</a>
        | <a href="{$col.U_SAVE}" class="save_col" rel="nofollow">{'save'|@translate}</a>
        {if $col.U_DOWNLOAD}| <a href="{$col.U_DOWNLOAD}" rel="nofollow">{'download'|@translate}</a>{/if}
        | <a href="{$col.U_DELETE}" onClick="return confirm('{'Are you sure?'|@translate}');" rel="nofollow">{'delete'|@translate}</a>
        {if not $col.active}| <a href="{$col.U_ACTIVE}" rel="nofollow">{'set active'|@translate}</a>{/if}
      </p>
    </li>
  {/foreach}
  </ul>
</fieldset>
{/if}

{if $collections}
<fieldset>
  <legend>{'Saved collections'|@translate}</legend>
  
  <ul class="collecList">
  {foreach from=$collections item=col}
    <li {if $col.active}class="active"{/if}>
      <p class="collecDate">
        {'created on %s'|@translate|@sprintf:$col.date_creation}
      </p>
      <p class="collecTitle">
        <a href="{$col.U_EDIT}" rel="nofollow"><b>{$col.name}</b></a> 
        <i>{'%d photos'|@translate|@sprintf:$col.nb_images}</i>
      </p>
      <p class="collecActions">
        <a href="{$col.U_EDIT}" rel="nofollow">{'Edit'|@translate}</a>
        {if $col.U_DOWNLOAD}| <a href="{$col.U_DOWNLOAD}" rel="nofollow">{'download'|@translate}</a>{/if}
        | <a href="{$col.U_DELETE}" onClick="return confirm('{'Are you sure?'|@translate}');" rel="nofollow">{'delete'|@translate}</a>
        {if not $col.active}| <a href="{$col.U_ACTIVE}" rel="nofollow">{'set active'|@translate}</a>{/if}
      </p>
    </li>
  {/foreach}
  </ul>
</fieldset>
{/if}

</div>{* <!-- content --> *}
