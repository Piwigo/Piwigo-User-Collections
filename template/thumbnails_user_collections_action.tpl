{strip}
<a class="addCollection" data-id="%id%" data-cols="[%collections%]" rel="nofollow">
{if not isset($UC_IN_EDIT)}
    {'Add to collection'|translate}<i class="uc-icon-star-filled"></i>
{else}
    {'Remove from collection'|translate}<i class="uc-icon-star"></i>
{/if}
</a>
{/strip}