<form id="mail_form" class="uc_form" action="{$F_ACTION}" method="post">
    {include file='infos_errors.tpl' errors=$share.errors infos=$share.infos}

    {if $UC_CONFIG.allow_send_admin && !$UC_CONFIG.allow_mails}
    <div>
        <span>{'To'|translate} {'All Administrators'|translate}</span>
    </div>
    {/if}

    <div {if !$UC_CONFIG.allow_send_admin || !$UC_CONFIG.allow_mails}style="display:none"{/if} class="uc_radio uc_input_group">
        <span>{'To'|translate}</span>
        <input type="radio" name="to" value="email" id="to_other" checked>
        <label for="to_other">{'Someone else'|translate}</label>

        <input type="radio" name="to" value="admin" id="to_admin">
        <label for="to_admin">{'All Administrators'|translate}</label>
    </div>

    <div class="recipient-input uc_input_group">
        <label for="recipient_name">{'Recipient name'|translate}</label>
        <input type="text" name="recipient_name" id="recipient_name" size="40" value="{$contact.recipient_name}">
    </div>

    <div class="recipient-input uc_input_group">
        <label for="recipient_email">{'Recipient e-mail'|translate}</label>
        <input type="text" name="recipient_email" id="recipient_email" size="40" value="{$contact.recipient_email}">
    </div>

    <div class="uc_input_group">
        <label for="message">{'Message'|translate}</label>
        <textarea name="message" id="message" rows="3">{$contact.message}</textarea>
    </div>

    <input type="hidden" name="key" value="{$UC_TKEY}">
    <input type="hidden" name="nb_images" value="4">
    <input type="hidden" name="send_mail" value="">
    
</form>
