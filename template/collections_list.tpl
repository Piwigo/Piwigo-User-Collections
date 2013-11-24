{combine_css path=$USER_COLLEC_PATH|cat:'template/style_collections.css'}

{footer_script require='jquery'}
jQuery('.new_col').click(function() {
  var name = prompt('{'Collection name:'|translate|escape:javascript}');
  if (name != null) {
    jQuery(this).attr('href',  jQuery(this).attr('href') +'&name='+ name);
    return true;
  }
  else {
    return false;
  }
});

jQuery('.titrePage h2').append(' [{$COLLECTIONS_COUNT}]');
{/footer_script}


<p style="text-align:left;font-weight:bold;margin:20px;"><a href="{$U_CREATE}" class="new_col">{'Create a new collection'|translate}</a></p>

{if empty($CATEGORIES)}
{'You have no collection'|translate}
{/if}