function mailPopin() {
    $.confirm({
        ...jconfirmConfig,
        boxWidth: '560px',
        title: str_mail_title,
        content: uc_mail_form,
        icon: 'uc-icon-mail-alt',
        buttons: {
            formSubmit: {
                text: str_send,
                btnClass: 'btn-uc',
                action: function () {
                    $('#mail_form').submit();
                }
            },
            cancel: {
                text: str_cancel,
            },
        },
        onContentReady: () => {
            $('#mail_form input[name=to]').on('change', function () {
                $('.recipient-input').toggle(jQuery(this).val() == 'email');
            });
        },
        onOpenBefore: () => {
            if (!uc_mail_open) {
                $('.uc_form .infos, .uc_form .alert').hide();
            }
        },
    })
}

$(function () {
    if (uc_mail_open) mailPopin();

    $('.mail_popin_open').click(() => mailPopin());
})