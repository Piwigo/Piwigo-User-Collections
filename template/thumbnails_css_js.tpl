{combine_css path=$USER_COLLEC_PATH|cat:'template/style_thumbnails.css'}

{* <!-- all pages but collection edit page --> *}
{if not $UC_IN_EDIT}
{footer_script require='jquery'}
var $cdm = jQuery("#collectionsDropdown");

{if not $IN_PICTURE}
$cdm.on("mouseleave", function() {ldelim}
  $cdm.hide();
});
{/if}

// click on "create collection" button
$cdm.find("a.new").on("click", function(event) {ldelim}
  jQuery(this).hide().next().show().focus();
  event.stopPropagation();
  return false;
});

// events on "new collection" input
$cdm.find("input.new").on({ldelim}
  // ENTER pressed
  "keyup": function(event) {ldelim}
    if (event.which != 13) return;
    
    jQuery(this).hide().prev().show();
    var name = jQuery(this).val();
    jQuery(this).val("{'Name'|@translate}");
    
    if (name == "" || name == null) return;
    
    jQuery.ajax({ldelim}
      type: "GET",
      dataType: "json",
      url: "{$ROOT_URL}ws.php",
      data: {ldelim}
        format: "json",
        method: "pwg.collections.create",
        name: name,
      },
      success: function(data) {ldelim}
        if (data.stat == 'ok') {ldelim}
          var col = data.result;
          var html = '<span>&#9733;</span> <a class="add" data-id="'+ col.id +'">'+ col.name +'</a> '
            +'<span class="menuInfoCat">[<span class="nbImagesCollec-'+ col.id +'">'+ col.nb_images +'</span>]</span> '
            +'<a class="remove" data-id="'+ col.id +'">{'(remove)'|@translate}</a>'
            +'<br>';
          
          $cdm.children(".switchBoxFooter").before(html);
          $cdm.children(".noCollecMsg").remove();
        }
        else {ldelim}
          alert(data.message);
        }
      },
      error: function() {ldelim}
        alert("{'Un unknown error occured'|@translate}");
      }
    });
    
    return false;
  },
  // prevent click propagation
  "click": function(event) {ldelim}
    event.stopPropagation();
  },
  // remove help on focus
  "focus": function() {ldelim}
    if (jQuery(this).val() == "{'Name'|@translate}") jQuery(this).val("");
  },
  // restore help on blur
  "blur" : function() {ldelim}
    if (jQuery(this).val() == "") jQuery(this).val("{'Name'|@translate}");
  }
});

// add and remove links (delegate for new collections)
$cdm.on("click", ".add, .remove", function() {ldelim}
  var img_id = $cdm.data("img_id");
  var col_id = jQuery(this).data("id");
  var method = jQuery(this).hasClass("add") ? "pwg.collections.addImages" : "pwg.collections.removeImages";
  
  jQuery.ajax({ldelim}
    type: "GET",
    dataType: "json",
    url: "{$ROOT_URL}ws.php",
    data: {ldelim}
      format: "json",
      method: method,
      col_id: col_id,
      image_ids: img_id
    },
    success: function(data) {ldelim}
      if (data.stat == 'ok') {ldelim}
        // update col counters
        jQuery(".nbImagesCollec-"+col_id).html(data.result.nb_images);
        
        // update item datas
        var $target = jQuery(".addCollection[data-id='"+ img_id +"']");
        var col_ids = $target.data("cols");
        if (method == "pwg.collections.addImages" && col_ids.indexOf(col_id) == -1)
          col_ids[ col_ids.length ] = col_id;
        else if (method == "pwg.collections.removeImages")
          col_ids.splice(col_ids.indexOf(col_id), 1);
        $target.data("col", col_ids);
      }
      else {ldelim}
        alert(data.message);
      }
    },
    error: function() {ldelim}
      alert("{'Un unknown error occured'|@translate}");
    }
  });
  
  $cdm.hide();
  return false;
});

// main button, open the menu
jQuery(".addCollection").on("click", function(event) {ldelim}
  var img_id = jQuery(this).data("id");
  var col_ids = jQuery(this).data("cols");
  
  $cdm.data("img_id", img_id);
  
  $cdm.children(".add").each(function() {ldelim}   
    if (col_ids.indexOf($(this).data("id")) != -1) {ldelim}
      $(this).css("font-weight", "bold").next().next().show();
    }
    else {ldelim}
      $(this).css("font-weight", "normal").next().next().hide();
    }
  });
  
  {if not $IN_PICTURE}
  $cdm.css({ldelim}
    "top": event.pageY-5-$(window).scrollTop(),
    "left": Math.min(event.pageX-jQuery(window).scrollLeft()-20, jQuery(window).width()-$cdm.outerWidth(true)-5) 
  });
  $cdm.show();
  {/if}
  
  return false;
});

// try to respect theme colors
$cdm.children(".switchBoxFooter").css("border-top-color", $cdm.children(".switchBoxTitle").css("border-bottom-color"));
{/footer_script}

<div id="collectionsDropdown" class="switchBox">
  <div class="switchBoxTitle">{'Collections'|@translate}</div>
  
  {foreach from=$COLLECTIONS item=col}
    <span>&#9733;</span> <a class="add" data-id="{$col.id}">{$col.name}</a>
    <span class="menuInfoCat">[<span class="nbImagesCollec-{$col.id}">{$col.nb_images}</span>]</span>
    <a class="remove" data-id="{$col.id}">{'(remove)'|@translate}</a>
    <br>
  {foreachelse}
    <span class="noCollecMsg">{'You have no collection'|@translate}</span>
  {/foreach}
  
  <div class="switchBoxFooter">
  <span>&#10010;</span> <a class="new">{'Create a new collection'|@translate}</a>
  <input class="new" value="{'Name'|@translate}" size="25"/>
  </div>
</div>

{* <!-- collection edit page --> *}
{else}
{footer_script require='jquery'}
jQuery(".addCollection").on("click", function(event) {ldelim}
  var $trigger = jQuery(this);
  var img_id = jQuery(this).data("id");
  var col_id = {$collection.ID};
  
  jQuery.ajax({ldelim}
    type: "GET",
    dataType: "json",
    url: "{$ROOT_URL}ws.php",
    data: {ldelim}
      format: "json",
      method: "pwg.collections.removeImages",
      col_id: col_id,
      image_ids: img_id
    },
    success: function(data) {ldelim}
      if (data.stat == 'ok') {ldelim}
        $trigger.parent("li").hide("fast", function() {ldelim}
          jQuery(this).remove();
          if (typeof GThumb != "undefined") GThumb.build();
        });
        
        jQuery(".nbImagesCollec-"+col_id).html(data.result.nb_images);
        if (typeof batchdown_count != 'undefined') batchdown_count = data.result.nb_images;
      }
      else {ldelim}
        alert(data.message);
      }
    },
    error: function() {ldelim}
      alert("{'Un unknown error occured'|@translate}");
    }
  });
  
  event.stopPropagation();
  event.preventDefault();
  return false;
});
{/footer_script}
{/if}