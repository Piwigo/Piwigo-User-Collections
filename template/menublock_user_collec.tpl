<dt>{$block->get_title()}</dt>
<dd>
  <p><a href="{$block->data.U_LIST}">{'You have %d collections'|@translate|sprintf:$block->data.NB_COL}</a></p>
  {if $block->data.collections}
  <ul>{strip}
		{foreach from=$block->data.collections item=col}
		<li>{if $col.active}
      <a href="{$col.U_EDIT}" style="font-weight:bold;" rel="nofollow">{$col.name}</a> <i class="menuInfoCat">({'active'|@translate})</i> <span class="menuInfoCat">[<span class="nbImagesCollec">{$col.nb_images}</span>]</span>
    {else}
      <a href="{$col.U_EDIT}" rel="nofollow">{$col.name}</a> <span class="menuInfoCat">[{$col.nb_images}]</span>
    {/if}</li>
		{/foreach}
    {if $block->data.MORE}<li class="menuInfoCat">{'%d more...'|@translate|sprintf:$block->data.MORE}</li>{/if}
	{/strip}</ul>
  {/if}
  <p><a href="{$block->data.U_CREATE}" rel="nofollow">{'Create a new collection'|@translate}</a></p>
</dd>
