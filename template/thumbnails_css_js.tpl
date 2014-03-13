{combine_css path=$USER_COLLEC_PATH|cat:'template/style_thumbnails.css'}

{* <!-- all pages but collection edit page --> *}
{if not $UC_IN_EDIT}
{footer_script require='jquery'}
var $cdm = jQuery('#collectionsDropdown');

{if not $IN_PICTURE}
$cdm.on('mouseleave', function() {
  $cdm.hide();
});
{/if}

// click on "create collection" button
$cdm.find('a.new').on('click', function(e) {
  jQuery(this).hide().next().show().focus();
  e.stopPropagation();
  e.preventDefault();
});

// events on "new collection" input
$cdm.find('input.new').on({
  // ENTER or ESC pressed
  keyup: function(e) {
    if (e.which == 27) {
      jQuery(this).val('').hide().prev().show();
      return;
    }

    if (e.which != 13) {
      return;
    }

    jQuery(this).hide().prev().show();
    var name = jQuery(this).val();
    jQuery(this).val('');

    if (name == '' || name == null) {
      return;
    }

    jQuery.ajax({
      type: 'GET',
      dataType: 'json',
      url: '{$ROOT_URL}ws.php',
      data: {
        format: 'json',
        method: 'pwg.collections.create',
        name: name,
      },
      success: function(data) {
        if (data.stat == 'ok') {
          var col = data.result;
          var html = '<span>&#9733;</span> <a class="add" data-id="'+ col.id +'">'+ col.name +'</a> '
            +'<span class="menuInfoCat">[<span class="nbImagesCollec-'+ col.id +'">'+ col.nb_images +'</span>]</span> '
            +'<a class="remove" data-id="'+ col.id +'">{'(remove)'|translate|escape:javscript}</a>'
            +'<br>';

          $cdm.children('.switchBoxFooter').before(html);
          $cdm.children('.noCollecMsg').remove();
        }
        else {
          alert(data.message);
        }
      },
      error: function() {
        alert('{'An unknown error occured'|translate|escape:javascript}');
      }
    });
  },
  // prevent click propagation
  click: function(e) {
    e.stopPropagation();
  }
});

// add and remove links (delegate for new collections)
$cdm.on('click', '.add, .remove', function(e) {
  var img_id = $cdm.data('img_id');
  var col_id = jQuery(this).data('id');
  var method = jQuery(this).hasClass('add') ? 'pwg.collections.addImages' : 'pwg.collections.removeImages';

  jQuery.ajax({
    type: 'GET',
    dataType: 'json',
    url: '{$ROOT_URL}ws.php',
    data: {
      format: 'json',
      method: method,
      col_id: col_id,
      image_ids: img_id
    },
    success: function(data) {
      if (data.stat == 'ok') {
        // update col counters
        jQuery('.nbImagesCollec-'+ col_id).html(data.result.nb_images);

        // update item datas
        var $target = jQuery('.addCollection[data-id="'+ img_id +'"]');
        var col_ids = $target.data('cols');
        if (method == 'pwg.collections.addImages' && col_ids.indexOf(col_id) == -1) {
          col_ids[ col_ids.length ] = col_id;
        }
        else if (method == 'pwg.collections.removeImages') {
          col_ids.splice(col_ids.indexOf(col_id), 1);
        }
        $target.data('col', col_ids);
      }
      else {
        alert(data.message);
      }
    },
    error: function() {
      alert('{'An unknown error occured'|translate|escape:javascript}');
    }
  });

  $cdm.hide();
  e.preventDefault();
});

// main button, open the menu
jQuery('#thumbnails').on('click', '.addCollection', function(e) {
  var img_id = jQuery(this).data('id');
  var col_ids = jQuery(this).data('cols');

  $cdm.data('img_id', img_id);

  $cdm.children('.add').each(function() {
    if (col_ids.indexOf($(this).data('id')) != -1) {
      $(this).css('font-weight', 'bold').next().next().show();
    }
    else {
      $(this).css('font-weight', 'normal').next().next().hide();
    }
  });

  {if not $IN_PICTURE}
  $cdm.css({
    'top': e.pageY-5-$(window).scrollTop(),
    'left': Math.min(e.pageX-jQuery(window).scrollLeft()-20, jQuery(window).width()-$cdm.outerWidth(true)-5)
  });
  $cdm.show();
  {/if}

  e.preventDefault();
});

// try to respect theme colors
$cdm.children('.switchBoxFooter').css('border-top-color', $cdm.children('.switchBoxTitle').css('border-bottom-color'));
{/footer_script}

<div id="collectionsDropdown" class="switchBox">
  <div class="switchBoxTitle">{'Collections'|translate}</div>

  {foreach from=$COLLECTIONS item=col}
    <span>&#9733;</span> <a class="add" data-id="{$col.id}">{$col.name}</a>
    <span class="menuInfoCat">[<span class="nbImagesCollec-{$col.id}">{$col.nb_images}</span>]</span>
    <a class="remove" data-id="{$col.id}">{'(remove)'|translate}</a>
    <br>
  {foreachelse}
    <span class="noCollecMsg">{'You have no collection'|translate}</span>
  {/foreach}

  <div class="switchBoxFooter">
  <span>&#10010;</span> <a class="new">{'Create a new collection'|translate}</a>
  <input type="text" class="new" placeholder="{'Name'|translate}" size="25"/>
  </div>
</div>

{* <!-- collection edit page --> *}
{else}
{footer_script require='jquery'}
jQuery('#thumbnails').on('click', '.addCollection', function(e) {
  var $trigger = jQuery(this);
  var img_id = jQuery(this).data('id');
  var col_id = {$collection.ID};

  jQuery.ajax({
    type: 'GET',
    dataType: 'json',
    url: '{$ROOT_URL}ws.php',
    data: {
      format: 'json',
      method: 'pwg.collections.removeImages',
      col_id: col_id,
      image_ids: img_id
    },
    success: function(data) {
      if (data.stat == 'ok') {
        $trigger.parent('li').hide('fast', function() {
          jQuery(this).remove();
          if (typeof GThumb != 'undefined') {
            GThumb.build();
          }
        });

        jQuery('.nbImagesCollec-'+ col_id).html(data.result.nb_images);
        if (typeof batchdown_count != 'undefined') {
          batchdown_count = data.result.nb_images;
        }
      }
      else {
        alert(data.message);
      }
    },
    error: function() {
      alert('{'An unknown error occured'|translate|escape:javascript}');
    }
  });

  e.stopPropagation();
  e.preventDefault();
});
{/footer_script}
{/if}