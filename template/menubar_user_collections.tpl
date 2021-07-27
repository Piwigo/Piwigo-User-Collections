<style>
  .uc-menu-badge {
    display: inline-flex;
    background: #0000004d;
    min-width: 2em;
    border-radius: 1em;
    font-size: 90%;
    font-weight: bold;
    color: white;
    justify-content: center;
  }
</style>

{function name=collectionsMenuItem}
{function collectionsMenuItem}
  <li class="mbUCItem-{$coll_id}" data-id="{$coll_id}" {if $coll_id == "coll_template"}style="display:none"{/if}>
    <a href="{$coll_edit}">{$coll_name}</a>&nbsp;
    <span class="uc-menu-badge nbImagesCollec-{$coll_id}">{$coll_nb_images}</span>
  </li>
{/function}
{/function}

<dt>{$block->get_title()}</dt>
<dd>
  <p>{strip}
    {if $block->data.NB_COL == 0}
      {'You have no collection'|translate}
    {else}
      <a href="{$block->data.U_LIST}">{$pwg->l10n_dec('You have %d collection', 'You have %d collections', $block->data.NB_COL)}</a>
    {/if}
  {/strip}</p>
  {collectionsMenuItem
      coll_id = "coll_template" 
      coll_name = "coll_name"
      coll_edit = "coll_edit"
      coll_nb_images = "coll_nb_images"
    }
  <ul>
  {if $block->data.collections}
		{foreach from=$block->data.collections item=col}{strip}
      {collectionsMenuItem
        coll_id = $col.id
        coll_name = $col.name
        coll_edit = $col.u_edit
        coll_nb_images = $col.nb_images
      }
		{/strip}{/foreach}
    {if isset($block->data.MORE)}<li class="menuInfoCat"><a href="{$block->data.U_LIST}">{'%d more...'|translate:$block->data.MORE}</a></li>{/if}
  {/if}
	</ul>
</dd>
