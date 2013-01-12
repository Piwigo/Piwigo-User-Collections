{combine_css path=$USER_COLLEC_PATH|@cat:"template/style.css"}
{combine_script id='jquery.zclip' path=$USER_COLLEC_PATH|@cat:"template/resources/jquery.zclip.min.js"}
{combine_script id='jquery.tipTip' path='themes/default/js/plugins/jquery.tipTip.minified.js'}

{combine_script id='jquery.colorbox' load='footer' require='jquery' path='themes/default/js/plugins/jquery.colorbox.min.js'}
{combine_css path="themes/default/js/plugins/colorbox/style2/colorbox.css"}

{footer_script require='jquery'}
{if $user_collections.allow_public}
  function bindZclip() {ldelim}
    jQuery("#publicURL .button").zclip({ldelim}
      path:'{$ROOT_URL}{$USER_COLLEC_PATH}template/resources/ZeroClipboard.swf',
      copy:$("#publicURL .url").html(),
      afterCopy: function() {ldelim}
        $('.confirm').remove();
        $("#publicURL .url").select();
        $('<span class="confirm" style="display:none;">{'Copied'|@translate}</span>').appendTo("#publicURL")
          .fadeIn(400).delay(1000).fadeOut(400, function(){ldelim} $(this).remove(); });
      }
    });
    $("#publicURL .url").click(function() {ldelim}
      $(this).select();
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
{/if}

{if $collection.PUBLIC && $user_collections.allow_mails}
  $(window).load(function(){ldelim}
    $(".mail_colorbox_open").colorbox({ldelim}
      {if isset($uc_mail_errors)}open: true, transition:"none",{/if}
      inline:true
    });
    $(".mail_colorbox_close").click(function() {ldelim}
      $(".mail_colorbox_open").colorbox.close();
      return false;
    })
  });
  $("#mail_form").css('background-color', $("#the_page #content").css('background-color'));
{/if}
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
{* <!-- edit collection -->*}
<form action="{$F_ACTION}" method="post">
<fieldset id="colProperties">
  <legend>{'Properties'|@translate}</legend>
  
  <p class="title"><label for="name">{'Name'|@translate}</label></p>
  <p><input type="text" name="name" id="name" value="{$collection.NAME|escape:html}" size="60"></p>
{if $user_collections.allow_public}
  <p class="title">{'Public collection'|@translate}</p>
  <p>
    <label><input type="radio" name="public" value="0" {if not $collection.PUBLIC}checked="checked"{/if}> {'No'|@translate}</label>
    <label><input type="radio" name="public" value="1" {if $collection.PUBLIC}checked="checked"{/if}> {'Yes'|@translate}</label>
    <span id="publicURL" {if not $collection.PUBLIC}style="display:none;"{/if}><span class="button" title="{'Copy to clipboard'|@translate}">&nbsp;</span><input type="text" class="url" value="{$collection.U_PUBLIC}" size="{$collection.U_PUBLIC|strlen}"></span>
  </p>
{/if}
  <p>
    <input type="submit" name="save_col" value="{'Save'|@translate}">
    <a href="{$U_LIST}" rel="nofollow">{'Return to collections list'|@translate}</a>
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
{/if}

{if $clear}<div style="clear: both;"></div>
</div>{/if}
</div>{* <!-- content --> *}