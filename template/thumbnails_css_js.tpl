{html_style}
#thumbnails li {ldelim} position:relative !important;display:inline-block; }
li .addCollection {ldelim} width:100%;height:16px;display:none;position:absolute;top:0;background:rgba(0,0,0,0.8);padding:2px;border-radius:2px;font-size:10px;z-index:100 !important;color:#eee;white-space:nowrap; }
li:hover .addCollection {ldelim} display:block !important; }
{/html_style}

{if not $NO_AJAX}
{footer_script require='jquery'}
jQuery(".addCollection").click(function() {ldelim}
  var $trigger = jQuery(this);
  var toggle_id = $trigger.data("id");
  var method = $trigger.data("stat");
  
  if (method != "add" && method != "remove") {ldelim}
    $trigger.html("{'Un unknown error occured'|@translate}");
    return false;
  }
  
  jQuery.ajax({ldelim}
    type: "GET",
    dataType: "json",
    url: "{$ROOT_URL}ws.php",
    data: {ldelim} "format": "json", "method": "pwg.collections."+method+"Images", "col_id": {$AJAX_COL_ID}, "image_ids": toggle_id },
    success: function(data) {ldelim}
      if (data['stat'] == 'ok') {ldelim}
        if (method == "add") {ldelim}
          $trigger.children(".uc_remove").show();
          $trigger.children(".uc_add").hide();
          $trigger.data("stat", "remove");
        }
        else if (method == "remove") {ldelim}
        {if $UC_IN_EDIT}
          $trigger.parent(".wrap1, .gthumb").hide("fast", function() {ldelim} $(this).remove() });
          if (typeof batchdown_count != 'undefined') batchdown_count-=1;
        {else}
          $trigger.children(".uc_remove").hide();
          $trigger.children(".uc_add").show();
          $trigger.data("stat", "add");
        {/if}
        }
        
        jQuery(".nbImagesCollec").html(data['result']['nb_images']);
      }
      else {ldelim}
        $trigger.html("{'Un unknown error occured'|@translate}");
      }
    },
    error: function() {ldelim}
      $trigger.html("{'Un unknown error occured'|@translate}");
    }
  });
  
  return false;
});
{/footer_script}
{/if}