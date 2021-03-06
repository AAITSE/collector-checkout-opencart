$(document).ready(function() {
    update_checkout();
});

$(document).on('submit', 'form#account-login', function(e) {
    e.preventDefault();
    var el = $(e.currentTarget);

    $.ajax({
        url: 'index.php?route=checkout/collector/login',
        method: 'post',
        dataType: 'json',
        data: {
            email: el.find('[name="email"]').first().val(),
            password: el.find('[name="password"]').first().val()
        }
    }).done(function (response) {
        if (response.success) {
            self.location.href = location.href;
        } else {
            alert(response.message)
        }
    });

    return false;
});

$(document).on( 'change', '.collector-cart .qty', function(e) {
    var el = $(e.currentTarget);
    var cart_id = el.closest('tr').data('cart-id');

    if (el.hasClass('disabled')) {
        e.preventDefault();
        return false;
    }

    el.prop('disabled', true);
    el.addClass('disabled');

    window.collector.checkout.api.suspend();

    $.ajax({
        url: 'index.php?route=checkout/collector/cart_update',
        method: 'post',
        dataType: 'json',
        data: {
            cart_id: cart_id,
            qty: el.val()
        }
    }).always(function () {
        //window.collector.checkout.api.resume();
    }).done(function (response) {
        var row_el = $('.collector-cart #cart_' + response.cart_id);
        if (response.action === 'update') {
            // Update row totals
            row_el.find('input.qty').val(response.qty);
            row_el.find('.unit-price').html(response.unit_price);
            row_el.find('.total-price').html(response.total_price);
        } else if (response.action === 'remove') {
            row_el.remove();
        }

        if (response.hasOwnProperty('total')) {
            $('#cart-total').text(response.total);
        }

        $('#cart > ul').load('index.php?route=common/cart/info ul li');

        update_checkout(function (err) {
            if (err) {
                alert(err);
                el.prop('disabled', false);
                el.removeClass('disabled');
                return;
            }

            update_collector_checkout(function () {
                el.prop('disabled', false);
                el.removeClass('disabled');
            });
        });
    });
});

$(document).on('click', '.collector-cart .remove', function(e) {
    var el = $(e.currentTarget);
    var cart_id = el.closest('tr').data('cart-id');

    if (el.hasClass('disabled')) {
        e.preventDefault();
        return false;
    }

    el.prop('disabled', true);
    el.addClass('disabled');

    $.ajax({
        url: 'index.php?route=checkout/collector/cart_update',
        method: 'post',
        dataType: 'json',
        data: {
            cart_id: cart_id,
            qty: el.val()
        }
    }).always(function () {
        //
    }).done(function (response) {
        var cart_id = el.closest('tr').data('cart-id');

        var row_el = $('.collector-cart #cart_' + response.cart_id);
        if (response.action === 'update') {
            row_el.find('input.qty').val(response.qty);
            row_el.find('.unit-price').html(response.unit_price);
            row_el.find('.total-price').html(response.total_price);
        } else if (response.action === 'remove') {
            row_el.remove();
        }

        $('#cart > ul').load('index.php?route=common/cart/info ul li');

        update_checkout(function (err) {
            if (err) {
                alert(err);
                el.prop('disabled', false);
                el.removeClass('disabled');
                return;
            }

            update_collector_checkout(function () {
                el.prop('disabled', false);
                el.removeClass('disabled');
            });
        });
    });
});

// Country select
$(document).on( 'change', '#input-payment-country', function(e) {
    var el = $(e.currentTarget);
    var country_id = el.val();

    el.prop('disabled', true);
    $.ajax({
        url: 'index.php?route=checkout/collector/set_country',
        method: 'post',
        dataType: 'json',
        data: {
            country_id: country_id
        }
    }).done(function (response) {
        el.prop('disabled', false);

        // Reload page
        if (response.success) {
            self.location.href = location.href;
        } else {
            alert(response.message)
        }
    });
});

// Shipping select
$(document).on( 'click', '#collector-shipping [name="shipping_method"]', function(e) {
    var el = $(e.currentTarget);
    var method_code = $('#collector-shipping [name="shipping_method"]:checked').val();

    el.prop('disabled', true);
    set_shipping_method(method_code, function (response) {
        el.prop('disabled', false);

        get_totals(function (err) {
            update_collector_checkout();
        });
    });
});

