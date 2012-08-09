{combine_css path=$USER_COLLEC_PATH|@cat:"template/style.css"}
{combine_script id='jquery.zclip' path=$USER_COLLEC_PATH|@cat:"template/jquery.zclip.min.js"}
{combine_script id='jquery.tipTip' path='themes/default/js/plugins/jquery.tipTip.minified.js'}

{footer_script require='jquery'}
function bindZclip() {ldelim}
  jQuery("#publicURL .button").zclip({ldelim}
    path:'{$USER_COLLEC_PATH}template/ZeroClipboard.swf',
    copy:$("#publicURL .url").html(),
    afterCopy: function() {ldelim}
      $('.confirm').remove();
      $('<span class="confirm" style="display:none;">{'Copied'|@translate}</span>').appendTo("#publicURL")
        .fadeIn(400).delay(1000).fadeOut(400, function(){ldelim} $(this).remove(); });
    }
  });
}

jQuery("input[name='public']").change(function() {ldelim}
  jQuery("#publicURL").fadeToggle("fast");
  bindZclip();
});
jQuery("#publicURL .button").tipTip({ldelim}
  delay: 0,
  defaultPosition: 'right'
});

jQuery("#actions input").click(function() {ldelim}
  if (confirm("{'Are you sure?'|@translate}")) {ldelim}
    document.location.href = jQuery(this).data("href");
  }
  return false;
});
{if $collection.PUBLIC}bindZclip();{/if}
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


{if $collection}
<form action="{$F_ACTION}" method="post">
<fieldset id="colProperties">
  <legend>{'Properties'|@translate}</legend>
  
  <p class="title"><label for="name">{'Name'|@translate}</label></p>
  <p><input type="text" name="name" id="name" value="{$collection.NAME}" size="60"></p>
  <p class="title">{'Public collection'|@translate}</p>
  <p>
    <label><input type="radio" name="public" value="0" {if not $collection.PUBLIC}checked="checked"{/if}> {'No'|@translate}</label>
    <label><input type="radio" name="public" value="1" {if $collection.PUBLIC}checked="checked"{/if}> {'Yes'|@translate}</label>
    <span id="publicURL" {if not $collection.PUBLIC}style="display:none;"{/if}><span class="button" title="{'Copy to clipboard'|@translate}">&nbsp;</span><span class="url">{$collection.U_PUBLIC}</span></span>
  </p>
  <p>
    <input type="submit" name="save_col" value="{'Save'|@translate}">
    <a href="{$U_LIST}" rel="nofollow">{'Return to collections list'|@translate}</a>
  </p>
</fieldset>
</form>


<form id="actions" style="text-align:center;">
<input type="submit" data-href="{$collection.U_CLEAR}" value="{'Clear collection'|@translate}">
<input type="submit" data-href="{$collection.U_DELETE}" value="{'Delete'|@translate}">
</form>


{if $collection.NB_IMAGES > 0}
<ul class="thumbnails" id="thumbnails">
{$THUMBNAILS}
</ul>
{else}
<p><i>{'This collection is empty'|@translate}</i></p>
{/if}

{if !empty($navbar)}{include file='navigation_bar.tpl'|@get_extent:'navbar'}{/if}
{/if}

{if $clear}<div style="clear: both;"></div>{/if}
</div>{* <!-- content --> *}