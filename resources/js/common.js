$(document).ready(function () {

    var start_window_width = $(window).width();

    /*For fix mobile nav */
    var $mobileNav = $('.js-mobile-navigation');
    var startPosMobileNav = $mobileNav.offset().top;
    var mobileNavHeight = $mobileNav.height();
    var mobileNavOuterHeight = $mobileNav.outerHeight(true);
    /*For fix mobile nav */

    var Validator = new Validate();

    $('body')
        .on('click', '.js-scrollTo', function (e) {
            e.preventDefault();
            var scroll_target = $(this).data('scrollTo');

            var $scroll_el = $('*[data-scroll-target="' + scroll_target + '"]:visible');

            if ($scroll_el.length !== 0) {
                $('html, body').animate({scrollTop: $scroll_el.offset().top}, 500);
            }

            if ($scroll_el.attr('role') == 'tab') {
                $scroll_el.trigger('click');
            }
        })
        .on('click', '.tab__link', function (e) {
            e.preventDefault();
            let $tabContainer = $(this).closest('.tab');

            $tabContainer.find('.tab__content').removeClass('tab__content--visible');
            $tabContainer.find('.tab__link').removeClass('tab__link--active');

            let contentLink = $(this).attr('href');

            $(this).addClass('tab__link--active');
            $tabContainer.find(contentLink).addClass('tab__content--visible');

        })
        .on('submit', '.js-form', function (e) {
            e.preventDefault();
            let $form = $(this);
            let action = $form.attr('action');
            let form_type = $form.attr('type') || 'POST';
            let has_error = 0;

            if ($form.hasClass('js-form-validate')) {

                $form.find('.js-validation').removeClass('form__validation--error');
                $form.find('.js-result').html('');

                $form.find('input.js-field-validate, select.js-field-validate, textarea.js-field-validate').each(function () {
                    has_error = checkField($(this), has_error, Validator);
                });

                if (has_error == 0) {
                    $.ajax({
                        url: action,
                        type: form_type,
                        data: $form.serialize(),
                        beforeSend: function () {
                            $form.find('input[type=submit]').addClass('form__btn--success');

                            $('body').append('<div class="bg-loading" style="display:flex"></div>');
                            $form.find('.js-loading').addClass('loading--show');
                            $form.css('z-index', '4');

                        },
                        success: function (json) {

                            if (json['success']) {
                                $form.find('.js-result').html(json['success']);
                                $form.find('.js-control')
                                    .val('')
                                    .attr('checked', false);
                                setTimeout(function () {
                                    $form.find('.js-result').html('');
                                    if ($form.hasClass('js-popup-block')) {
                                        $.magnificPopup.close();
                                    }
                                    if (json['redirect']) {
                                        location = json['redirect'];
                                    }
                                }, 3000);
                            } else if (json['redirect']) {
                                location = json['redirect'];
                            }
                        },
                        complete: function () {
                            $form.find('input[type=submit]').removeClass('form__btn--success');

                            $('body').find('.bg-loading').css('display', 'none');
                            $form.find('.js-loading').removeClass('loading--show');
                            $form.css('z-index', '0');
                        },
                        error: function (data) {
                            var errors = $.parseJSON(data.responseText);

                            $form.find('.js-result').html(getFirstError(errors.errors));
                        }
                    });
                }
            }
        })
        .on('click', function (e) {
            if ($(this).hasClass('open-dropdown')) {
                let $dropMenu = $('.dropdownMenu__drop--show');
                if (!$dropMenu.is($(e.target)) && $dropMenu.has(e.target).length === 0) {
                    $('.dropdownMenu__drop--show').removeClass('dropdownMenu__drop--show');
                }
            }

            if ($(this).hasClass('notification--active') && !$(e.target).is('.js-notification') && !$(this).hasClass('preloader--active')) {
                $('.js-notification').trigger('close');
            }
        })
        .on('click', function (e) {
            if ($(this).hasClass('js-forgotten-open')) {
                let $form = $('.js-form-forgotten');

                if (!$form.is($(e.target)) && $form.has(e.target).length === 0) {
                    $form.hide();
                    $form.prev('.form').show();
                }
            }
        })
        .on('click', '.js-open-login-form', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $('.js-topform-login').trigger('click');
        })
        .on('click', '.js-login-open-forgotten', function (e) {
            e.stopPropagation();
            $(this).closest('.form').hide();
            $(this).closest('.form').next('.form').show();
            $('body').addClass('js-forgotten-open');
        })
        .on('click', '.js-product-toggle-btn', function () {
            $(this).next('.js-product-toggle-content').slideToggle();
        })
        .on('click', '.js-menu-link-with-child', function (e) {
            e.preventDefault();

            let lvl = +$(this).closest('.js-mobile-section').data('level') + 1;
            let parent_id = $(this).data('id') ? +$(this).data('id') : 0;

            $('#mobile-categories-' + lvl).find('.js-menu-link').each(function () {
                if ($(this).data('parent') == parent_id) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });

            $(this).closest('.js-mobile-section ').addClass('mobileMenu__section--subopen');

            $('#mobile-categories-' + lvl).addClass('mobileMenu__section--open');
            $('#mobile-categories-' + lvl).find('.mobileMenu__return--text').text($(this).text());

            if ($(this).closest('.js-mobile-section').data('level') == 1) {
                $(this).closest('.js-m-container-for-scroll').mCustomScrollbar('destroy');
            }
        })
        .on('click', '.js-mobile-category-return', function (e) {
            e.preventDefault();

            $(this).parent('.js-mobile-section').removeClass('mobileMenu__section--open');

            if ($(this).parent('.js-mobile-section').data('level') == 2) {
                $('#mobile-categories-1').removeClass('mobileMenu__section--subopen');
            } else {
                $(this).parent('.js-mobile-section').prev().removeClass('mobileMenu__section--subopen');
            }

        })
        .on('click', '.js-open-modal', function(e){
            e.preventDefault();

            $.magnificPopup.open({
                items: {
                    src: $($(e.target).attr('href')).html(),
                    type: 'inline'
                }
            });
        });

    $('.js-telMask').mask('+38 (099) 999-99-99', {
        completed: function () {
            this.trigger('change');
        }
    });

    $('.js-open-modal-cart').magnificPopup({
        closeMarkup: '<button title="%title%" type="button" class="mfp-close icon sb-icon-cancel"></button>'
    });

    /* DropDown */

    $('.js-dropdown-toggle').click(function (e) {
        e.stopPropagation();

        $('body').removeClass('open-dropdown');
        $('.dropdownMenu__drop').removeClass('dropdownMenu__drop--show');

        $(e.target).next('.js-dropdown-content').toggleClass('dropdownMenu__drop--show');
        $('body').toggleClass('open-dropdown');
    });

    /* Language */
    $('#form-language .js-change-language').on('click', function (e) {
        e.preventDefault();

        $('#form-language input[name=\'code\']').val($(this).data('val'));

        $('#form-language').submit();
    });

    // Currency
    $('#form-currency .js-change-select').on('click', function (e) {
        e.preventDefault();

        $('#form-currency input[name=\'code\']').val($(this).attr('name'));

        $('#form-currency').submit();
    });

    /* Change Products View */
    $('.js-view-link').on('click', function (e) {
        e.preventDefault();
        let type = $(this).data('page');
        $(this).siblings().removeClass('contentView__link--active');
        $(this).addClass('contentView__link--active');

        if ($(this).data('view') != localStorage.getItem('display')) {
            $.ajax({
                url: $('body').data('langPrefix') + '/index.php?route=product/' + type + '/setView',
                type: 'POST',
                data: 'view=' + $(this).data('view'),
                beforeSend: function () {
                    $('.products').html('');
                    $('.products__pagination').hide();
                    $('.products').append('<div id="ajaxblock" style="width:100%;height:30px;margin-top:20px;text-align:center;border:none !important;"><i class="icon sb-icon-refresh-button icon--spin"></i></div>');
                },
                success: function (json) {
                    if (json['success']) {
                        localStorage.setItem('display', $(this).data('view'));
                        $.get(location.href, function (data) {
                            $('.products').html($(data).find('.products').html());
                            $('.products__pagination').show();
                        });
                    }
                }
            });
        }
    });

    //Mobile Menu

    $('#js-open-mobile-menu').click(function () {
        $('.mobileMenu').addClass('mobileMenu--open');
        $('body').addClass('no-scroll');
    });

    $('#mobileMenu__close').click(function () {
        var menu = $('.mobileMenu');
        menu.removeClass('mobileMenu--open');
        $('body').removeClass('no-scroll');
    });

    $(document)
        .on('open', '.js-notification', function () {
            $('body').addClass('notification--active');

            toggleBackground(true);

            $(this).removeClass('notification--hidden').addClass('notification--visible');
        })
        .on('close', '.js-notification', function () {
            $('body').removeClass('notification--active');

            toggleBackground(false);

            $(this).removeClass('notification--visible').addClass('notification--hidden');
        })
        .on('remove', '.js-notification', function () {
            $(this).remove();
        })
        .on('click', '.js-notification-close', function () {
            $('.js-notification').trigger('close');
        });

    $('.js-footer-collapse').click(function () {
        if ($(window).width() < 971) {
            $(this).next('.js-footer-collapse-content').slideToggle();
        }
    });

//mobile language

    $(window).resize(function (e) {

        if ($(window).width() == start_window_width && !e.isTrigger) return false;

        change_pos_mob('.js-change-language', '#form-language .js-dropdown-content', '#mLanguages', 971, {
            mobile: {
                preAction: function () {
                    $('.js-change-language').each(function () {
                        $(this).removeClass('dropdownMenu__link')
                            .addClass('mobileMenuSection__link')
                            .addClass('mobileMenuSection__link mobileMenuSection__link--language');
                        if ($(this).hasClass('dropdownMenu__link--active')) {
                            $(this)
                                .removeClass('dropdownMenu__link--active')
                                .addClass('mobileMenuSection__link--active');
                        }
                    });
                }
            }
        });

        change_pos_mob('#formTopLogin', '#formTopLoginContainer', '#mobileFormLogin', 971);

        change_pos_mob('#form-review', '#tab_review', '#toggleReview', 442);

        if(window.theme === 'beauty'){

            change_pos_mob('.js-logo', '.js-logo-container', '.js-m-logo-container', 971);

            change_pos_mob('.js-product-info-contentCol', '.js-product-info-contentCol-container', '.js-product-info-contentCol-container-m', 971);

            change_pos_mob('.js-rating-brand', '.js-rating-brand-container', '.js-rating-brand-container-m', 971);


        }


        if ($(this).width() <= 970) {
            $('.js-m-container-for-sort').append($('.js-products-sort'));
        } else {
            $('.js-products-filter-container').prepend($('.js-products-sort'));
        }

        if ($(this).width() < 760) {
            changeXsText();
        } else {
            changeLgText();
        }

        //Footer Collapse
        if ($(window).width() < 971) {
            $('.js-footer-collapse').next('.js-footer-collapse-content').hide();

            $('.js-footer-section-contacts').append($('.js-footer-to-column-first'));
            $('.js-footer-section-schedule').append($('.js-footer-to-column-second'));
            $('.js-footer-section-call').append($('.js-footer-to-column-third'));

        } else {
            $('.js-footer-collapse').next('.js-footer-collapse-content').show();

            $('.js-big-container-for-els-foot').append($('#footLogo'));
            $('.js-big-container-for-els-foot').append($('#footSchedule'));
            $('.js-big-container-for-els-foot').append($('#footModals'));
        }

        if ($('.js-view-link.contentView__link--active').data('view') === 'list' && $(window).width() < 1200) {
            $('.js-view-link:not(".contentView__link--active")').trigger('click');
        }

    });

    $(window).scroll(function () {

        if ($(window).width() < 971 && $(window).scrollTop() >= startPosMobileNav) {
            $mobileNav
                .addClass('mNav--fixed')
                .parent().css('paddingTop', mobileNavOuterHeight);
        } else if ($(window).scrollTop() < startPosMobileNav + mobileNavHeight) {
            $('.js-mobile-navigation')
                .removeClass('mNav--fixed')
                .parent().css('paddingTop', 0);
        }
    });

    $(window).trigger('resize', [1]);

//popup form
    $('.btn_popup').magnificPopup();

    $('.btn_popup_contact').magnificPopup();

    $('.btn_popup').on('click', function () {
        var his_href = $(this).attr('href');
        if ($(this).data('title')) {
            $(his_href).find('.title').text($(this).data('title'));
        } else {
            $(his_href).find('.title').text($(this).text());
        }
    });

    $('.quantity').find('.js-change-quantity').on('click', function () {
        let $quantity = $(this).closest('.quantity').find('.js-quantity');

        let multiplicity = $quantity.data('multiplicity');

        let recomendMultiplicity = getRecomendQuantity(multiplicity, $quantity.val());

        if ($(this).data('action') == 'plus') {
            $quantity.val(recomendMultiplicity['+']);
        } else if ($(this).data('action') == 'minus') {
            if (+$quantity.val() - 1 < 0) {
                $quantity.val(0);
            } else {
                $quantity.val(recomendMultiplicity['-']);
            }
        }

        $quantity.trigger('input');
    });

});

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

