jQuery(document).ready(function () {
    // Add Actions on thumbnails
    let thumbnails = $(thumbnailsActions);

    thumbnails.each((index, elem) => {

        let id = null;

        if ($(elem).find('a').attr('data-id')) {
            id = $(elem).find('a').attr('data-id');
        } else { //For Modus, the prefilter that add the "data-id" don't work
            let match = /\?\/(\d+)/.exec($(elem).find('a').attr('href'));
            
            if (match) {
                id = match[1]
            }
        }


        let nodeAction = htmlThumbnailAction;

        if (id) {
            nodeAction = nodeAction.replace('%id%', id);
            if (collectionImage && collectionImage[id])
                nodeAction = nodeAction.replace('%collections%', collectionImage[id])
            else
                nodeAction = nodeAction.replace('%collections%', '');
        }

        $(elem).prepend($(nodeAction));
    })

    if (!editCollectionPage) {
        let $cdm = jQuery('#collectionsDropdown');

        $cdm.on('mouseleave', function () {
            $cdm.hide();
        });

        // click on 'create collection' button
        $cdm.find('a.new').on('click', function (e) {
            $(this).find('span').hide();
            $cdm.find('input.new').show().focus();
            e.stopPropagation();
            e.preventDefault();
        });

        // events on 'new collection' input
        $cdm.find('input.new').on({
            // ENTER or ESC pressed
            keyup: function (e) {
                if (e.which == 27) {
                    jQuery(this).val('').hide().prev().show();
                    return;
                }

                if (e.which != 13) {
                    return;
                }

                jQuery(this).hide();
                $cdm.find('a.new span').show();

                let name = jQuery(this).val();
                jQuery(this).val('');

                if (name == '' || name == null) {
                    return;
                }

                let first = $cdm.children('.noCollecMsg').length > 0;

                addCollection(name)
                    .then((col) => {
                        if (first) {
                            $cdm.children('.noCollecMsg').remove();
                        }
                        changeLink('addImages', col.id, $cdm.data('img_id'));
                    })
                    .catch(e => alert(e));
            },
            // prevent click propagation
            click: function (e) {
                e.stopPropagation();
            }
        });


        function addCollection(name) {
            return new Promise((res, rej) => {
                jQuery.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: rootUrl + 'ws.php?format=json',
                    data: {
                        method: 'pwg.collections.create',
                        name: name,
                    },
                    success: function (data) {
                        if (data.stat == 'ok') {
                            let col = data.result;
                            // Adding Collection on the tooltip                       
                            if ($('.collection-item-coll_template').length > 0) {
                                let htmlTT = $('.collection-item-coll_template')[0].outerHTML
                                    .replaceAll('coll_template', col.id)
                                    .replaceAll('coll_name', col.name)
                                    .replaceAll('coll_nb_image', col.nb_images);

                                let nodeTT = $(htmlTT).attr('style', '');
                                $cdm.children('.switchBoxFooter').before(nodeTT);
                            }

                            if ($(collectionMenuTemplate).length > 0) {
                                let htmlMenu = $(collectionMenuTemplate)[0].outerHTML
                                    .replaceAll('coll_template', col.id)
                                    .replaceAll('coll_edit', 'index.php?/collections/edit/' + col.id)
                                    .replaceAll('coll_name', col.name)
                                    .replaceAll('coll_nb_images', col.nb_images);

                                let nodeMenu = $(htmlMenu).attr('style', '');

                                $(mbUserCollection).append(nodeMenu);
                            }
                            res(col);
                        }
                        else {
                            rej(data.message);
                        }
                    },
                    error: function () {
                        rej(str_error);
                    }
                });
            });
        }

        // add and remove links (delegate for new collections)
        $cdm.on('click', '.add, .remove', function (e) {
            let img_id = $cdm.data('img_id'),
                album_id = $cdm.data('album_id'),
                col_id = jQuery(this).data('id'),
                method = (album_id ? 'addAlbum' : jQuery(this).hasClass('add') ? 'addImages' : 'removeImages');

            changeLink(method, col_id, img_id, album_id)
                .then()
                .catch(e => alert(e));

            e.preventDefault();
        });

        function changeLink(method, col_id, img_id, album_id) {
            return new Promise((res, rej) => {

                //Loading animation
                let item = $cdm.find('.collection-item-' + col_id);
                item.find('i').attr('class', 'uc-icon-spin1 animate-spin')
                
                jQuery.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: rootUrl + 'ws.php?format=json',
                    data: {
                        method: 'pwg.collections.' + method,
                        col_id: col_id,
                        image_ids: img_id,
                        album_id: album_id
                    },
                    success: function (data) {
                        if (data.stat == 'ok') {
                            // update col counters
                            jQuery('.collection-item-' + col_id + ' .nbImages').html(data.result.nb_images);

                            $(collectionMenuNbImages.replace('%id%', col_id)).html(data.result.nb_images);

                            // update item datas
                            let $target = album_id ? jQuery('.addCollection[data-id]') : jQuery('.addCollection[data-id=' + img_id + ']');

                            $target.each(function () {
                                let col_ids = $(this).data('cols');

                                if (method == 'removeImages') {
                                    col_ids.splice(col_ids.indexOf(col_id), 1);
                                }
                                else if (col_ids.indexOf(col_id) == -1) {
                                    col_ids[col_ids.length] = col_id;
                                }
                                $(this).data('col', col_ids);
                            });
                            
                            // Update visual in dropdown
                            changeItem(item, method == 'addImages');

                            res();
                        }
                        else {
                            rej(data.message);
                        }
                    },
                    error: function () {
                        rej(str_error);
                    }
                });
            })
        }

        // main button, open the menu
        jQuery(document).on('click', '.addCollection', function (e) {
            
            updateDropdown($(this));

            if (picturePage) {
                $cdm.css({
                    'left': Math.min(jQuery(this).offset().left, jQuery(window).width() - $cdm.outerWidth(true) - 5),
                    'top': jQuery(this).offset().top + jQuery(this).outerHeight(true)
                });
            } else {

                $cdm.css({
                    'left': Math.min(e.pageX - jQuery(window).scrollLeft() - 20, jQuery(window).width() - $cdm.outerWidth(true) - 5),
                    'top':  Math.min(e.pageY - jQuery(window).scrollTop() - 5, jQuery(window).height() - $cdm.outerHeight(true)-5)
                });
            }

            $cdm.toggle();

            e.preventDefault();
        });

        function updateDropdown(nodeUC) {

            let img_id = nodeUC.data('id'),
                album_id = nodeUC.data('albumId') ? nodeUC.data('albumId') : '',
                col_ids = nodeUC.data('cols');
            
            $cdm.data('img_id', img_id);
            $cdm.data('album_id', album_id);

            $cdm.find('.collectionsItem:not(.collection-item-coll_template)').each(function (index, node) {
                let item = $(node);

                changeItem(item, col_ids && col_ids.find(x => x == item.data('id')));

                if (album_id) {
                    item.removeClass('remove').addClass('add');
                }
            });
        }

        function changeItem(item, isLinked) {
            if (isLinked) {
                item.removeClass('add').addClass('remove');
                item.find('i').attr('class', 'uc-icon-star-filled');
            } else {
                item.removeClass('remove').addClass('add');
                item.find('i').attr('class', 'uc-icon-star');
            }
        }

        // try to respect theme colors
        $cdm.children('.switchBoxFooter').css('border-top-color', $cdm.children('.switchBoxTitle').css('border-bottom-color'));

    } else { // If we are on collection page

        jQuery(thumbnailAction).on('click', function (e) {
            let $trigger = jQuery(this),
                img_id = jQuery(this).data('id'),
                col_id = collectionId;

            jQuery.ajax({
                type: 'POST',
                dataType: 'json',
                url: rootUrl + 'ws.php?format=json',
                data: {
                    method: 'pwg.collections.removeImages',
                    col_id: col_id,
                    image_ids: img_id
                },
                success: function (data) {
                    if (data.stat == 'ok') {

                        if ($(thumbnailsActions).length == 1) {
                            location.reload()
                        }

                        $trigger.closest(findThumbnailToHide).hide('fast', function () {
                            jQuery(this).remove();
                            if (typeof GThumb != 'undefined') {
                                GThumb.build();
                            }
                        });


                        $(collectionMenuNbImages.replace('%id%', col_id)).html(data.result.nb_images);

                        jQuery('.collection-item-' + col_id + ' .nbImages').html(data.result.nb_images);
                        if (typeof batchdown_count != 'undefined') {
                            batchdown_count = data.result.nb_images;
                        }
                    }
                    else {
                        alert(data.message);
                    }
                },
                error: function () {
                    alert(str_error);
                }
            });

            e.stopPropagation();
            e.preventDefault();
        });
    }
})
