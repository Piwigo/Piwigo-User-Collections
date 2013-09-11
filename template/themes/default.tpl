{$MENUBAR}

<div id="content" class="content{if isset($MENUBAR)} contentWithMenu{/if}">

<div class="titrePage">
	<ul class="categoryActions">
  {if !empty($image_orders)}
		<li>{strip}<a id="sortOrderLink" title="{'Sort order'|@translate}" class="pwg-state-default pwg-button" rel="nofollow">
			<span class="pwg-icon pwg-icon-sort">&nbsp;</span><span class="pwg-button-text">{'Sort order'|@translate}</span>
		</a>
		<div id="sortOrderBox" class="switchBox">
			<div class="switchBoxTitle">{'Sort order'|@translate}</div>
			{foreach from=$image_orders item=image_order name=loop}{if !$smarty.foreach.loop.first}<br>{/if}
			{if $image_order.SELECTED}
			<span>&#x2714; </span>{$image_order.DISPLAY}
			{else}
			<span style="visibility:hidden">&#x2714; </span><a href="{$image_order.URL}" rel="nofollow">{$image_order.DISPLAY}</a>
			{/if}
			{/foreach}
		</div>
		{footer_script require='jquery'}{literal}
    jQuery("#sortOrderLink").click(function() {
      var elt = jQuery("#sortOrderBox");
      elt.css("left", Math.min( jQuery(this).offset().left, jQuery(window).width() - elt.outerWidth(true) - 5))
        .css("top", jQuery(this).offset().top + jQuery(this).outerHeight(true))
        .toggle();
    });
    jQuery("#sortOrderBox").on("mouseleave", function() {
      jQuery(this).hide();
    });
		{/literal}{/footer_script}
		{/strip}</li>
  {/if}
  {if !empty($image_derivatives)}
		<li>{strip}<a id="derivativeSwitchLink" title="{'Photo sizes'|@translate}" class="pwg-state-default pwg-button" rel="nofollow">
			<span class="pwg-icon pwg-icon-sizes">&nbsp;</span><span class="pwg-button-text">{'Photo sizes'|@translate}</span>
		</a>
		<div id="derivativeSwitchBox" class="switchBox">
			<div class="switchBoxTitle">{'Photo sizes'|@translate}</div>
			{foreach from=$image_derivatives item=image_derivative name=loop}{if !$smarty.foreach.loop.first}<br>{/if}
			{if $image_derivative.SELECTED}
			<span>&#x2714; </span>{$image_derivative.DISPLAY}
			{else}
			<span style="visibility:hidden">&#x2714; </span><a href="{$image_derivative.URL}" rel="nofollow">{$image_derivative.DISPLAY}</a>
			{/if}
			{/foreach}
		</div>
		{footer_script require='jquery'}{literal}
    jQuery("#derivativeSwitchLink").click(function() {
      var elt = jQuery("#derivativeSwitchBox");
      elt.css("left", Math.min( jQuery(this).offset().left, jQuery(window).width() - elt.outerWidth(true) - 5))
        .css("top", jQuery(this).offset().top + jQuery(this).outerHeight(true))
        .toggle();
    });
    jQuery("#derivativeSwitchBox").on("mouseleave", function() {
      jQuery(this).hide();
    });
		{/literal}{/footer_script}
		{/strip}</li>
  {/if}
  {if isset($U_CADDIE)}
		<li><a href="{$U_CADDIE}" title="{'Add to caddie'|@translate}" class="pwg-state-default pwg-button">
			<span class="pwg-icon pwg-icon-caddie-add">&nbsp;</span><span class="pwg-button-text">{'Caddie'|@translate}</span>
		</a></li>
  {/if}
  
  {if $U_SHARE}
    <li><a href="#share_form" title="{'Share this collection'|@translate}" class="share_colorbox_open pwg-state-default pwg-button" rel="nofollow">
          <span class="pwg-icon user-collections-share-icon" style="background:url('{$ROOT_URL}{$USER_COLLEC_PATH}template/resources/share.png') center center no-repeat;">&nbsp;</span><span class="pwg-button-text">{'Share'|@translate}</span>
    </a></li>
  {/if}
  {if $U_MAIL}
    <li><a href="#mail_form" title="{'Send this collection my mail'|@translate}" class="mail_colorbox_open pwg-state-default pwg-button" rel="nofollow">
          <span class="pwg-icon user-collections-mail-icon" style="background:url('{$ROOT_URL}{$USER_COLLEC_PATH}template/resources/mail.png') center center no-repeat;">&nbsp;</span><span class="pwg-button-text">{'Send'|@translate}</span>
    </a></li>
  {/if}
  {if $U_CLEAR}
    <li><a href="{$U_CLEAR}" title="{'Clear this collection'|@translate}" class="pwg-state-default pwg-button" rel="nofollow" onClick="return confirm('{'Are you sure?'|@translate|escape:javascript}');">
      <span class="pwg-icon user-collections-clear-icon" style="background:url('{$ROOT_URL}{$USER_COLLEC_PATH}template/resources/bin.png') center center no-repeat;">&nbsp;</span><span class="pwg-button-text">{'Clear'|@translate}</span>
    </a></li>
  {/if}
  {if $U_DELETE}
    <li><a href="{$U_DELETE}" title="{'Delete this collection'|@translate}" class="pwg-state-default pwg-button" rel="nofollow" onClick="return confirm('{'Are you sure?'|@translate|escape:javascript}');">
      <span class="pwg-icon user-collections-delete-icon" style="background:url('{$ROOT_URL}{$USER_COLLEC_PATH}template/resources/delete.png') center center no-repeat;">&nbsp;</span><span class="pwg-button-text">{'Delete'|@translate}</span>
    </a></li>
  {/if}

  {if !empty($COLLECTION_ACTIONS)}
    {$COLLECTION_ACTIONS}
  {/if}
	</ul>

  <h2>{$TITLE}</h2>
</div>