export function Validate() {
    this.textMin = 0;
    this.textMax = 3000;

};

Validate.prototype.checkMail = function ($input, display_error) {

    if (typeof display_error === 'undefined') {
        display_error = 1;
    }

    var re = /^[\w-\.]+@[\w-]+\.[a-z]{2,4}$/i;
    var valid = re.test($input.val());
    if (!valid && display_error) {
        $input.next('.js-validation').addClass('form__validation--error');
    }
    return valid;
};

Validate.prototype.checkSame = function ($input, $secondInput) {
    if ($input.val() !== $secondInput.val() || !$input.val().length) {
        $input.next('.js-validation').addClass('form__validation--error');
        return 0;
    } else {
        return 1;
    }
}

Validate.prototype.checkText = function ($input, textMin, textMax) {

    textMin = textMin || this.textMin;
    textMax = textMax || this.textMax;

    let field_length = $input.val().trim().length;

    if (field_length < textMin || field_length > textMax) {
        $input.next('.js-validation').addClass('form__validation--error');
        return 0;
    } else {
        return 1;
    }
}

Validate.prototype.checkTel = function ($input, format, text) {
    format = format || /^\+38\s\(0[0-9]{2}\)\s[0-9]{3}-[0-9]{2}-[0-9]{2}$/;

    switch (format) {
        case 'count_numbers':
            valid = (text.length === 10 || text.length === 12);
            if (valid) $input.val(text);
            break;
        default:
            var re = format;
            var myTel = $input.val();
            var valid = re.test(myTel);

            if (!valid) {
                valid = ($input.val().length === 10 || $input.val().length === 12);
            }
            break;

    }
    if (!valid) {
        $input.parent().find('.js-validation').addClass('form__validation--error');
    }
    return valid;
};

