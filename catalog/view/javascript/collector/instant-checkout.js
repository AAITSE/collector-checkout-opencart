(function ($) {
    var checkout_initiated = false,
        btn = null;

    document.addEventListener('collectorInstantTokenRequested', function() {
        checkout_initiated = true;

        var el = $(btn).closest('div'),
            product_id = el.find('[name="product_id"]').first().val(),
            qty = el.find('[name="quantity"]').first().val();

        $.ajax({
            url: 'index.php?route=checkout/collector/instant_purchase',
            method: 'post',
            dataType: 'json',
            data: {
                customer_token: window.collector.instant.api.getCustomerToken(),
                product_id: product_id,
                qty: qty
            }
        }).always(function () {
            $(btn).prop('disabled', false);
        }).done(function (response) {
            if (!response.success) {
                alert(response.message);
                return;
            }

            window.collector.instant.api.setPublicToken(response.public_token, 'INSTANT_CHECKOUT_MODAL' );
        });
    });

    $(document).on( 'click', '.collector-buy', function(e) {
        e.preventDefault();
        btn = e.currentTarget;
        $(btn).prop('disabled', true);

        if (checkout_initiated === true) {
            update_checkout();
        }

        $('#instant-checkout-lightbox-container').modal('show');
        showInstantCheckout();

        return false;
    });

    $(document).on('ready', function () {
        $('#instant-checkout-lightbox-container').on('hidden.bs.modal', function (e) {
            console.log('hideInstantCheckout');
            hideInstantCheckout();
        });
    });

    // Display Instant Buy Modal function
    function showInstantCheckout() {
        window.collector.instant.api.expand('INSTANT_CHECKOUT_MODAL');
    }

    // Hide Instant Buy Modal function
    function hideInstantCheckout() {
        window.collector.instant.api.collapse('INSTANT_CHECKOUT_MODAL');
    }

    // Update checkout function
    function update_checkout() {
        window.collector.instant.api.suspend();

        var el = $(btn).closest('div'),
            product_id = el.find('[name="product_id"]').first().val(),
            qty = el.find('[name="quantity"]').first().val();

        $.ajax({
            url: 'index.php?route=checkout/collector/instant_purchase_update',
            method: 'post',
            dataType: 'json',
            data: {
                customer_token: window.collector.instant.api.getCustomerToken(),
                product_id: product_id,
                qty: qty
            }
        }).always(function () {
            $(btn).prop('disabled', false);
        }).done(function (response) {
            if (!response.success) {
                alert(response.message);
                return;
            }

            console.log('Instant checkout update ok');
            window.collector.instant.api.resume();
        });
    }
}(jQuery));

