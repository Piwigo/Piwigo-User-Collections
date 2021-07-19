{function name=collectionsItem}
{function collectionsItem}
  <a class="collectionsItem collection-item-{$coll_id} add" data-id="{$coll_id}" {if $coll_id =="coll_template"}style="display:none !important"{/if}>
    <i class="uc-icon-star"></i> 
    <span class="collection-name">{$coll_name}</span>
    <span class="menuInfoCat">[<span class="nbImages">{$coll_nb_image}</span>]</span>
    <span class="remove-legend" data-id="{$coll_id}">{'(remove)'|translate}</span>
  </a>
{/function}
{/function}

<div id="collectionsDropdown" class="switchBox">
  <div class="switchBoxTitle">{'Collections'|translate}</div>

  {collectionsItem coll_id="coll_template" coll_name="coll_name" coll_nb_image="coll_nb_image"}

  {foreach from=$COLLECTIONS item=col}
    {collectionsItem 
      coll_id=$col.id 
      coll_name=$col.name 
      coll_nb_image=$col.nb_images
    }
  {foreachelse}
    <span class="noCollecMsg">{'You have no collection'|translate}</span>
  {/foreach}

  <div class="switchBoxFooter">
    <a class="new"><i class="uc-icon-plus"></i> <span>{'Create a new collection'|translate}</span></a>
    <input type="text" class="new" placeholder="{'Name'|translate}" size="25"/>
  </div>
</div>