Validate.prototype.checkLogin = function ($input) {

    let valid = Validate.prototype.checkMail($input, 0);

    if (!valid) {
        valid = Validate.prototype.checkTel($input, 'count_numbers', $input.val().replace(/[^0-9]/gim, ''))
    }

    return valid;
}

export function checkField($field, has_error, Validator) {

    $field.parent().find('.js-validation').removeClass('form__validation--error');

    let tmp;
    switch ($field.data('field')) {
        case 'name':
            let min = $field.data('min') || 2;
            let max = $field.data('max') || 32;
            tmp = !Validator.checkText($field, min, max);
            if (!has_error) has_error = tmp;
            break;
        case 'password':
            tmp = !Validator.checkText($field, 4, 20);
            if (!has_error) has_error = tmp;
            break;
        case 'same':
            tmp = !Validator.checkSame($field, $($field.data('samecheck')));
            if (!has_error) has_error = tmp;
            break;
        case 'email':
            tmp = !Validator.checkMail($field);
            if (!has_error) has_error = tmp;
            break;
        case 'text':
            min = $field.data('min') || 10;
            max = $field.data('max') || 1000;

            tmp = !Validator.checkText($field, min, max);
            if (!has_error) has_error = tmp;
            break;
        case 'tel':
            tmp = !Validator.checkTel($field);
            if (!has_error) has_error = tmp;
            break;
        case 'login':
            tmp = !Validator.checkLogin($field);
            if (!has_error) has_error = tmp;
            break;
        default:
            break;
    }

    return has_error;
}

