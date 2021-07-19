{strip}
{combine_css path=$USER_COLLEC_PATH|cat:'template/style_collections.css'}

{include file=$USER_COLLEC_ABS_PATH|cat:'template/thumbnails_colorbox.tpl'}

{*<!-- datepicker and timepicker -->*}
{combine_script id='jquery.ui.datepicker' load='footer' path='themes/default/js/ui/jquery.ui.datepicker.js'}
{combine_script id='jquery.ui.timepicker' load='footer' require='jquery.ui.slider' path=$USER_COLLEC_PATH|cat:'template/resources/jquery-timepicker/jquery-ui-timepicker-addon.js'}

{assign var=datepicker_language value='themes/default/js/ui/i18n/jquery.ui.datepicker-'|cat:$lang_info.code|cat:'.js'}
{if 'PHPWG_ROOT_PATH'|constant|cat:$datepicker_language|file_exists}
{combine_script id='jquery.ui.datepicker-'|cat:$lang_info.code load='footer' require='jquery.ui.datepicker' path=$datepicker_language}
{/if}

{assign var=timepicker_language value=$USER_COLLEC_PATH|cat:'template/resources/jquery-timepicker/i18n/jquery-ui-timepicker-'|cat:$lang_info.code|cat:'.js'}
{if 'PHPWG_ROOT_PATH'|constant|cat:$timepicker_language|file_exists}
{combine_script id='jquery.ui.timepicker-'|cat:$lang_info.code load='footer' require='jquery.ui.timepicker' path=$timepicker_language}
{/if}

{combine_css path='themes/default/js/ui/theme/jquery.ui.core.css'}
{combine_css path='themes/default/js/ui/theme/jquery.ui.theme.css'}
{combine_css path='themes/default/js/ui/theme/jquery.ui.datepicker.css'}
{combine_css path='themes/default/js/ui/theme/jquery.ui.slider.css'}
{combine_css path=$USER_COLLEC_PATH|cat:'template/resources/jquery-timepicker/jquery-ui-timepicker-addon.css'}
{/strip}

{$UC_JS}

{if isset($collection)}

<p class="collection-edit">
  <input type="submit" id="edit_form_show" value="{'Edit'|translate}">
  {if empty($THUMBNAILS)} {'or'|translate} <a id="delete" href="{$URL_DELETE}">{'Delete'|translate}</a>{/if}
</p>

