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
      <a href="{$col.u_edit}" rel="nofollow">{$col.name}</a>&nbsp; 
      <span class="menuInfoCat">[<span class="nbImagesCollec-{$col.id}">{$col.nb_images}</span>]</span>
    </li>
		{/foreach}
    {if isset($block->data.MORE)}<li class="menuInfoCat"><a href="{$block->data.U_LIST}">{'%d more...'|@translate|sprintf:$block->data.MORE}</a></li>{/if}
	{/strip}</ul>
  {/if}
  <p><a href="{$block->data.U_CREATE}" rel="nofollow">{'Create a new collection'|@translate}</a></p>
</dd>