// Cart update custom function
function updateChecoutFromCart(productsInCart) {
    if (location.pathname.indexOf('checkout') != -1) {
        if (productsInCart == 0) {
            location = 'index.php?route=checkout/cart';
        } else {
            $('.js-section-cart').load('index.php?route=checkout/cart/getCheckoutCart');
        }
    }
}

// Cart add remove functions
var cart = {
    'add': function (product_id, quantity) {
        $.ajax({
            url: $('body').data('langPrefix') + '/index.php?route=checkout/cart/add',
            type: 'post',
            data: 'product_id=' + product_id + '&quantity=' + (typeof (quantity) != 'undefined' ? quantity : 1),
            dataType: 'json',
            success: function (json) {

                if (json['redirect']) {
                    location = json['redirect'];
                }

                if (json['success']) {
                    setTimeout(function () {
                        $('#cart').addClass('headerTools__item--active');
                        $('#cart-items').html(json['items_count']);
                    }, 100);

                    $('#cart_popup .js-cart-content').load('index.php?route=common/cart/info #cart_popup .js-cart-content', function (response) {
                        $('#cart').trigger('click');
                    });
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    },
    'update': function (key, quantity) {
        var DedUpdate = $.Deferred();

        $.ajax({
            url: $('body').data('langPrefix') + '/index.php?route=checkout/cart/edit',
            type: 'post',
            data: 'key=' + key + '&quantity=' + (typeof (quantity) != 'undefined' ? quantity : 1),
            dataType: 'json',
            success: function (json) {

                if (json['error']) {
                    getNotification(json['error']);
                }

                $('#cart-items').html(json['total']);

                $.get($('body').data('langPrefix') + "/index.php?route=common/cart/info", function (data) {
                    $('#cart_popup .js-cart-content').html($(data).find('#cart_popup .js-cart-content').html());
                });

                DedUpdate.resolve();

                updateChecoutFromCart(json['total']);
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });

        return DedUpdate;
    },
    'remove': function (key) {
        $.ajax({
            url: $('body').data('langPrefix') + '/index.php?route=checkout/cart/remove',
            type: 'post',
            data: 'key=' + key,
            dataType: 'json',
            success: function (json) {
                // Need to set timeout otherwise it wont update the total
                setTimeout(function () {
                    $('#cart-items').html(json['total']);
                }, 100);

                $.get($('body').data('langPrefix') + "/index.php?route=common/cart/info", function (data) {
                    $('#cart_popup .js-cart-content').html($(data).find('#cart_popup .js-cart-content').html());
                });

                updateChecoutFromCart(json['total']);

                //form in cart hide
                if (json['total'] == 0) {
                    $('#cart_popup .cart_bottom').hide(300);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }
}

var wishlist = {
    'add': function (product_id, target) {
        if (globalConfig.Customer.isLogged) {
            $.ajax({
                url: $('body').data('langPrefix') + '/index.php?route=account/wishlist/add',
                type: 'post',
                data: 'product_id=' + product_id,
                dataType: 'json',
                success: function (json) {
                    $('.alert').remove();

                    if (json['redirect']) {
                        location = json['redirect'];
                    }

                    if (json['success']) {

                        setTimeout(function () {
                            $('#wishlist').addClass('headerTools__item--active');

                            $(target).addClass('action__add--active');

                            $('.js-wishlist-add').each(function () {
                                if ($(this).data('product') == product_id) {
                                    $(this).addClass('headerTools__item--active');
                                }
                            });

                            $('#wishlistTotal').html(json['total']);

                        }, 100);

                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        } else {
            event.stopPropagation();
            openLoginForm();
        }
    },
    'remove': function () {

    }
}

var compare = {
    'add': function (product_id, target) {
        $.ajax({
            url: $('body').data('langPrefix') + '/index.php?route=product/compare/add',
            type: 'post',
            data: 'product_id=' + product_id,
            dataType: 'json',
            success: function (json) {
                //$('.alert').remove();

                if (json['success']) {
                    //$('#content').parent().before('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');

                    setTimeout(function () {
                        $('#compare').addClass('headerTools__item--active');

                        $(target).addClass('action__add--active');

                        $('.js-comparelist-add').each(function () {
                            if ($(this).data('product') == product_id) {
                                $(this).addClass('headerTools__item--active');
                            }
                        });

                        $('#compareTotal').html(json['total']);

                    }, 100);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    },
    'remove': function () {

    }
}

/* Agree to Terms */
$(document).delegate('.agree', 'click', function (e) {
    e.preventDefault();

    if ($('#modal-agree').length > 0) {
        $('#modal-agree').modal('show');
    } else {

        var element = $(this);

        $.ajax({
            url: element.attr('href'),
            type: 'get',
            dataType: 'html',
            success: function (data) {

                data = $(data).find('#content').html();
                var html = '';

                html += '<div id="modal-agree" class="modal">';
                html += '  <div class="modal-dialog">';
                html += '    <div class="modal-content">';
                html += '      <div class="modal-header">';
                html += '        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
                html += '        <h4 class="modal-title">' + element.text() + '</h4>';
                html += '      </div>';
                html += '      <div class="modal-body">' + data + '</div>';
                html += '    </div';
                html += '  </div>';
                html += '</div>';

                $('body').append(html);

                $('#modal-agree').modal('show');
            }
        });
    }
});

// Autocomplete */
(function ($) {
    $.fn.autocomplete = function (option) {
        return this.each(function () {
            this.timer = null;
            this.items = new Array();

            $.extend(this, option);

            $(this).attr('autocomplete', 'off');

            // Focus
            $(this).on('focus', function () {
                this.request();
            });

            // Blur
            $(this).on('blur', function () {
                setTimeout(function (object) {
                    object.hide();
                }, 200, this);
            });

            // Keydown
            $(this).on('keydown', function (event) {
                switch (event.keyCode) {
                    case 27: // escape
                        this.hide();
                        break;
                    default:
                        this.request();
                        break;
                }
            });

            // Click
            this.click = function (event) {
                event.preventDefault();

                value = $(event.target).parent().attr('data-value');

                if (value && this.items[value]) {
                    this.select(this.items[value]);
                }
            }

            // Show
            this.show = function () {
                var pos = $(this).position();

                $(this).siblings('ul.dropdown-menu').css({
                    top: pos.top + $(this).outerHeight(),
                    left: pos.left
                });

                $(this).siblings('ul.dropdown-menu').show();
            }

            // Hide
            this.hide = function () {
                $(this).siblings('ul.dropdown-menu').hide();
            }

            // Request
            this.request = function () {
                clearTimeout(this.timer);

                this.timer = setTimeout(function (object) {
                    object.source($(object).val(), $.proxy(object.response, object));
                }, 200, this);
            }

            // Response
            this.response = function (json) {
                let html = '';

                if (json.length) {
                    for (i = 0; i < json.length; i++) {
                        this.items[json[i]['value']] = json[i];
                    }

                    for (i = 0; i < json.length; i++) {
                        if (!json[i]['category']) {
                            html += '<li data-value="' + json[i]['value'] + '"><a href="#">' + json[i]['label'] + '</a></li>';
                        }
                    }

                    // Get all the ones with a categories
                    var category = new Array();

                    for (i = 0; i < json.length; i++) {
                        if (json[i]['category']) {
                            if (!category[json[i]['category']]) {
                                category[json[i]['category']] = new Array();
                                category[json[i]['category']]['name'] = json[i]['category'];
                                category[json[i]['category']]['item'] = new Array();
                            }

                            category[json[i]['category']]['item'].push(json[i]);
                        }
                    }

                    for (i in category) {
                        html += '<li class="dropdown-header">' + category[i]['name'] + '</li>';

                        for (j = 0; j < category[i]['item'].length; j++) {
                            html += '<li data-value="' + category[i]['item'][j]['value'] + '"><a href="#">&nbsp;&nbsp;&nbsp;' + category[i]['item'][j]['label'] + '</a></li>';
                        }
                    }
                }

                if (html) {
                    this.show();
                } else {
                    this.hide();
                }

                $(this).siblings('ul.dropdown-menu').html(html);
            }

            $(this).after('<ul class="dropdown-menu"></ul>');
            $(this).siblings('ul.dropdown-menu').delegate('a', 'click', $.proxy(this.click, this));

        });
    }
})(window.jQuery);

export function change_pos_mob(elm, desc_pos, mob_pos, point, actions) {

    if (!actions) {
        actions = {
            mobile: {}
        }
    }

    var cur_tg = $(elm);

    if ($(window).width() < point) {
        if (typeof actions.mobile.preAction !== 'undefined') {
            actions.mobile.preAction();
        }

        $(mob_pos).append(cur_tg);

        if (typeof actions.mobile.afterAction !== 'undefined') {
            actions.mobile.afterAction();
        }
    } else {
        if (!$(desc_pos).find(cur_tg).length) $(desc_pos).append(cur_tg);
    }
}

function getURLVar(key) {
    var value = [];

    var query = String(document.location).split('?');

    if (query[1]) {
        var part = query[1].split('&');

        for (i = 0; i < part.length; i++) {
            var data = part[i].split('=');

            if (data[0] && data[1]) {
                value[data[0]] = data[1];
            }
        }

        if (value[key]) {
            return value[key];
        } else {
            return '';
        }
    }
}

function changeXsText() {
    $('.js-change-ln-text').each(function () {
        if ($(this).data('xsText')) {
            $(this).data('lgText', $(this).text());
            $(this).text($(this).data('xsText'));
        }
    });
}

function changeLgText() {
    $('.js-change-ln-text').each(function () {
        if ($(this).data('lgText')) {
            $(this).text($(this).data('lgText'));
        }
    });
}


function isAvailable($block, error_null) {

    var Def = $.Deferred();

    if (typeof (error_null) === 'undefined') {
        error_null = true;
    }

    var $block = $block || $('.productInfo');

    post_data = $block.find(
        ' input[type=\'hidden\'],' +
        ' .js-quantity,' +
        ' input[type=\'radio\']:checked,' +
        ' input[type=\'checkbox\']:checked,' +
        ' select');

    if (+$block.find('.js-quantity').val() !== '') {
        $.ajax({
            url: $('body').data('langPrefix') + '/index.php?route=product/product/isAvailableProduct',
            type: 'post',
            data: post_data,
            dataType: 'json',
            success: function (json) {
                if (json['error']) {
                    if (!error_null) {
                        if (+$block.find('.js-quantity').val() !== 0) {
                            getNotification(json['error']);
                            Def.resolve(false);
                        } else {
                            updatePrice($block, post_data);
                            Def.resolve(true);
                        }
                    } else {
                        getNotification(json['error']);
                        Def.resolve(false);
                    }
                } else if (json['success']) {
                    updatePrice($block, post_data);
                    Def.resolve(true);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }

    return Def;
}

function updatePrice($block, post_data) {

    var is_special = 0;
    var price_old, price_new;

    if ($block.find('.js-price-share').length) {
        price_old = $block.find('.js-price-default');
        price_new = $block.find('.js-price-share');

        is_special = 1;
    } else {
        price_old = $block.find('.js-price-default');
        price_new = '';

        is_special = 0;
    }

    if (+$block.find('.js-quantity').val() !== '') {
        $.ajax({
            url: $('body').data('langPrefix') + '/index.php?route=product/product/getProductPrice',
            type: 'post',
            data: post_data,
            dataType: 'json',
            success: function (json) {
                if (json['error']) {
                    console.log(json['error']);
                } else {
                    if (is_special) {
                        price_old.html(json['price']);
                        price_new.html(json['special']);
                    } else {
                        price_old.html(json['price']);
                    }
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }
}

export function getNotification(text) {

    let close_text = global_lang_config.close;

    $('.js-notification').trigger('remove');

    let html = "<div class=\"notification notification--hidden js-notification\">" +
        "       <div class=\"l-notification__content l-notification__content--left\">" +
        "           <div class=\"notification__text\">" + text + "</div>" +
        "       </div>" +
        "       <div class=\"l-notification__content l-notification__content--right\">" +
        "           <div class=\"notification__close js-notification-close\">" + close_text + "</div>" +
        "           <i class=\"notification__icon icon sb-icon-cancel-round js-notification-close\"></i>" +
        "       </div>" +
        "   </div>";

    $('body').after(html);

    setTimeout(function () {
        $('.js-notification').trigger('open');
    }, 100);

    setTimeout(function () {
        $('.js-notification').trigger('close');
    }, 5000);

}

function getRecomendQuantity(multiplicity, quantity) {
    var a, b, result = [];

    if (multiplicity > quantity) {
        result['+'] = result['-'] = multiplicity
    } else {
        a = Math.trunc(quantity / multiplicity);
        b = (multiplicity * a) + multiplicity;

        result['+'] = b;

        if (multiplicity * a != quantity) {
            result['-'] = multiplicity * a;
        } else {
            result['-'] = multiplicity * a - multiplicity;
        }
    }

    return result;
}

export function togglePreloader(id, path, status) {

    var settings = {
        loadingBlockHtml: '<div class="loading js-loading" id="' + id + '">\n' +
            '            <div class="loading__wrap">\n' +
            '                <span class="icon icon--spin sb-icon-loading">\n' +
            '                    <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span><span class="path7"></span><span class="path8"></span><span class="path9"></span><span class="path10"></span><span class="path11"></span><span class="path12"></span>\n' +
            '                </span>\n' +
            '            </div>\n' +
            '        </div>',
        loadingClass: 'loading--show',
        loaderPath: '.js-loading',
        bgClass: 'bg-loading'
    };

    let $container;

    if (typeof path === "undefined" || !path.length) {
        $container = $('.App');
    } else if (typeof path === "string") {
        $container = $(path);
    } else {
        $container = path;
    }

    let $loadingBlock = $('#' + id);

    if (!$loadingBlock.length) {
        $container.append(settings.loadingBlockHtml);

        $loadingBlock = $('#' + id);
    }

    if (status) {
        $('body').addClass('no-scroll preloader--active');
        $loadingBlock.addClass(settings.loadingClass);
        $container.css('z-index', '4');
    } else {
        $('body').removeClass('no-scroll').removeClass('preloader--active');
        $loadingBlock.removeClass(settings.loadingClass);
        $container.css('z-index', '0');
    }

    toggleBackground(status);
}

function toggleBackground(status) {

    var bgPath = '.bg-loading';

    var stopClasses = ['preloader--active', 'notification--active'];

    if (status) {
        $.fx.off = true;
        $('body').find(bgPath + ':hidden').show('flex');
    } else {

        var allowRemoving = true;

        stopClasses.forEach(function (elClass) {
            if (allowRemoving && $('body').hasClass(elClass)) {
                allowRemoving = false;
            }
        });

        if (allowRemoving) $('body').find(bgPath).hide();
    }
    $.fx.off = false;
}

function openLoginForm() {

    if ($(window).width() < 971) {
        //mobile
        $('a[href=#mobileFormLogin]').trigger('click');
    } else {
        //desktop
        $('html, body').animate({scrollTop: 0}, 200);
        $('.js-topform-login').trigger('click');
    }
}

export function getFirstError(errors) {

    var first_error_message = '';

    for (let error_name in errors) {
        if (!first_error_message) first_error_message = errors[error_name][0];
    }

    return first_error_message;
}

export function findObjectByKey(object, key, value) {
    for (let i in object) {
        if (object[i][key] === value) {
            return object[i];
        }
    }
    return null;
}

export function updateRating($this) {

    $('.js-review .rating i.icon').addClass('rating--null');

    $this.removeClass('rating--null')
        .parent('.form__ratingLabel')
        .prevAll()
        .find('i.icon')
        .removeClass('rating--null');
    $('.js-review input[type="hidden"]').val($this.prev().data('value'));
}