$(document).ready(function(){
    $('#otp_default').attr('text-price', $('#otp-price').html());
});
$(document).on('keyup', $(input_quantity_selector), function(){
    checkAvailability();
});

function revertData() {
    revertAddToCart();
    //$(product_price_selector).html($('#otp_default').attr('text-price'));
    $('#otp-model').html($('#otp_default').attr('model'));
    $('#otp-reward').html($('#otp_default').attr('points'));
    $('#otp-stock').html($('#otp_default').attr('text-stock'));
    if(otp_config_otp_extra) {
        $('#otp-extra').html('');
    }
    if(otp_config_otp_dimensions) {
        $('#otp-dimensions').html('');
    }
}
function revertAddToCart() {
    var button_cart_text = otp_button_cart;
    if(typeof extenRevertAddToCartButtonText == 'function') {
        button_cart_text = extenRevertAddToCartButtonText(button_cart_text);
    }

    $(button_cart_selector).html(button_cart_text);
    $(button_cart_selector).removeClass('otp-no-stock');
    $(button_cart_selector).removeClass('inactive');
}
function checkAvailability() {
    setTimeout(function(){
        if ($('#otp_default').attr('out-of-stock') != '0') {
            if ($('#otp_default').attr('stock') == '0') {
                $(button_cart_selector).html($('#otp_default').attr('out-of-stock'));
                $(button_cart_selector).addClass('otp-no-stock');
                $(button_cart_selector).addClass('inactive');
            }
            else if (parseInt($('#otp_default').attr('stock')) < parseInt($(input_quantity_selector).val())) {
                $(button_cart_selector).html(otp_text_unavailable);
                $(button_cart_selector).addClass('otp-no-stock');
                $(button_cart_selector).addClass('inactive');
            }
            else {
                revertAddToCart();
            }
        }
        else {
            revertAddToCart();
        }
    }, 100);

}

