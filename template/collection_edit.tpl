{combine_css path=$USER_COLLEC_PATH|cat:'template/style_collections.css'}

{include file=$USER_COLLEC_ABS_PATH|cat:'template/thumbnails_colorbox.tpl'}

{*<!-- datepicker and timepicker -->*}
{include file='include/datepicker.inc.tpl'}
{combine_script id='jquery.ui.timepicker' load='footer' require='jquery.ui.datepicker,jquery.ui.slider' path=$USER_COLLEC_PATH|cat:'template/resources/jquery-timepicker/jquery-ui-timepicker-addon.js'}

{assign var="timepicker_language" value=$USER_COLLEC_PATH|cat:'template/resources/jquery-timepicker/i18n/jquery-ui-timepicker-'|cat:$lang_info.code|cat:'.js'}
{if "PHPWG_ROOT_PATH"|@constant|@cat:$timepicker_language|@file_exists}
{combine_script id="jquery.ui.timepicker-$lang_info.code" load='footer' require='jquery.ui.timepicker' path=$timepicker_language}
{/if}

{combine_css path='themes/default/js/ui/theme/jquery.ui.core.css'}
{combine_css path='themes/default/js/ui/theme/jquery.ui.theme.css'}
{combine_css path='themes/default/js/ui/theme/jquery.ui.slider.css'}
{combine_css path=$USER_COLLEC_PATH|cat:'template/resources/jquery-timepicker/jquery-ui-timepicker-addon.css'}


{footer_script require='jquery,jquery.ui.timepicker'}
var bg_color = $('#the_page #content').css('background-color');
if (!bg_color || bg_color=='transparent') {ldelim}
  bg_color = $('body').css('background-color');
}

{if isset($U_SHARE)}
{literal}
  var $share_form = jQuery('#share_form');
  
  // functions
  jQuery.fn.extend({
      hideVis: function() {
          $(this).css('visibility', 'hidden');
          return this;
      },
      showVis: function() {
          $(this).css('visibility', 'visible');
          return this;
      },
      toggleVis: function(toggle) {
          if ($(this).css('visibility')=='hidden' || toggle === true){
              return $(this).showVis();
          } else {
              return $(this).hideVis();
          }
      }
  });
  
  function enterShareKeyEdit() {
      $share_form.find('.url-edit').show();
      $share_form.find('.url-normal').hide();
      jQuery(".share_colorbox_open").colorbox.resize({speed:0});
  }
  function exitShareKeyEdit() {
      $share_form.find('.url-edit').hide();
      $share_form.find('.url-normal').show();
      jQuery(".share_colorbox_open").colorbox.resize({speed:0});
  }
  
  // hide some inputs
  exitShareKeyEdit();
  
  // display key
  $share_form.find('.url-more').text($share_form.find('input[name="share_key"]').val());
  
  // url edition
  $share_form.find('.edit_share_key').on('click', function() {
      enterShareKeyEdit();
      return false;
  });
  $share_form.find('.set_share_key').on('click', function() {
      if ($share_form.find('input[name="share_key"]').val().length < 8) {
          alert("{/literal}{'The key must be at least 8 characters long'|@translate|escape:javascript}{literal}");
      }
      else {
          $share_form.find('.url-more').text($share_form.find('input[name="share_key"]').val());
          exitShareKeyEdit();
      }
      return false;
  });
  $share_form.find('.cancel_share_key').on('click', function() {
      $share_form.find('input[name="share_key"]').val($share_form.find('.url-more').text());
      exitShareKeyEdit();
      return false;
  });
  $share_form.find('.url-more').on('dblclick', function() {
      enterShareKeyEdit();
  });
  
  // optional inputs
  $share_form.find('.share-option').each(function() {
      $share_form.find('input[name="'+ $(this).data('for') +'"]').hideVis();
  }).on('change', function() {
      $share_form.find('input[name="'+ $(this).data('for') +'"]').toggleVis($(this).is(':checked'));
  });
  
  // datetime picker
  $share_form.find('input[name="share_deadline"]').datetimepicker({
      dateFormat: 'yy-mm-dd',
      minDate: new Date()
  });
  
  
  // popup
  jQuery(".share_colorbox_open").colorbox({
    {/literal}{if isset($share.open)}open: true, transition:"none",{/if}{literal}
    inline:true
  });
  jQuery(".share_colorbox_close").click(function() {
    jQuery(".share_colorbox_open").colorbox.close();
    return false;
  });
  jQuery("#share_form").css('background-color', bg_color);
{/literal}
{/if}

