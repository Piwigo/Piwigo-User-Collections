$(function () {
    let selector = 'a.preview-box';
    let getSrc = (node) => $(node).data('src');
    let getTitle = (node) => $(node).closest('.wrap1').find('.thumbName').html();
    
    if (user_theme === 'modus') {
        selector = '#thumbnails > li > a:not(.addCollection)';
        getSrc = (node) => $(node).find('img').attr('src');
        getTitle = (node) => $(node).parent().find('.overDesc').html();
    } else if (user_theme === 'bootstrap_darkroom') {
        getTitle = (node) => $(node).closest('.card-thumbnail').find('.card-title').html();
    }

    jQuery(selector).colorbox({
        rel: '.preview-box',
        href: function () {
            return getSrc(this);
        },
        title: function () {
            var title = getTitle(this);
            if (uc_remove_action)
                title += ' · <a class="addCollection" data-id="' + jQuery(this).data('id') + '" rel="nofollow">' + str_remove_from_col + '</a>';
            title += ' · <a href="' + jQuery(this).attr('href') + '" target="_blank">' + str_jump_to_photo + ' →</a>';
            return title;
        }
    });
    jQuery(document).on('click', '#cboxTitle .addCollection', function () {
        jQuery.colorbox.close();
        jQuery('#thumbnails a.addCollection[data-id="' + jQuery(this).data('id') + '"]').trigger('click');
        return false;
    });
})