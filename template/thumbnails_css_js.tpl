{html_style}
.thumbnails .wrap1 {ldelim} position:relative !important; }
.wrap1 .addCollection {ldelim} width:100%;height:16px;display:none;position:absolute;top:0;background:rgba(0,0,0,0.8);padding:2px;border-radius:2px;font-size:10px;z-index:100;color:#eee;white-space:nowrap; }
.wrap1:hover .addCollection {ldelim} display:block; }
{/html_style}

{footer_script require='jquery'}
jQuery(".addCollection").click(function() {ldelim}
  var toggle_id = jQuery(this).data("id");
  var $trigger = jQuery(this);
  
  jQuery.ajax({ldelim}
    type: "POST",
    url: "{$USER_COLLEC_PATH}toggle_image.php",
    data: {ldelim} {if $COL_ID}"col_id": "{$COL_ID}", {/if}"toggle_id": toggle_id }
  }).done(function(msg) {ldelim}
    if (msg == "true") {ldelim}
      $trigger.html('{'Remove from collection'|@translate}&nbsp;<img src="{$USER_COLLEC_PATH}template/image_delete.png" title="{'Remove from collection'|@translate}">');
      jQuery(".nbImagesCollec").html(parseInt(jQuery(".nbImagesCollec").html()) +1);
    } else if (msg == "false") {ldelim}
    {if $COL_ID}
      $trigger.parent(".wrap1").hide("fast", function() {ldelim} $trigger.remove() });
    {else}
      $trigger.html('{'Add to collection'|@translate}&nbsp;<img src="{$USER_COLLEC_PATH}template/image_add.png" title="{'Add to collection'|@translate}">');
    {/if}
      jQuery(".nbImagesCollec").html(parseInt(jQuery(".nbImagesCollec").html()) -1);
    } else {ldelim}
      $trigger.html('{'Un unknown error occured'|@translate}');
    }
  });
  
  return false;
});
{/footer_script}