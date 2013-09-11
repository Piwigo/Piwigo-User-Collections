{combine_css path=$USER_COLLEC_PATH|cat:'template/style_collections.css'}
{include file=$USER_COLLEC_ABS_PATH|cat:'template/thumbnails_colorbox.tpl'}


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

{if $UC_MODE == 'password'}
<form action="{$F_LOGIN_ACTION}" method="post" name="login_form" class="properties">
  <fieldset>
    <legend>{'Restricted access'|@translate}</legend>

    <ul>
      <li>
        <span class="property">
          <label for="uc_password">{'Password'|@translate}</label>
        </span>
        <input tabindex="1" class="login" type="password" name="uc_password" id="uc_password" size="25" maxlength="25">
      </li>
    </ul>
  </fieldset>

  <p>
    <input tabindex="2" type="submit" value="{'Submit'|@translate}">
  </p>
</form>

<script type="text/javascript"><!--
document.login_form.username.focus();
//--></script>

{else}
{if !empty($CONTENT_DESCRIPTION)}
<div class="additional_info">
	{$CONTENT_DESCRIPTION}
</div>
{/if}

{if !empty($THUMBNAILS)}
<ul class="thumbnails" id="thumbnails">
{$THUMBNAILS}
</ul>
{/if}

{if !empty($navbar)}{include file='navigation_bar.tpl'|@get_extent:'navbar'}{/if}
{/if}

{if isset($clear)}<div style="clear: both;"></div>
</div>{/if}
</div>{* <!-- content --> *}