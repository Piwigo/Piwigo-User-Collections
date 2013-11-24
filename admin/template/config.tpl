{combine_css path=$USER_COLLEC_PATH|cat:'admin/template/style.css'}

{footer_script}
$('input[name="allow_public"]').on('change', function() {
    $('#allow_mails').toggle($(this).is(':checked'));
});
{/footer_script}

<div class="titrePage">
	<h2>User Collections</h2>
</div>

<form method="post" action="" class="properties">
<fieldset id="commentsConf">
  <ul>
    <li>
      <label>
        <input type="checkbox" name="allow_public" {if $user_collections.allow_public}checked="checked"{/if}>
        <b>{'Allow users to set their collections as public'|translate}</b>
      </label>
    </li>
    <li id="allow_mails" {if not $user_collections.allow_public}style="display:none;"{/if}>
      <label>
        <input type="checkbox" name="allow_mails" {if $user_collections.allow_mails}checked="checked"{/if}>
        <b>{'Allow users to send their public collections by mail'|translate}</b>
      </label>
    </li>
  </ul>
</fieldset>

  <p class="formButtons"><input type="submit" name="save_config" value="{'Save Settings'|translate}"></p>
</form>