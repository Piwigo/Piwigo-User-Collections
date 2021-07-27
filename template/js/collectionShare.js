function sharePopin(animation = true) {
    $.confirm({
        ...jconfirmConfig,
        boxWidth: '760px',
        title: str_share_title,
        content: uc_share_form,
        icon: 'uc-icon-share',
        buttons: {
            formSubmit: {
                text: str_add,
                btnClass: 'btn-uc',
                action: function () {
                    $('#share_form').submit();
                }
            },
            cancel: {
                text: str_close,
            },
        },
        onContentReady: () => {
            $('#share_form input[name="share_deadline"]').datetimepicker({
                dateFormat: 'yy-mm-dd',
                minDate: new Date()
            });

            $('#share_form .uc_opt_param').each((_, node) => {
                let input = $(node).find('input[type="text"]');
                $(node).find('input[type="checkbox"]').on('change', () => {
                    input.val('');
                })
            })
        },
        onOpenBefore: () => {
            if (!uc_share_open) {
                $('.uc_form .infos, .uc_form .alert').hide();
            }
        },
    })
}

$(function () {
    if (uc_share_open) sharePopin();

    $('.share_popin_open').click(() => sharePopin())
})
