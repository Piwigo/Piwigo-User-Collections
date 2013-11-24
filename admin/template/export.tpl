{combine_css path=$USER_COLLEC_PATH|cat:'admin/template/style.css'}

{footer_script require='jquery.ui.sortable'}
$('#fields_active, #fields_inactive').sortable({
  connectWith: '.connectedSortable',
  items: '> li',
  placeholder: 'sortable-moving'
}).disableSelection();

$('input[name="generate"]').click(function() {
  query = 'ws.php?format=rest&method=pwg.collections.getSerialized&col_id={$COL_ID}';
  $('#fields_active li').each(function() {
    query+= '&content[]='+ $(this).data('name');
  });

  $('#iframeWrapper').show();
  $('#invokeFrame').attr('src', query);

  return false;
});

$('#invokeFrame').load(function() {
  $(this).css('height', $(this).contents().find('body').outerHeight(true)+10);
});

$('input[name="download"]').click(function() {
  $('#fields_active li').each(function() {
    $('#export_form').append('<input type="hidden" name="active[]" value="'+ $(this).data('name') +'"/>');
  });
  $('#fields_inactive li').each(function() {
    $('#export_form').append('<input type="hidden" name="inactive[]" value="'+ $(this).data('name') +'"/>');
  });
});
{/footer_script}

<div class="titrePage">
	<h2>User Collections</h2>
</div>

{if $COL_ID}
<form method="post" action="" class="properties" id="export_form">
<fieldset>
  <legend>{'Fields'|translate}</legend>
  <ul id="fields_active" class="connectedSortable">
    <h4>{'Active'|translate}</h4>
  {foreach from=$active_fields item=field}
    <li data-name="{$field}"><img src="{$themeconf.admin_icon_dir}/cat_move.png"> {$field}</li>
  {/foreach}
  </ul>
  <ul id="fields_inactive" class="connectedSortable">
    <h4>{'Inactive'|translate}</h4>
  {foreach from=$inactive_fields item=field}
    <li data-name="{$field}"><img src="{$themeconf.admin_icon_dir}/cat_move.png"> {$field}</li>
  {/foreach}
  </ul>

  <p class="formButtons">
    <input type="submit" name="generate" value="{'Preview'|translate}">
    <input type="submit" name="download" value="{'Download CSV file'|translate}">
  </p>
</fieldset>

<fieldset id="iframeWrapper" style="display:none;">
  <legend>{'Preview'|translate}</legend>
  <iframe src="" id="invokeFrame" name="invokeFrame"></iframe>
</fieldset>
</form>
{/if}