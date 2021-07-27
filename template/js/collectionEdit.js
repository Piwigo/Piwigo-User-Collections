function editPopin() {
    $.confirm({
        ...jconfirmConfig,
        boxWidth: '560px',
        title: str_edit_col,
        content: uc_edit_form,
        icon: 'uc-icon-pencil',
        buttons: {
            formSubmit: {
                text: str_save,
                btnClass: 'btn-uc',
                action: function () {
                    $('#edit_form').submit();
                }
            },
            cancel: {
                text: str_cancel,
            },
        },
    })
}

$(function () {
    $('.edit_popin_open').click(() => editPopin());
})