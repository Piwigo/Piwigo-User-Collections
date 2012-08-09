{$MENUBAR}

<div id="content" class="content{if isset($MENUBAR)} contentWithMenu{/if}">

<div class="titrePage">
  {if !empty($COLLECTION_ACTIONS)}
	<ul class="categoryActions">
    {$COLLECTION_ACTIONS}
	</ul>
  {/if}

  <h2>{$TITLE}</h2>
</div>