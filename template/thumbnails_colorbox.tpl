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
    if (uc_remove_action)
      title+= ' · <a class="addCollection" data-id="'+ jQuery(this).data('id') +'" rel="nofollow">' + str_remove_from_col + '</a>';
    title+= ' · <a href="'+ jQuery(this).attr('href') +'" target="_blank">' + str_jump_to_photo + ' →</a>';
    return  title;
  }
});
jQuery(document).on('click', '#cboxTitle .addCollection', function() {
  jQuery.colorbox.close();
  jQuery('#thumbnails a.addCollection[data-id="'+ jQuery(this).data('id') +'"]').trigger('click');
  return false;
});
{/footer_script}