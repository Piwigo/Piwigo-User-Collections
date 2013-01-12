{$MENUBAR}

<div id="content" class="content{if isset($MENUBAR)} contentWithMenu{/if}">

<div class="titrePage">
	<ul class="categoryActions">
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

  {if !empty($COLLECTION_ACTIONS)}
    {$COLLECTION_ACTIONS}
  {/if}
	</ul>

  <h2>{$TITLE}</h2>
</div>