{if isset($U_MAIL)}
{literal}
  jQuery(".mail_colorbox_open").colorbox({
    {/literal}{if isset($contact.open)}open: true, transition:"none",{/if}{literal}
    inline:true
  });
  jQuery(".mail_colorbox_close").click(function() {
    jQuery(".mail_colorbox_open").colorbox.close();
    return false;
  });
  
  
  jQuery("#mail_form").css('background-color', bg_color);
{/literal}
{/if}

{literal}
jQuery("#edit_form_show").click(function() {
  jQuery("#edit_form_show").hide();
  jQuery(".additional_info").hide();
  jQuery("#edit_form").show();
});
jQuery("#edit_form_hide").click(function() {
  jQuery("#edit_form_show").show();
  jQuery(".additional_info").show();
  jQuery("#edit_form").hide();
});
{/literal}
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


{if isset($collection)}

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

  <p>
    <input type="submit" name="save_col" value="{'Save'|@translate}">
    <a id="edit_form_hide">{'Cancel'|@translate}</a>
  </p>
</fieldset>
</form>

{*<!-- create share links -->*}
{if isset($U_SHARE)}
<div style="display:none;">
  <form id="share_form" class="uc_form" action="{$F_ACTION}" method="post">
    {include file='infos_errors.tpl' errors=$share.errors infos=$share.infos}
    
    <table>
      <tr>
        <td colspan="2" class="url-preview" style="white-space:nowrap;">
          <span class="url-base">{$U_SHARE}</span><span class="url-more url-normal"></span>
          <input type="text" name="share_key" class="url-edit" size="20" value="{$share.share_key}">
          <button class="url-normal edit_share_key">{'Edit'|@translate}</button>
          <button class="url-edit set_share_key">{'OK'|@translate}</button>
          <a href="#" class="url-edit cancel_share_key">{'Cancel'|@translate}</button>
        </td>
      </tr>
      <tr>
        <td class="title"><label>
          {'Password'|@translate}
          <input type="checkbox" name="use_share_password" data-for="share_password" class="share-option">
        </label></td>
        <td>
          <input type="text" name="share_password" size="25" maxlength="25" value="{$share.password}" placeholder="{'Password'|@translate}">
        </td>
      </tr>
      <tr>
        <td class="title"><label>
          {'Expiration date'|@translate}
          <input type="checkbox" name="use_share_deadline" data-for="share_deadline" class="share-option">
        </label></td>
        <td>
          <input type="text" name="share_deadline" size="25" value="{$share.deadline}" placeholder="{'Date'|@translate}">
        </td>
      </tr>
      <tr>
        <td class="title">&nbsp;</td>
        <td>
          <input class="submit" type="submit" name="add_share" value="{'Add'|@translate}">
          <a class="share_colorbox_close" href="#">{'Cancel'|@translate}</a>
          <input type="hidden" name="key" value="{$UC_TKEY}">
        </td>
      </tr>
    </table>
    
  {if not empty($collection.SHARES)}
    <table class="shares_list">
      <tr class="header">
        <th>{'Share key'|@translate}</th>
        <th>{'Creation date'|@translate}</th>
        <th>{'Password'|@translate}</th>
        <th>{'Expiration date'|@translate}</th>
        <th></th>
      </tr>
    {foreach from=$collection.SHARES item=share}
      <tr class="{cycle values='row2,row1'} {if $share.expired}expired{/if}">
        <td><a href="{$share.url}">{$share.share_key}</a></td>
        <td>{$share.add_date_readable}</td>
        <td>{if $share.params.password}{'Yes'|@translate}{else}{'No'|@translate}{/if}</td>
        <td>{if $share.params.deadline}{$share.params.deadline_readable}{else}{'No'|@translate}{/if}</td>
        <td><a href="{$share.u_delete}" onClick="return confirm('{'Are you sure?'|@translate}');">
          <img src="{$ROOT_URL}{$USER_COLLEC_PATH}template/resources/delete.png" width=16 height=16></a>
        </td>
      </tr>
    {/foreach}
    </table>
  {/if}
  </form>
</div>
{/if}

{*<!-- send collection by mail -->*}
{if isset($U_MAIL)}
<div style="display:none;">
  <form id="mail_form" class="uc_form" action="{$F_ACTION}" method="post">
    {include file='infos_errors.tpl' errors=$contact.errors}

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
          <input type="hidden" name="key" value="{$UC_TKEY}">
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

{if isset($clear)}<div style="clear: both;"></div>
</div>{/if}
</div>{* <!-- content --> *}