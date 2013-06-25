{combine_css path=$USER_COLLEC_PATH|cat:'template/style_collections.css'}
{combine_script id='ZeroClipboard' path=$USER_COLLEC_PATH|cat:'template/resources/ZeroClipboard.min.js'}
{include file=$USER_COLLEC_ABS_PATH|cat:'template/thumbnails_colorbox.tpl'}

{footer_script require='jquery'}
{if $user_collections.allow_public}
ZeroClipboard.setDefaults( {ldelim} moviePath: "{$ROOT_URL}{$USER_COLLEC_PATH}template/resources/ZeroClipboard.swf" } );
var clip = new ZeroClipboard();

clip.glue(jQuery("#publicURL .button").get());
clip.addEventListener('onMouseOver', function() {ldelim}
  clip.setText(jQuery("#publicURL .url").val());
});
clip.addEventListener('complete', function() {ldelim}
  jQuery('.confirm').remove();
  jQuery("#publicURL .url").select();
  jQuery('<span class="confirm" style="display:none;">{'Copied'|@translate}</span>').appendTo("#publicURL")
    .fadeIn(400).delay(1000).fadeOut(400, function(){ldelim} jQuery(this).remove(); });
});

jQuery("#publicURL .url").click(function() {ldelim}
  jQuery(this).select();
});

jQuery("input[name='public']").change(function() {ldelim}
  jQuery("#publicURL").fadeToggle("fast");
});
{/if}

{if $collection.PUBLIC && $user_collections.allow_mails}
  jQuery(window).load(function(){ldelim}
    jQuery(".mail_colorbox_open").colorbox({ldelim}
      {if isset($uc_mail_errors)}open: true, transition:"none",{/if}
      inline:true
    });
    jQuery(".mail_colorbox_close").click(function() {ldelim}
      jQuery(".mail_colorbox_open").colorbox.close();
      return false;
    })
  });
  jQuery("#mail_form").css('background-color', jQuery("#the_page #content").css('background-color'));
{/if}

jQuery("#edit_form_show").click(function() {ldelim}
  jQuery("#edit_form_show").hide();
  jQuery(".additional_info").hide();
  jQuery("#edit_form").show();
});
jQuery("#edit_form_hide").click(function() {ldelim}
  jQuery("#edit_form_show").show();
  jQuery(".additional_info").show();
  jQuery("#edit_form").hide();
});
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


{if !empty($CONTENT_DESCRIPTION)}
<div class="additional_info">
	{$CONTENT_DESCRIPTION}
</div>
{/if}

<p style="text-align:center;"><input type="submit" id="edit_form_show" value="{'Edit'|@translate}"></p>

{* <!-- edit collection -->*}
<form action="{$F_ACTION}" method="post" id="edit_form" style="display:none;">
<fieldset id="colProperties">
  <legend>{'Properties'|@translate}</legend>
  
  <p class="title"><label for="name">{'Name'|@translate}</label></p>
  <p><input type="text" name="name" id="name" value="{$collection.NAME|escape:html}" size="60"></p>
  
  <p class="title"><label for="comment">{'Description'|@translate}</label></p>
  <p><textarea name="comment" id="comment" style="width:400px;height:100px;">{$collection.COMMENT}</textarea></p>
  
{if $user_collections.allow_public}
  <p class="title">{'Public collection'|@translate}</p>
  <p>
    <label><input type="radio" name="public" value="0" {if not $collection.PUBLIC}checked="checked"{/if}> {'No'|@translate}</label>
    <label><input type="radio" name="public" value="1" {if $collection.PUBLIC}checked="checked"{/if}> {'Yes'|@translate}</label>
    <span id="publicURL" {if not $collection.PUBLIC}style="display:none;"{/if}><!--
    --><span class="button" title="{'Copy to clipboard'|@translate}">&nbsp;</span><!--
    --><input type="text" class="url" value="{$collection.U_PUBLIC}" size="{$collection.U_PUBLIC|strlen}"><!--
  --></span>
  </p>
{/if}

  <p>
    <input type="submit" name="save_col" value="{'Save'|@translate}">
    <a id="edit_form_hide">{'Cancel'|@translate}</a>
  </p>
</fieldset>
</form>


{* <!-- send collection by mail -->*}
{if $user_collections.allow_public && $user_collections.allow_mails}
<div style="display:none;">
  <form id="mail_form" action="{$F_ACTION}" method="post">
  {if isset($uc_mail_errors)}
    {assign var=errors value=$uc_mail_errors}
    {include file='infos_errors.tpl'}
  {/if}

    <table>
      <tr>
        <td class="title"><label for="sender_name">{'Your name'|@translate}</label></td>
        <td>
          <input type="text" name="sender_name" id="sender_name" size="40" value="{$contact.sender_name}">
        </td>
      </tr>
      <tr>
        <td class="title"><label for="sender_email">{'Your e-mail'|@translate}</label></td>
        <td>
          <input type="text" name="sender_email" id="sender_email" size="40" value="{$contact.sender_email}">
        </td>
      </tr>
      <tr>
        <td class="title"><label for="recipient_name">{'Recipient name'|@translate}</label></td>
        <td>
          <input type="text" name="recipient_name" id="recipient_name" size="40" value="{$contact.recipient_name}">
        </td>
      </tr>
      <tr>
        <td class="title"><label for="recipient_email">{'Recipient e-mail'|@translate}</label></td>
        <td>
          <input type="text" name="recipient_email" id="recipient_email" size="40" value="{$contact.recipient_email}">
        </td>
      </tr>
      <tr>
        <td class="title"><label for="nb_images">{'Number of images'|@translate}</label></td>
        <td>
          <select name="nb_images">
            <option value="2" {if $contact.nb_images==2}selected="selected"{/if}>2</option>
            <option value="4" {if $contact.nb_images==4}selected="selected"{/if}>4</option>
            <option value="8" {if $contact.nb_images==8}selected="selected"{/if}>8</option>
          </select>
        </td>
      </tr>
      <tr>
        <td class="title"><label for="message">{'Message (optional)'|@translate}</label></td>
        <td><textarea name="message" id="message" rows="6" style="width:350px;">{$contact.message}</textarea></td>
      </tr>
      <tr>
        <td class="title">&nbsp;</td>
        <td>
          <input class="submit" type="submit" name="send_mail" value="{'Send'|@translate}">
          <a class="mail_colorbox_close" href="#">{'Cancel'|@translate}</a>
          <input type="hidden" name="key" value="{$contact.KEY}" />
        </td>
      </tr>
    </table>
  </form>
</div>
{/if}


{* <!-- collection content -->*}
{if $collection.NB_IMAGES > 0}
<ul class="thumbnails" id="thumbnails">
{$THUMBNAILS}
</ul>
{else}
<p><i>{'This collection is empty'|@translate}</i></p>
{/if}

{if !empty($navbar)}{include file='navigation_bar.tpl'|@get_extent:'navbar'}{/if}


{if isset($clear)}<div style="clear: both;"></div>
</div>{/if}
</div>{* <!-- content --> *}