function getOtpChild() {
    $.ajax({
        type: 'GET',
        url: 'index.php?route=product/product/&action=getOtpChildValues',
        data: { product_id: otp_product_id, child_option_id: $('#otp-option-1').attr('option'), parent_option_value_id: $('#otp-option-0').val(), mode: $('#otp-option-1').attr('mode') },
        success: function(data) {
            $('.otp-option-1-wrap').show();
            if ($('#otp-option-1').attr('mode') == 'select') {
                $('#otp-option-1').html(data);
            }
            else {
                $('.otp-option-1').html(data);
            }

            if(typeof extend_getOtpChild == 'function') {
                extend_getOtpChild($('.otp-option-1-wrap'));
            }
        }
    });
    product_data_selector.find('.text-danger').remove();
    $('#otp-option-1').val('');
}
function getOtpGrandchild() {
    $.ajax({
        type: 'GET',
        url: 'index.php?route=product/product/&action=getOtpGrandchildValues',
        data: { product_id: otp_product_id, grandchild_option_id: $('#otp-option-2').attr('option'), parent_option_value_id: $('#otp-option-0').val(), child_option_value_id: $('#otp-option-1').val(), mode: $('#otp-option-2').attr('mode') },
        success: function(data) {
            $('.otp-option-2-wrap').show();
            if ($('#otp-option-2').attr('mode') == 'select') {
                $('#otp-option-2').html(data);
            }
            else {
                $('.otp-option-2').html(data);
            }

            if(typeof extend_getOtpGrandchild == 'function') {
                extend_getOtpGrandchild($('.otp-option-2-wrap'));
            }
        }
    });
    product_data_selector.find('.text-danger').remove();
    $('#otp-option-2').val('');
}
function getOtpData(pov_id, cov_id, gov_id) {
    product_data_selector.find('.text-danger').remove();
    $(button_cart_selector).addClass('inactive');
    $.ajax({
        type: 'GET',
        dataType: 'json',
        url: 'index.php?route=product/product/&action=getOtp',
        data: { product_id: otp_product_id, parent_option_value_id: pov_id, child_option_value_id: cov_id, grandchild_option_value_id: gov_id, price: otp_raw_price, special: otp_raw_special, quantity: $(input_quantity_selector).val() },
        success: function(data){
            if (data) {
                $('#otp').val(data.id);
                $(button_cart_selector).removeClass('inactive');
                $('#otp_default').attr('stock', data.quantity);
                $('#otp_default').attr('out-of-stock', data.out_of_stock);
                var text_tax = $('#otp_default').attr('text-tax');
                checkAvailability();
//                otp_price = '';
//                clean_prices();
//                $(product_price_selector).hide();
//                $(product_price_old).hide();
//                $(product_price_new).hide();
//                $(product_price_tax).hide();
//
//                if (data.price != 0 || data.special != 0) {
//                    if (data.price != 0) {
//                        if (data.special != 0) {
//                            $(product_price_old).html(data.price).show();
//                            $(product_price_new).html(data.special).show();
//                        }
//                        else if ($('#otp_default').attr('special') != '0') {
//                            $(product_price_old).html(data.price).show();
//                            $(product_price_new).html($('#otp_default').attr('special')).show();
//                        }
//                        else {
//                            $(product_price_selector).html(data.price).show();
//                        }
//                    }
//                    else if (data.special != 0) {
//                        $(product_price_old).html($('#otp_default').attr('price')).show();
//                        $(product_price_new).html(data.special).show();
//                    }
//                    if (data.tax != 0 && tax_enabled) {
//                        $(product_price_tax).html(text_tax+' '+data.tax).show();
//                    }
//                    if(typeof extend_getOtpData_put_prices == 'function') {
//                        extend_getOtpData_put_prices(data);
//                    }
//                }
//                else {
//                    if ($('#otp_default').attr('special') != '0') {
//                        $(product_price_old).html($('#otp_default').attr('price')).show();
//                        $(product_price_new).html($('#otp_default').attr('special')).show();
//                    }
//                    else {
//                        $(product_price_selector).html($('#otp_default').attr('price')).show();
//                    }
//                    if(otp_tax && tax_enabled) {
//                        $(product_price_tax).html(text_tax+' '+otp_tax).show();
//                    }
//                }

                if (data.points != '' && data.points > 0) {
                    $('#otp-reward').html(data.points);
                }

                if (data.model != '') {
                    $('#otp-model').html(data.model);
                }
                else {
                    $('#otp-model').html($('#otp_default').attr('model'));
                }
                if(otp_config_otp_dimensions) {
                    if (data.dimensions_full_text != '') {
                        $('#otp-dimensions').html(data.dimensions_full_text);
                    }
                    else {
                        $('#otp-dimensions').html('');
                    }
                }

                if(otp_config_otp_extra) {
                    if (data.extra != '') {
                        $('#otp-extra').html(data.extra);
                    }
                    else {
                        $('#otp-extra').html('');
                    }
                }
                $('#otp-stock').html(data.stock);
            }
        }
    });
}
$(document).ready(function() {
    otp_select_events();
});