$(document).on( 'click', '#change-customer', function(e) {
    var el = $(e.currentTarget);
    var customer_type = el.data('customer');

    el.prop('disabled', true);
    $.ajax({
        url: 'index.php?route=checkout/collector/set_customer_type',
        method: 'post',
        dataType: 'json',
        data: {
            customer_type: customer_type
        }
    }).done(function (response) {
        el.prop('disabled', false);

        // Reload page
        if (response.success) {
            self.location.href = location.href;
        } else {
            alert(response.message)
        }
    });
});

/**
 * Get Cart
 * @param callback
 * @returns {*}
 */
function get_cart(callback) {
    return $.ajax({
        url: 'index.php?route=checkout/collector/cart',
        dataType: 'html',
        success: function (html) {
            $('#collector-cart').html(html);
            callback(null, html);
        },
        error: function (xhr, ajaxOptions, thrownError) {
            callback(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
}

/**
 * Get Totals
 * @param callback
 * @returns {*}
 */
function get_totals(callback) {
    if (typeof callback === 'undefined') {
        callback = function(){};
    }

    return $.ajax({
        url: 'index.php?route=checkout/collector/totals',
        dataType: 'html',
        success: function (html) {
            $('#collector-totals').html(html);
            callback(null, html);
        },
        error: function (xhr, ajaxOptions, thrownError) {
            callback(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
}

/**
 * Get Shipping Methods
 * @param country_id
 * @param callback
 * @returns {*}
 */
function get_shipping_methods(country_id, callback) {
    if (typeof callback === 'undefined') {
        callback = function(){};
    }

    return $.ajax({
        url: 'index.php?route=checkout/collector/shipping_methods',
        method: 'get',
        dataType: 'html',
        data: {
            country_id: country_id
        }
    }).always(function () {
        callback(null);
    }).done(function (response) {
        $('#collector-shipping').html(response);
    });
}

/**
 * Update Collector Checkout
 * @param callback
 * @return {*}
 */
function update_collector_checkout(callback) {
    if (typeof callback === 'undefined') {
        callback = function(){};
    }

    window.collector.checkout.api.suspend();

    return $.ajax({
        url: 'index.php?route=checkout/collector/collector_update',
        method: 'post',
        dataType: 'json'
    }).done(function (response) {
        window.collector.checkout.api.resume();

        if (response.hasOwnProperty('success') && response.success) {
            if (response.hasOwnProperty('redirect')) {
                self.location.href = response.redirect;
                return;
            }
            callback(null);
        } else {
            callback(response.message);
            alert(response.message);
        }
    });
}

/**
 * Update Checkout
 * @param callback
 */
function update_checkout(callback) {
    if (typeof callback === 'undefined') {
        callback = function(){};
    }

    var method_code = false;
    if ($('#collector-shipping [name="shipping_method"]:checked').length > 0) {
        method_code = $('#collector-shipping [name="shipping_method"]:checked').val();
    }

    set_shipping_method(method_code, function (response) {
        async.parallel({
            cart: function(callback1) {
                get_cart(function (err, data) {
                    callback1(err, data)
                });
            },
            totals: function(callback1) {
                get_totals(function (err, data) {
                    callback1(err, data);
                });
            },
            shipping_methods: function (callback1) {
                setTimeout(function () {
                    var country_id = $('#input-payment-country').val();
                    get_shipping_methods(country_id, function (err) {
                        callback1(err);
                    });
                }, 1000);
            }
        }, function(err, results) {
            // Parallel is done
            callback(err, results);
        });
    });
}

/**
 * Set shipping method
 * @param method_code
 * @param callback
 * @returns {*}
 */
function set_shipping_method(method_code, callback) {
    if (typeof method_code === 'undefined' || !method_code) {
        return callback();
    }

    return $.ajax({
        url: 'index.php?route=checkout/collector/set_shipping_method',
        method: 'post',
        dataType: 'json',
        data: {
            shipping_method: method_code
        }
    }).done(function (response) {
        callback(response);
    }).fail(function (jqXHR, textStatus, errorThrown) {
        callback(errorThrown);
    });
}
