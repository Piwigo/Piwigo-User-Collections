{combine_css path=$USER_COLLEC_PATH|cat:'template/style_collections.css'}
{include file=$USER_COLLEC_ABS_PATH|cat:'template/thumbnails_colorbox.tpl'}

{if $UC_MODE == 'password'}
<form action="{$F_LOGIN_ACTION}" method="post" name="login_form" class="properties">
  <fieldset>
    <legend>{'Restricted access'|translate}</legend>

    <ul>
      <li>
        <span class="property">
          <label for="uc_password">{'Password'|translate}</label>
        </span>
        <input tabindex="1" class="login" type="password" name="uc_password" id="uc_password" size="25" maxlength="25">
      </li>
    </ul>
  </fieldset>

  <p>
    <input tabindex="2" type="submit" value="{'Submit'|translate}">
  </p>
</form>

<script>document.login_form.username.focus();</script>
{/if}