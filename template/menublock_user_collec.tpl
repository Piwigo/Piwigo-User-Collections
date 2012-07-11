<dt>{$block->get_title()}</dt>
<dd>
{if $block->data.current}
  {assign var="nb_images" value='<span class="nbImagesCollec">'|@cat:$block->data.current.NB_IMAGES|@cat:'</span>'}
  <p>{'Current collection:'|@translate} <b>{$block->data.current.NAME}</b>, {'%d photos'|@translate|replace:'%d':'%s'|sprintf:$nb_images}</p>
	<ul>{strip}
		{foreach from=$block->data.links item=link}
		<li><a href="{$link.URL}" title="{$link.TITLE}" rel="nofollow">{$link.NAME}</a></li>
		{/foreach}
	{/strip}</ul>
{/if}
  <p><a href="{$block->data.U_LIST}" rel="nofollow">{'See all my collections'|@translate}</a></p>
</dd>
