{include file=$USER_COLLEC_ABS_PATH|cat:'template/thumbnails_css_js.tpl'}

<a title="{'Add to collection'|translate}" class="pwg-state-default pwg-button addCollection" rel="nofollow"
  data-id="{$current.id}" data-cols="[{$CURRENT_COLLECTIONS}]">
  <span class="pwg-icon user-collections-icon"></span>
  <span class="pwg-button-text">{'Add to collection'|translate}</span>
</a>