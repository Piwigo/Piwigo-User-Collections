{combine_css path=$USER_COLLEC_PATH|cat:'template/style_thumbnails.css'}

{* <!-- all pages but collection edit page --> *}
{if not isset($UC_IN_EDIT)}
{footer_script require='jquery'}

{* add css class to selected items on load, only for bootstrap theme *}
jQuery(".addCollection").each(function() {
    var col_ids = jQuery(this).data('cols');
    if (col_ids != '') {
      jQuery(this).closest('.card.card-thumbnail').addClass('user-collection-selected');
    }
});

var $cdm = jQuery('#collectionsDropdown');

$cdm.on('mouseleave', function() {
  $cdm.hide();
});

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
    
    var first = $cdm.children('.noCollecMsg').length > 0;

    jQuery.ajax({
      type: 'POST',
      dataType: 'json',
      url: '{$ROOT_URL}ws.php?format=json',
      data: {
        method: 'pwg.collections.create',
        name: name,
      },
      success: function(data) {
        if (data.stat == 'ok') {
          var col = data.result,
              html = 
            '<span>&#9733;</span> <a class="add" data-id="'+ col.id +'">'+ col.name +'</a> '
            +'<span class="menuInfoCat">[<span class="nbImagesCollec-'+ col.id +'">'+ col.nb_images +'</span>]</span> '
            +'<a class="remove" data-id="'+ col.id +'">{'(remove)'|translate|escape:javscript}</a>'
            +'<br>';

          $cdm.children('.switchBoxFooter').before(html);
          
          if (first) {
            $cdm.children('.noCollecMsg').remove();
            $cdm.children('.add').trigger('click');
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
  },
  // prevent click propagation
  click: function(e) {
    e.stopPropagation();
  }
});

// add and remove links (delegate for new collections)
$cdm.on('click', '.add, .remove', function(e) {
  var img_id = $cdm.data('img_id'),
      album_id = $cdm.data('album_id'),
      col_id = jQuery(this).data('id'),
      method = 'pwg.collections.' + (album_id ? 'addAlbum' : jQuery(this).hasClass('add') ? 'addImages' : 'removeImages');

  jQuery.ajax({
    type: 'POST',
    dataType: 'json',
    url: '{$ROOT_URL}ws.php?format=json',
    data: {
      method: method,
      col_id: col_id,
      image_ids: img_id,
      album_id: album_id
    },
    success: function(data) {
      if (data.stat == 'ok') {
        // update col counters
        jQuery('.nbImagesCollec-'+ col_id).html(data.result.nb_images);

        // update item datas
        var $target = album_id ? jQuery('.addCollection[data-id]') : jQuery('.addCollection[data-id="'+ img_id +'"]');
        
        $target.each(function() {
          var col_ids = $(this).data('cols');
          
          if (method == 'pwg.collections.removeImages') {
            col_ids.splice(col_ids.indexOf(col_id), 1);
            if (col_ids == ''){
              $(this).closest('.card.card-thumbnail').removeClass('user-collection-selected');
            }
          }
          else if (col_ids.indexOf(col_id) == -1) {
            col_ids[ col_ids.length ] = col_id;
            $(this).closest('.card.card-thumbnail').addClass('user-collection-selected');
          }
          $(this).data('col', col_ids);
        });
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
jQuery(document).on('click', '.addCollection', function(e) {
  var img_id = jQuery(this).data('id'),
      album_id = jQuery(this).data('albumId'),
      col_ids = jQuery(this).data('cols');

  $cdm.data('img_id', img_id);
  $cdm.data('album_id', album_id);

  $cdm.children('.add').each(function() {
    if (col_ids && col_ids.indexOf(jQuery(this).data('id')) != -1) {
      jQuery(this).css('font-weight', 'bold').next().next().show();
    }
    else {
      jQuery(this).css('font-weight', 'normal').next().next().hide();
    }
  });

  {if isset($IN_PICTURE)}
  $cdm.css({
    'left': Math.min(jQuery(this).offset().left, jQuery(window).width()-$cdm.outerWidth(true)-5),
    'top': jQuery(this).offset().top + jQuery(this).outerHeight(true)
  });
  {else}
  $cdm.css({
    'left': Math.min(e.pageX-jQuery(window).scrollLeft()-20, jQuery(window).width()-$cdm.outerWidth(true)-5),
    'top': e.pageY-5-$(window).scrollTop()
  });
  {/if}
  $cdm.toggle();

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
  var $trigger = jQuery(this),
      img_id = jQuery(this).data('id'),
      col_id = {$collection.ID};

  jQuery.ajax({
    type: 'POST',
    dataType: 'json',
    url: '{$ROOT_URL}ws.php?format=json',
    data: {
      method: 'pwg.collections.removeImages',
      col_id: col_id,
      image_ids: img_id
    },
    success: function(data) {
      if (data.stat == 'ok') {
        {* col-outer is for bootstrap themes *}
        $trigger.closest('li, .col-outer').hide('fast', function() {
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