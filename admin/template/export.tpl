{combine_css path=$USER_COLLEC_PATH|@cat:"admin/template/style.css"}

{footer_script require='jquery.ui.sortable'}{literal}
$( "#fields_active, #fields_inactive" ).sortable({
  connectWith: ".connectedSortable",
  items: "> li",
  placeholder: "sortable-moving"
}).disableSelection();

$("input[name='generate']").click(function() {
  query = "ws.php?format=rest&method=pwg.collections.getSerialized&col_id={/literal}{$COL_ID}{literal}";
  $("#fields_active li").each(function() {
    query+= '&content[]='+ $(this).data('name');
  });
  
  $("#iframeWrapper").show();
  $("#invokeFrame").attr('src', query);
  
  return false;
});

$("input[name='download']").click(function() {
  $("#fields_active li").each(function() {
    $("#export_form").append('<input type="hidden" name="content[]" value="'+ $(this).data('name') +'"/>');
  });
});
{/literal}{/footer_script}

<div class="titrePage">
	<h2>User Collections</h2>
</div>

{if $COL_ID}
<form method="post" action="" class="properties" id="export_form">
<fieldset>
  <legend>{'Fields'|@translate}</legend>
  <ul id="fields_active" class="connectedSortable">
    <h4>{'Active'|@translate}</h4>
    <li data-name="id"><img src="{$themeconf.admin_icon_dir}/cat_move.png"> id</li>
    <li data-name="name"><img src="{$themeconf.admin_icon_dir}/cat_move.png"> name</li>
    <li data-name="path"><img src="{$themeconf.admin_icon_dir}/cat_move.png"> path</li>
  </ul>
  <ul id="fields_inactive" class="connectedSortable">
    <h4>{'Inactive'|@translate}</h4>
    <li data-name="file"><img src="{$themeconf.admin_icon_dir}/cat_move.png"> file</li>
    <li data-name="url"><img src="{$themeconf.admin_icon_dir}/cat_move.png"> url</li>
  </ul>
  
  <p class="formButtons">
    <input type="submit" name="generate" value="{'Generate'|@translate}">
    <input type="submit" name="download" value="{'Download CSV file'|@translate}">
  </p>    
</fieldset>

<fieldset id="iframeWrapper" style="display:none;">
  <legend>{'Output'|@translate}</legend>
  <iframe src="" id="invokeFrame" name="invokeFrame"></iframe>
</fieldset>
</form>
{/if}