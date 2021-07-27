{* This file cannot be extend by a theme ! *}

<div class="collectionActions">
  <a href="{$cat.URL}" rel="nofollow"><i class="uc-icon-folder"></i>{"Open"|@translate}</a>
  | 
  <a title="{'Delete this collection'|translate}" 
    href="{$cat.U_DELETE}"
    class="uc-confirm-link" 
    data-icon="uc-icon-trash" 
    data-validate="{'Delete'|translate}" 
    data-content="{'Are you sure?'|translate}" 
    data-cancel="{'Cancel'|translate}" 
    rel="nofollow"
  ><i class="uc-icon-trash"></i>{"Delete"|@translate}</a>
</div>