function otp_select_events() {
    if (otp_otpcount == 1) {
        $('#otp-option-0').on('change', function () {
            if ($(this).val() != '') {
                getOtpData($('#otp-option-0').val(), 0, 0);
            }
            else {
                revertData();
            }
        });
        $('.otp-option-0').on('click', 'li', function () {
            if (!$(this).hasClass('selected')) {
                $('.otp-option-0 > li').removeClass('selected');
                $(this).addClass('selected');
                $('#otp-option-0').val($(this).attr('value'));
                $('.otp-option-0-wrap small').html($(this).attr('data-original-title'));
                getOtpData($('#otp-option-0').val(), 0, 0);
            }
        });
    }
    if (otp_otpcount > 1) {
        $('#otp-option-0').on('change', function () {
            revertData();
            if ($(this).val() != '') {
                getOtpChild();
            }
            else {
                $('#otp-option-1').val('');
                $('.otp-option-1-wrap').hide();
            }
            $('#foraktar').html('');
            $('#otp-option-2').val('');
            $('.otp-option-2-wrap').hide();
        });
        $('.otp-option-0').on('click', 'li', function () {
            if (!$(this).hasClass('selected')) {
                $('.otp-option-0 > li').removeClass('selected');
                $(this).addClass('selected');
                revertData();
                $('#otp-option-0').val($(this).attr('value'));
                $('.otp-option-0-wrap small').html($(this).attr('data-original-title'));
                getOtpChild();
                $('#foraktar').html('');
                $('#otp-option-2').val('');
                $('.otp-option-2-wrap').hide();
            }
        });
    }
    if (otp_otpcount == 2) {
        $('#otp-option-1').on('change', function () {
            if ($(this).val() != '') {
                getOtpData($('#otp-option-0').val(), $('#otp-option-1').val(), 0);
            }
            else {
                revertData();
                $('#foraktar').html('');
            }
        });
        $('.otp-option-1').on('click', 'li', function () {
            if (!$(this).hasClass('selected')) {
                $('.otp-option-1 > li').removeClass('selected');
                $(this).addClass('selected');
                $('#otp-option-1').val($(this).attr('value')); 
                $('#foraktar').html($('div.fo_parent_' + $('#otp-option-0 ').val() + ' span.fo_child_' + $(this).attr('value')).html());
                $('.otp-option-1-wrap small').html($(this).attr('data-original-title'));
                getOtpData($('#otp-option-0').val(), $('#otp-option-1').val(), 0);
            }
        });
    }
    if (otp_otpcount == 3) {
        $('#otp-option-1').on('change', function () {
            revertData();
            if ($(this).val() != '') {
                getOtpGrandchild();
            }
            else {
                $('#foraktar').html('');
                $('#otp-option-2').val('');
                $('.otp-option-2-wrap').hide();
            }
        });
        $('.otp-option-1').on('click', 'li', function () {
            if (!$(this).hasClass('selected')) {
                $('.otp-option-1 > li').removeClass('selected');
                $(this).addClass('selected');
                revertData();
                $('#otp-option-1').val($(this).attr('value'));
                $('.otp-option-1-wrap small').html($(this).attr('data-original-title'));
                getOtpGrandchild();
            }
        });
        $('#otp-option-2').on('change', function () {
            if ($(this).val() != '') {
                getOtpData($('#otp-option-0').val(), $('#otp-option-1').val(), $('#otp-option-2').val());
            }
            else {
                revertData();
                $('#foraktar').html('');
            }
        });
        $('.otp-option-2').on('click', 'li', function () {
            if (!$(this).hasClass('selected')) {
                $('.otp-option-2 > li').removeClass('selected');
                $(this).addClass('selected');
                $('#otp-option-2').val($(this).attr('value'));
                $('.otp-option-2-wrap small').html($(this).attr('data-original-title'));
                getOtpData($('#otp-option-0').val(), $('#otp-option-1').val(), $('#otp-option-2').val());
            }
        });
    }
}

function imageOptionsCombinations(input_changed) {
    var img_combination = '';

    $.each($('div.otp-container'), function( index, value ) {
        var value = '';
        if($(this).find('select').length) {
            var value = $(this).find('select').val();
            if(value == '')
                return false;
        } else if($(this).find('input[type="hidden"]').length) {
            var value = $(this).find('input[type="hidden"]').val();
            if(value == '')
                return false;
        }
        img_combination += value+'_';
    });

    if($("div[data-opt_comb_ids='" + img_combination + "']").length) {
        var data_additional_images = [];
        $.each($("div[data-opt_comb_ids='" + img_combination + "']"), function( index, value ) {
            var div_opt_comb = $(this);
            temp = {
                popup : div_opt_comb.data('popup'),
                thumb : div_opt_comb.data('thumb'),
                popup : div_opt_comb.data('popup'),
                otp_thumb : div_opt_comb.data('otp_thumb'),
                title : div_opt_comb.data('title'),
            };
            data_additional_images.push(temp);
        });

        $.ajax({
            type: 'GET',
            url: 'index.php?route=product/product&action=getOptCombsImages',
            dataType: 'json',
            data: { data_additional_images : data_additional_images },
            success: function(result) {
                if (result != '') {
                    replace_gallery(result);
                    reloadScript();
                    return true;
                } else {
                    getSwapAjax(input_changed);
                }
            }
        });
    } else {
        getSwapAjax(input_changed);
    }
}

function getSwapAjax(input_changed) {
    if(typeof swapcheck !== 'undefined') {
        if (input_changed.is('select') && input_changed.hasClass('select-swap-' + swapcheck)) {
            if (input_changed.val() != '') {
                swapImages(input_changed.val());
            }
            else {
                revertImages();
            }
        }

        if (input_changed.is('li') && input_changed.closest('ul').hasClass('list-swap-' + swapcheck)) {
            swapImages(input_changed.attr('value'));
        }
    }
}

$(document).on('change', '.select-swap', function() {
    imageOptionsCombinations($(this));
});
$(document).on('click', '.otp-option li', function() {
    imageOptionsCombinations($(this));
});