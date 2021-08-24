$(function () {
    let selector = 'a.preview-box';
    let getSrc = (node) => $(node).data('src');
    let getTitle = (node) => $(node).closest('.wrap1').find('.thumbName').html();
    
    if (user_theme === 'modus') {
        selector = '#thumbnails > li a:not(.addCollection)';
        getSrc = (node) => {
            let initialSrc = $(node).find('img').attr('src');
            let match = [...initialSrc.matchAll(/(upload\S*)-[^._]+.(\S+)/gm)][0];
            return "i.php?/"+match[1]+'-me.'+match[2];
        };
        getTitle = (node) => $(node).parent().find('img').attr('title');
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