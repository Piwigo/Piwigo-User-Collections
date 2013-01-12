{html_style}
.thumbnails .wrap1 {ldelim} position:relative !important; }
.wrap1 .addCollection, .gthumb .addCollection {ldelim} width:100%;height:16px;display:none;position:absolute;top:0;background:rgba(0,0,0,0.8);padding:2px;border-radius:2px;font-size:10px;z-index:100 !important;color:#eee;white-space:nowrap; }
.wrap1:hover .addCollection, .gthumb:hover .addCollection {ldelim} display:block; }
{/html_style}

{if not $NO_AJAX}
{footer_script require='jquery'}
jQuery(".addCollection").click(function() {ldelim}
  var toggle_id = jQuery(this).data("id");
  var $trigger = jQuery(this);
  
  jQuery.ajax({ldelim}
    type: "POST",
    url: "{$ROOT_URL}{$USER_COLLEC_PATH}toggle_image.php",
    data: {ldelim} {if $COL_ID}"col_id": "{$COL_ID}", {/if}"toggle_id": toggle_id }
  }).done(function(msg) {ldelim}
    if (msg == "true") {ldelim}
      $trigger.children(".uc_remove").show();
      $trigger.children(".uc_add").hide();
      jQuery(".nbImagesCollec").html(parseInt(jQuery(".nbImagesCollec").html()) +1);
    } else if (msg == "false") {ldelim}
    {if $COL_ID}
      $trigger.parent(".wrap1, .gthumb").hide("fast", function() {ldelim} $trigger.remove() });
      if (typeof batchdown_count != 'undefined') batchdown_count-=1;
    {else}
      $trigger.children(".uc_remove").hide();
      $trigger.children(".uc_add").show();
    {/if}
    jQuery(".nbImagesCollec").html(parseInt(jQuery(".nbImagesCollec").html()) -1);
    } else {ldelim}
      $trigger.html('{'Un unknown error occured'|@translate}');
    }
  });
  
  return false;
});
{/footer_script}
{/if}