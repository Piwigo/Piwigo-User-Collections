{combine_script id='jquery.colorbox' load='footer' require='jquery' path='themes/default/js/plugins/jquery.colorbox.min.js'}
{combine_css id='colorbox' path='themes/default/js/plugins/colorbox/style2/colorbox.css'}

{footer_script require='jquery'}
jQuery('a.preview-box').colorbox({
  rel: '.preview-box',
  href: function() {
    return jQuery(this).data('src');
  },
  title: function() {
    var title = jQuery(this).children('img.thumbnail').attr("alt");
    {if isset($F_ACTION)} title+= ' · <a class="addCollection" data-id="'+ jQuery(this).data('id') +'" rel="nofollow">{'Remove from collection'|translate|escape:javascript}</a>';{/if}
    title+= ' · <a href="'+ jQuery(this).attr('href') +'" target="_blank">{'jump to photo'|translate|escape:javascript} →</a>';
    return  title;
  }
});
jQuery(document).on('click', '#cboxTitle .addCollection', function() {
  jQuery.colorbox.close();
  jQuery('#thumbnails a.addCollection[data-id="'+ jQuery(this).data('id') +'"]').trigger('click');
  return false;
});
{/footer_script}