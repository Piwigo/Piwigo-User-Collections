<form action="{$F_ACTION}" method="post" class="uc_form" id="edit_form">
  <div class="uc_input_group">
    <label for="name">{'Name'|translate}</label>
    <input type="text" name="name" id="name" value="{$collection.NAME|escape:html}" size="60">
  </div>

  <div class="uc_input_group">
    <label for="comment">{'Description'|translate}</label>
    <textarea name="comment" id="comment">{$collection.COMMENT}</textarea>
  </div>

  <input type="hidden" name="save_col">
</form>

