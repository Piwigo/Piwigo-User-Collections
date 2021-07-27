$(() => {
    jQuery('a.new_col').click(function (e) {
        e.preventDefault();
        let link = $(this); 
        let jc = $.confirm({
            ...jconfirmConfig,
            boxWidth: '360px',
            title: link.attr('title'),
            content: `<form id="add_form" class="uc_form">
                <div class="uc_input_group">
                    <label for="collection_name">` + link.data('label') + `</label>
                    <input type="text" id="collection_name">
                </div>
            </form>
            `,
            icon: 'uc-icon-plus',
            buttons: {
                add: {
                    text: link.data('btn-validate'),
                    btnClass: 'btn-uc',
                    keys: ['enter'],
                    action: function () {
                        let name = $('#add_form #collection_name').val();
                        if (name != "")
                            window.location.replace(link.attr('href') + '&name=' + name);
                        else {
                            jc.highlight();
                            return false;
                        };
                    }
                },
                cancel: {
                    text: link.data('btn-cancel'),
                },
            },
            onContentReady : () => {
                $('#add_form #collection_name').focus();
                $('#add_form').on('submit', (e) => {
                    e.preventDefault();
                    jc.buttons.add.action();
                })
            }
        })
    })
})