{* <!-- edit collection -->*}
<form action="{$F_ACTION}" method="post" id="edit_form" style="display:none;">
<fieldset id="colProperties">
  <legend>{'Edit'|translate}</legend>

  <p class="title"><label for="name">{'Name'|translate}</label></p>
  <p><input type="text" name="name" id="name" value="{$collection.NAME|escape:html}" size="60"></p>

  <p class="title"><label for="comment">{'Description'|translate}</label></p>
  <p><textarea name="comment" id="comment" style="width:400px;height:100px;">{$collection.COMMENT}</textarea></p>

  <p>
    <input type="submit" name="save_col" value="{'Save'|translate}">
    <a id="edit_form_hide">{'Cancel'|translate}</a>
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
          <button class="url-normal edit_share_key">{'Edit'|translate}</button>
          <button class="url-edit set_share_key">{'OK'|translate}</button>
          <a href="#" class="url-edit cancel_share_key">{'Cancel'|translate}</button>
        </td>
      </tr>
      <tr>
        <td class="title"><label>
          {'Password'|translate}
          <input type="checkbox" name="use_share_password" data-for="share_password" class="share-option">
        </label></td>
        <td>
          <input type="text" name="share_password" size="25" maxlength="25" value="{$share.password}" placeholder="{'Password'|translate}">
        </td>
      </tr>
      <tr>
        <td class="title"><label>
          {'Expiration date'|translate}
          <input type="checkbox" name="use_share_deadline" data-for="share_deadline" class="share-option">
        </label></td>
        <td>
          <input type="text" name="share_deadline" size="25" value="{$share.deadline}" placeholder="{'Date'|translate}">
        </td>
      </tr>
      <tr>
        <td class="title">&nbsp;</td>
        <td>
          <input class="submit" type="submit" name="add_share" value="{'Add'|translate}">
          <a class="share_colorbox_close" href="#">{'Cancel'|translate}</a>
          <input type="hidden" name="key" value="{$UC_TKEY}">
        </td>
      </tr>
    </table>

  {if not empty($collection.SHARES)}
    <table class="shares_list">
      <tr class="header">
        <th>{'Share key'|translate}</th>
        <th>{'Creation date'|translate}</th>
        <th>{'Password'|translate}</th>
        <th>{'Expiration date'|translate}</th>
        <th></th>
      </tr>
    {foreach from=$collection.SHARES item=share}
      <tr class="{cycle values='row2,row1'} {if $share.expired}expired{/if}">
        <td><a href="{$share.url}">{$share.share_key}</a></td>
        <td>{$share.add_date_readable}</td>
        <td>{if $share.params.password}{'Yes'|translate}{else}{'No'|translate}{/if}</td>
        <td>{if $share.params.deadline}{$share.params.deadline_readable}{else}{'No'|translate}{/if}</td>
        <td><a href="{$share.u_delete}" onClick="return confirm('{'Are you sure?'|translate}');">
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
        <td class="title"><label for="sender_name">{'Your name'|translate}</label></td>
        <td>
          <input type="text" name="sender_name" id="sender_name" size="40" value="{$contact.sender_name}">
        </td>
      </tr>
      <tr>
        <td class="title"><label for="sender_email">{'Your e-mail'|translate}</label></td>
        <td>
          <input type="text" name="sender_email" id="sender_email" size="40" value="{$contact.sender_email}">
        </td>
      </tr>
      {if $UC_CONFIG.allow_send_admin && !$UC_CONFIG.allow_mails}
      <tr>
        <td class="title">{'To'|translate}</td>
        <td>{'Administrator'|translate}</td>
      </tr>
      {/if}
      <tr {if !$UC_CONFIG.allow_send_admin || !$UC_CONFIG.allow_mails}style="display:none"{/if}>
        <td class="title">{'To'|translate}</td>
        <td>
          <label><input type="radio" name="to" value="admin" checked> {'Administrator'|translate}</label>
          <label><input type="radio" name="to" value="email"> {'Someone else'|translate}</label>
        </td>
      </tr>
      <tr style="display:none" class="recipient-input">
        <td class="title"><label for="recipient_name">{'Recipient name'|translate}</label></td>
        <td>
          <input type="text" name="recipient_name" id="recipient_name" size="40" value="{$contact.recipient_name}">
        </td>
      </tr>
      <tr style="display:none" class="recipient-input">
        <td class="title"><label for="recipient_email">{'Recipient e-mail'|translate}</label></td>
        <td>
          <input type="text" name="recipient_email" id="recipient_email" size="40" value="{$contact.recipient_email}">
        </td>
      </tr>
      <tr>
        <td class="title"><label for="nb_images">{'Number of images'|translate}</label></td>
        <td>
          <select name="nb_images">
            <option value="2" {if $contact.nb_images==2}selected="selected"{/if}>2</option>
            <option value="4" {if $contact.nb_images==4}selected="selected"{/if}>4</option>
            <option value="8" {if $contact.nb_images==8}selected="selected"{/if}>8</option>
          </select>
        </td>
      </tr>
      <tr>
        <td class="title"><label for="message">{'Message (optional)'|translate}</label></td>
        <td><textarea name="message" id="message" rows="6" style="width:350px;">{$contact.message}</textarea></td>
      </tr>
      <tr>
        <td class="title">&nbsp;</td>
        <td>
          <input class="submit" type="submit" name="send_mail" value="{'Send'|translate}">
          <a class="mail_colorbox_close" href="#">{'Cancel'|translate}</a>
          <input type="hidden" name="key" value="{$UC_TKEY}">
        </td>
      </tr>
    </table>
  </form>
</div>
{/if}

{if empty($THUMBNAILS)}
<p style="text-align:center"><i>{'This collection is empty'|translate}</i></p>
{/if}

{/if}
