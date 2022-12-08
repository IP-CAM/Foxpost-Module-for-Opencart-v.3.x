function reloadScript() {
    $('.product-info .image img').attr('id', 'image');

    $('.product-info .image-additional a').click(function (e) {
        e.preventDefault();
        var thumb = $(this).find('img').attr('src');
        var image = $(this).attr('href');
        $('#image').attr('src', image);
        $('#image').parent().attr('href', image);
        if ($('#image').data('imagezoom')) {
            $('#image').attr('data-largeimg', image);
            $('#image').data('imagezoom').changeImage(image, image);
        }
        return false;
    });

    $('#product-gallery .swiper-container').swiper(opts_gallery_otp);
    $('.product-info .image-gallery div').lightGallery({
        download: false,
        actualSize: false,
        hideBarsDelay: Journal.galleryBarsDelay,
        zoom: Journal.galleryZoom,
        thumbnail: Journal.galleryThumb,
        showThumbByDefault: Journal.galleryThumbHide,
        thumbWidth: Journal.galleryThumbWidth,
        thumbContHeight: Journal.galleryThumbHeight,
        thumbMargin: Journal.galleryThumbSpacing
    });
    $('#image').parent().click(function () {
        $('.product-info .image-gallery a.swipebox').eq($('#image').attr('data-src-index') || 0).click();
        return false;
    });
    $('.gallery-text').click(function () {
        $('.product-info .image-gallery a.swipebox').first().click();
        return false;
    });
}

function clean_prices() {
    product_prices_container.find('.product-price').remove();
    product_prices_container.find('.price-old').remove();
    product_prices_container.find('.price-new').remove();
    product_prices_container.find('.price-tax').remove();

    product_prices_container.append('<li class="product-price"></li>');
    product_prices_container.append('<li class="price-old"></li>');
    product_prices_container.append('<li class="price-new"></li>');
    product_prices_container.append('<li class="price-tax"></li>');
}

function revertImages() {
    $('#swap').val('');
    var img_default = $('#image-default').html();

    var label_default = $('#label-default').html();
    var image_default = $('#image-default').html();
    var image_additional_default = $('#image-additional-default').html();
    var image_gallery_default = $('#image-gallery-default').html();

    gallery_main_image_selector.find('a').remove();
    gallery_main_image_selector.append(image_default);
    gallery_additional_images_selector.html(image_additional_default);
    gallery_selector.html('<div>'+image_gallery_default+'</div>');

    reloadScript();
}
function swapImages(ov_id) {
    if (ov_id != '') {
        $.ajax({
            type: 'GET',
            url: 'index.php?route=product/product&action=getSwapImages',
            dataType: 'json',
            data: { product_id: product_id, option_value_id : ov_id, heading_title : heading_title },
            success: function(json) {
                if (json && json.swap) {
                    $('#swap').val(json.swap);
                    replace_gallery(json);

                    reloadScript();
                }
                else {
                    revertImages();
                }
            }
        });
    }
}
function replace_gallery(result) {
    gallery_main_image_selector.find('a').remove();
    gallery_main_image_selector.append(result.main);
    gallery_additional_images_selector.html(result.additional);
    gallery_selector.html('<div>'+result.gallery+'</div>');
}