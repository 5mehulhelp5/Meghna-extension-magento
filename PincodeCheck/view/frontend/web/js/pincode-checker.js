define(['jquery', 'mage/url'], function ($, urlBuilder) {
    'use strict';

    return function (config, element) {
        $(element).find('#check-pincode-btn').on('click', function () {
            var pincode = $(element).find('#pincode-input').val().trim();
            var ajaxUrl = urlBuilder.build('pincodecheck/index/check');

            if (!pincode) {
                $(element).find('#pincode-result').html('<span class="pincode-error-msg">Please enter a PIN code.</span>');
                return;
            }

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: { pincode: pincode },
                success: function (response) {
                    if (response.success) {
                        var resultHtml = '';
                        if (response.is_available === true) {
                            resultHtml = '<span class="pincode-success-msg">Delivery available<br>Expected delivery: ' + response.expected_days + '</span>';
                        } else {
                            resultHtml = '<span class="pincode-error-msg">Delivery unavailable</span>';
                        }
                        $(element).find('#pincode-result').html(resultHtml);
                    }
                },
                error: function () {
                    $(element).find('#pincode-result').html('<span class="pincode-error-msg">An error occurred. Please try again.</span>');
                }
            });
        });
    };
});
