{combine_css path=$USER_COLLEC_PATH|cat:'template/style_collections.css'}

{footer_script}
var str_jump_to_photo = '{'jump to photo'|translate|escape:javascript}';
var user_theme = '{$USER_THEME}'; 
var uc_remove_action = false;
{/footer_script}

{combine_script id='uc_collection_zoom' require='jquery' load='footer' path='plugins/UserCollections/template/js/collectionZoom.js'}
{combine_script id='jquery.colorbox' load='footer' require='jquery' path='themes/default/js/plugins/jquery.colorbox.min.js'}
{combine_css id='colorbox' path='themes/default/js/plugins/colorbox/style2/colorbox.css'}

{if $UC_MODE == 'password'}
<form action="{$F_LOGIN_ACTION}" method="post" name="login_form" class="properties">
  <fieldset>
    <legend>{'Restricted access'|translate}</legend>
      <span class="property">
        <label for="uc_password">{'Password'|translate}</label>
      </span>
      <input tabindex="1" class="login" type="password" name="uc_password" id="uc_password" size="25" maxlength="25">
  </fieldset>

  <p>
    <input tabindex="2" type="submit" value="{'Submit'|translate}">
  </p>
</form>

<script>document.login_form.username.focus();</script>
{/if}