{if not isset($UC_NO_LIGHTBOX)}
{combine_script id='jquery.colorbox' load='footer' require='jquery' path='themes/default/js/plugins/jquery.colorbox.min.js'}
{combine_css path='themes/default/js/plugins/colorbox/style2/colorbox.css'}
{/if}

{footer_script}
jQuery("a.preview-box").colorbox({ldelim}
  rel: ".preview-box",
  href: function() {ldelim} return $(this).data('src'); },
  title: function() {ldelim}
    var title = $(this).children("img.thumbnail").attr("alt");
    {if $F_ACTION} title+= ' · <a class="addCollection" data-id="'+ $(this).data('id') +'" rel="nofollow">{'Remove from collection'|@translate|escape:javascript}</a>';{/if}
    title+= ' · <a href="'+ $(this).attr('href') +'" target="_blank">{'jump to photo'|@translate|escape:javascript} →</a>';
    return  title;
  }
});
jQuery(document).on("click", "#cboxTitle .addCollection", function() {ldelim}
  jQuery.colorbox.close();
  jQuery("#thumbnails a.addCollection[data-id='"+ $(this).data('id')+"']").trigger("click");
  return false;
});
{/footer_script}