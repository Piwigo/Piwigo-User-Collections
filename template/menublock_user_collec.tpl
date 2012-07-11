<dt>{$block->get_title()}</dt>
<dd>
{if $block->data.current}
  {'Current collection:'|@translate} <b>{$block->data.current.NAME}</b>, {'%d photos'|@translate|@sprintf:$block->data.current.NB_IMAGES}
	<ul>{strip}
		{foreach from=$block->data.links item=link}
		<li><a href="{$link.URL}" title="{$link.TITLE}" rel="nofollow">{$link.NAME}</a></li>
		{/foreach}
	{/strip}</ul>
{/if}
  <a href="{$block->data.U_LIST}" rel="nofollow">{'See all my collections'|@translate}</a>
</dd>
