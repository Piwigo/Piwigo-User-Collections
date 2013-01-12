{combine_script id='jquery.colorbox' load='footer' require='jquery' path='themes/default/js/plugins/jquery.colorbox.min.js'}
{combine_css path="themes/default/js/plugins/colorbox/style2/colorbox.css"}

{footer_script}
jQuery("a.preview-box").colorbox({ldelim}
  rel: ".preview-box",
  href: function() {ldelim} return $(this).data('src'); },
  title: function() {ldelim}
    var title = $(this).children("img.thumbnail").attr("alt");
    {if $F_ACTION} title+= ' · <a href="{$collection_toggle_url}&amp;collection_toggle='+ $(this).data('id') +'" class="addCollection" data-id="'+ $(this).data('id') +'">{'Remove from collection'|@translate}</a>';{/if}
    title+= ' · <a href="'+ $(this).attr('href') +'" target="_blank">{'jump to photo'|@translate} →</a>';
    return  title;
  }
});
jQuery(document).on("click", "#cboxTitle .addCollection", function() {ldelim}
  jQuery.colorbox.close();
  jQuery("#thumbnails a.addCollection[data-id='"+ $(this).data('id')+"']").trigger("click");
  return false;
});
{/footer_script}