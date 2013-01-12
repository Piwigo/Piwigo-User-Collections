<dt>{$block->get_title()}</dt>
<dd>
  <p>
    {if $block->data.NB_COL == 0}
      {'You have no collection'|@translate}
    {else}
      <a href="{$block->data.U_LIST}">{$pwg->l10n_dec('You have %d collection', 'You have %d collections', $block->data.NB_COL)}</a>
    {/if}</p>
  {if $block->data.collections}
  <ul>{strip}
		{foreach from=$block->data.collections item=col}
		<li>
      <a href="{$col.U_EDIT}" {if $col.active}style="font-weight:bold;"{/if} rel="nofollow">{$col.name}</a>&nbsp; 
      {if $col.active}<i class="menuInfoCat">({'active'|@translate})</i>&nbsp;{/if} 
      <span class="menuInfoCat">[<span {if $col.count_handler}class="nbImagesCollec"{/if}>{$col.nb_images}</span>]</span>
    </li>
		{/foreach}
    {if $block->data.MORE}<li class="menuInfoCat">{'%d more...'|@translate|sprintf:$block->data.MORE}</li>{/if}
	{/strip}</ul>
  {/if}
  <p><a href="{$block->data.U_CREATE}" rel="nofollow">{'Create a new collection'|@translate}</a></p>
</dd>
