<form id="share_form" class="uc_form" action="{$F_ACTION}" method="post">
    {include file='infos_errors.tpl' errors=$share.errors infos=$share.infos}
    
    <div class="uc_share_url">
        <span class="url-base">{$U_SHARE}</span><span class="url-more url-normal"></span>
        <input type="text" name="share_key" class="url-edit" size="20" value="{$share.share_key}">
    </div>

    <div class="uc_input_group uc_opt_param">
        <input type="checkbox" name="use_share_password" data-for="share_password" class="share-option" id="use_share_password">
        <label for="use_share_password">{'Password'|translate}</label>
        
        <input type="text" name="share_password" size="25" maxlength="25" value="{$share.password}" placeholder="{'Password'|translate}">
    </div>

    <div class="uc_input_group uc_opt_param">
        <input type="checkbox" name="use_share_deadline" data-for="share_deadline" class="share-option" id="use_share_deadline">
        <label for="use_share_deadline">{'Expiration date'|translate}</label>

        <input type="text" name="share_deadline" size="25" value="{$share.deadline}" placeholder="{'Date'|translate}">
    </div>

    <input type="hidden" name="add_share" value="">
    <input type="hidden" name="key" value="{$UC_TKEY}">
    
{if not empty($collection.SHARES)}
    <table class="shares_list">
        <tr class="header">
        <th>{'Share key'|translate}</th>
        <th>{'Creation date'|translate}</th>
        <th>{'Password'|translate}</th>
        <th>{'Expiration date'|translate}</th>
        <th></th>
        </tr>
    {foreach from=$collection.SHARES item=share}
        <tr class="{cycle values='row2,row1'} {if $share.expired}expired{/if}">
        <td><a href="{$share.url}">{$share.share_key}</a></td>
        <td>{$share.add_date_readable}</td>
        <td>{if $share.params.password}{'Yes'|translate}{else}{'No'|translate}{/if}</td>
        <td>{if $share.params.deadline}{$share.params.deadline_readable}{else}{'No'|translate}{/if}</td>
        <td>
            <a title='{'Delete link'|translate}'
                href="{$share.u_delete}"
                class="uc-confirm-link" 
                data-icon="uc-icon-cancel" 
                data-validate="{'Delete'|translate}"
                data-content="{'Are you sure?'|translate}" 
                data-cancel="{'Cancel'|translate}" 
                rel="nofollow"
            >
            <i class="uc-icon-cancel"></i>
        </td>
        </tr>
    {/foreach}
    </table>
{/if}
</form>