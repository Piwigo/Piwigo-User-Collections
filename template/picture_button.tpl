{include file=$USER_COLLEC_ABS_PATH|cat:'template/thumbnails_css_js.tpl'}

{footer_script require='jquery'}
jQuery(function() {
  jQuery("#collectionsLink").click(function() {
	  var elt = jQuery("#collectionsDropdown");

    elt.css("left", Math.min( jQuery(this).offset().left, jQuery(window).width() - elt.outerWidth(true) - 5))
      .css("top", jQuery(this).offset().top + jQuery(this).outerHeight(true))
      .toggle();

    return false;
  });

  jQuery("#collectionsDropdown").on("mouseleave", function() {
    jQuery(this).hide();
  });
});
{/footer_script}

<a id="collectionsLink" title="{'Add to collection'|translate}" class="pwg-state-default pwg-button addCollection" rel="nofollow" data-id="{$current.id}" data-cols="[{$CURRENT_COLLECTIONS}]">
  <span class="pwg-icon" style="background:url('{$ROOT_URL}{$USER_COLLEC_PATH}template/resources/image_add.png') center center no-repeat;"></span>
  <span class="pwg-button-text">{'Add to collection'|translate}</span>
</a>