<dt>{$block->get_title()}</dt>
<dd>
  <p>{strip}
    {if $block->data.NB_COL == 0}
      {'You have no collection'|@translate}
    {else}
      <a href="{$block->data.U_LIST}">{$pwg->l10n_dec('You have %d collection', 'You have %d collections', $block->data.NB_COL)}</a>
    {/if}
  {/strip}</p>
  {if $block->data.collections}
  <ul>
		{foreach from=$block->data.collections item=col}{strip}
		<li>
      <a href="{$col.u_edit}">{$col.name}</a>&nbsp; 
      <span class="menuInfoCat">[<span class="nbImagesCollec-{$col.id}">{$col.nb_images}</span>]</span>
    </li>
		{/strip}{/foreach}
    {if isset($block->data.MORE)}<li class="menuInfoCat"><a href="{$block->data.U_LIST}">{'%d more...'|@translate|sprintf:$block->data.MORE}</a></li>{/if}
	</ul>
  {/if}
</dd>
