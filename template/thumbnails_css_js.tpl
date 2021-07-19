{combine_css path=$USER_COLLEC_PATH|cat:'template/style_thumbnails.css'}
{combine_css path=$USER_COLLEC_PATH|cat:'template/fontello/css/fontello.css'}

{footer_script require='jquery'}

{* Theme variable *}

let thumbnailsActions = 'ul#thumbnails li';
let findThumbnailToHide = 'li';
let thumbnailAction = '#thumbnails .addCollection';
let collectionMenuNbImages = '.menuInfoCat .nbImagesCollec-%id%';
let collectionMenuTemplate = '.mbUCItem-coll_template'
let mbUserCollection = '#mbUserCollection ul';

{if ($USER_THEME=='bootstrap_darkroom')}

thumbnailsActions = '#thumbnails .card';
findThumbnailToHide = '.col-outer';
thumbnailAction = '#thumbnails .addCollection';
collectionMenuTemplate = '#menu-info-coll-coll_template';
collectionMenuNbImages = "#menu-info-coll-%id% .badge";
mbUserCollection = '#menu-info-coll-container';

{/if}

{* Add Actions on thumbnails *}
let thumbnails = $(thumbnailsActions);

let collection_image = JSON.parse('{json_encode($IMAGES_COLLECTIONS)}');


thumbnails.each((index, elem) => {
  let action_str = '{$UC_THUMBNAIL_ACTION}';
  
  let match = /picture\.php\?\/(\d+)/.exec($(elem).find('a').attr('href'));

  if (match) {
    let id = match[1];
    action_str = action_str.replace('%id%', id);
    if (collection_image && collection_image[id])
      action_str = action_str.replace('%collections%', collection_image[id])
    else 
      action_str = action_str.replace('%collections%', '');
  }

  $(elem).prepend($(action_str));
})
{/footer_script}

{* <!-- all pages but collection edit page --> *}
{if not isset($UC_IN_EDIT)}
{footer_script require='jquery'}
var $cdm = jQuery('#collectionsDropdown');

$cdm.on('mouseleave', function() {
  $cdm.hide();
});

// click on "create collection" button
$cdm.find('a.new').on('click', function(e) {
  $(this).find('span').hide();
  $cdm.find('input.new').show().focus();
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

    jQuery(this).hide();
    $cdm.find('a.new span').show();

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
          var col = data.result;
          {* Adding Collection on the tooltip *}
          if ($(".collection-item-coll_template").length > 0) {
            let htmlTT = $(".collection-item-coll_template")[0].outerHTML
              .replaceAll('coll_template', col.id)
              .replaceAll('coll_name', col.name)
              .replaceAll('coll_nb_image', col.nb_images);
            
            let nodeTT = $(htmlTT).attr('style', '');
            $cdm.children('.switchBoxFooter').before(nodeTT);
          }

          {* Adding Collection on the menu *}
          if ($(collectionMenuTemplate).length > 0) {
            let htmlMenu = $(collectionMenuTemplate)[0].outerHTML
              .replaceAll('coll_template', col.id)
              .replaceAll('coll_edit', 'index.php?/collections/edit/'+col.id)
              .replaceAll('coll_name', col.name)
              .replaceAll('coll_nb_images', col.nb_images);
            
            let nodeMenu = $(htmlMenu).attr('style', '');

            $(mbUserCollection).append(nodeMenu);
          }
          
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
        jQuery('.collection-item-'+ col_id + ' .nbImages').html(data.result.nb_images);

        $(collectionMenuNbImages.replace('%id%', col_id)).html(data.result.nb_images);

        // update item datas
        var $target = album_id ? jQuery('.addCollection[data-id]') : jQuery('.addCollection[data-id="'+ img_id +'"]');
        
        $target.each(function() {
          var col_ids = $(this).data('cols');
          
          if (method == 'pwg.collections.removeImages') {
            col_ids.splice(col_ids.indexOf(col_id), 1);
          }
          else if (col_ids.indexOf(col_id) == -1) {
            col_ids[ col_ids.length ] = col_id;
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

  if (album_id) { 
    $cdm.data('album_id', album_id);

    {* Get all collections of images and make an intersection *}
    let img_col_id = [];

    $(thumbnailAction).each((index, node) => {
      img_col_id.push($(node).data('cols'));
    });

    if (img_col_id.length > 0) {
      col_ids = img_col_id[0];
      for (let i = 1; i<img_col_id.length; i++) {
        col_ids = col_ids.filter(n => img_col_id[i].find(x => n === x));
      }
    }
  } else 
    $cdm.data('album_id', '');

  $cdm.find('.collectionsItem').each(function(index, node) {
    let item = $(node);
    
    if (col_ids && col_ids.indexOf(item.data('id')) != -1) {
      item.find('.remove-legend').show();
      item.removeClass('add').addClass('remove');
      item.find('.collection-name').css('font-weight', 'bold');
      item.find('i').attr('class', 'uc-icon-star-filled');
    }
    else {
      item.removeClass('remove').addClass('add');
      item.find('.remove-legend').hide();
      item.find('.collection-name').css('font-weight', 'normal');
      item.find('i').attr('class', 'uc-icon-star');
    }
    
    if (album_id) {
      item.removeClass('remove').addClass('add');
      item.find('.remove-legend').hide();
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

{* <!-- collection edit page --> *}
{else}
{footer_script require='jquery'}
jQuery(thumbnailAction).on('click', function(e) {
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
        
        if ($(thumbnailsActions).length == 1) {
          location.reload()
        }

        $trigger.closest(findThumbnailToHide).hide('fast', function() {
          jQuery(this).remove();
          if (typeof GThumb != 'undefined') {
            GThumb.build();
          }
        });


        $(collectionMenuNbImages.replace('%id%', col_id)).html(data.result.nb_images);

        jQuery('.collection-item-'+ col_id + ' .nbImages').html(data.result.nb_images);
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