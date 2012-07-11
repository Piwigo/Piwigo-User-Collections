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
{if $collection.PUBLIC}bindZclip();{/if}
{/footer_script}

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

{if $collection and not $collection.IS_TEMP}
<form action="{$U_VIEW}" method="post">
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
{/if}

  

{if !empty($THUMBNAILS)}
<ul class="thumbnails" id="thumbnails">
{$THUMBNAILS}
</ul>
{else}
<p><i>{'This collection is empty'|@translate}</i></p>
{/if}

{if !empty($navbar)}{include file='navigation_bar.tpl'|@get_extent:'navbar'}{/if}

<p style="text-align:center;font-weight:bold;margin:20px;"><a href="{$U_LIST}" rel="nofollow">{'Return to collections list'|@translate}</a></p>

</div>{* <!-- content --> *}