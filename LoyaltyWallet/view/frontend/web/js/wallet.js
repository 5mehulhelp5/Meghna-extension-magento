define([
    'jquery',
    'mage/url',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/get-totals'
], function ($, urlBuilder, quote, getTotalsAction) {
    'use strict';

    return function (config, element) {

        console.log('Codilar Wallet component initialized.');

        $('body').on('click', '#apply-wallet-btn', function (e) {
            e.preventDefault();

            var button = $(this);
            var amount = parseFloat($('#wallet-amount-input').val());

            if (!amount || amount <= 0) {
                $('#wallet-message')
                    .css('color', '#e02b2b')
                    .text('Please enter a valid wallet amount.');

                return;
            }

            var applyUrl = urlBuilder.build(
                'loyalty/wallet/ajaxapply'
            );

            button.prop('disabled', true);

            $.ajax({
                url: applyUrl,
                type: 'POST',
                data: {
                    wallet_amount: amount
                },
                dataType: 'json',
                showLoader: true,

                success: function (response) {

                    console.log(
                        'Wallet Apply Response:',
                        response
                    );

                    if (!response.success) {

                        $('#wallet-message')
                            .css('color', '#e02b2b')
                            .text(response.message);

                        return;
                    }

                    $('#wallet-message')
                        .css('color', '#006400')
                        .text(response.message);

                    /*
                     * Reload Magento checkout totals.
                     */
                    var deferred = $.Deferred();

                    getTotalsAction([], deferred);

                    $.when(deferred).done(function () {

                        console.log(
                            'Checkout totals reloaded.'
                        );

                        var currentTotals = quote.getTotals();

                        console.log(
                            'Current checkout totals:',
                            currentTotals
                        );

                        if (currentTotals) {

                            console.log(
                                'Grand Total:',
                                currentTotals.grand_total
                            );

                            console.log(
                                'Wallet Total:',
                                currentTotals.wallet
                            );
                        }

                    }).fail(function () {

                        console.error(
                            'Unable to reload checkout totals.'
                        );

                    });

                },

                error: function (xhr, status, error) {

                    console.error(
                        'Wallet AJAX Error:',
                        error
                    );

                    $('#wallet-message')
                        .css('color', '#e02b2b')
                        .text(
                            'An error occurred while applying the wallet.'
                        );
                },

                complete: function () {
                    button.prop('disabled', false);
                }
            });
        });
    };
});
