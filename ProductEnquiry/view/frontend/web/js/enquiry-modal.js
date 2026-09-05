define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'mage/validation'
], function ($, modal) {
    'use strict';

    return function (config, element) {
        var $enquiryBtn = $(element);
        var $modalContainer = $('#product-enquiry-modal-content');
        var $form = $('#product-enquiry-form');

        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: $.mage.__('Product Enquiry'),
            buttons: [{
                text: $.mage.__('Submit Enquiry'),
                class: 'action primary submit',
                click: function () {
                    $form.submit();
                }
            }]
        };

        modal(options, $modalContainer);

        $enquiryBtn.on('click', function () {
            // Clear any old messages when opening the modal
            $('#enquiry-message-container').html('');
            var currentSku = $('div.product-info-price [itemprop="sku"]').text().trim() || config.defaultSku;
            $('#enquiry-product-sku').val(currentSku);
            $modalContainer.modal('openModal');
        });

        $form.off('submit').on('submit', function (e) {
            e.preventDefault();

            if ($form.validation() && $form.validation('isValid')) {
                $.ajax({
                    url: config.submitUrl,
                    type: 'POST',
                    data: $form.serialize(),
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            // 1. Close the popup modal
                            $modalContainer.modal('closeModal');

                            // 2. Reset the form fields for next time
                            $form[0].reset();

                            // 3. Create the Magento-style green success message HTML
                            var successHtml = '<div class="message-success success message" style="margin: 15px 0;"><div>' +
                                $.mage.__('Thank you! Your product enquiry has been submitted successfully. We will get back to you shortly.') +
                                '</div></div>';

                            // 4. Inject it right above the product title/price info so it's guaranteed to be seen
                            if ($('.product-info-main').length) {
                                $('.product-info-main').prepend(successHtml);
                            } else {
                                $('main.page-main').prepend(successHtml);
                            }

                            // Optional: Auto-hide the success message after 6 seconds so it doesn't stay forever
                            setTimeout(function() {
                                $('.message-success').fadeOut('slow', function() {
                                    $(this).remove();
                                });
                            }, 6000);

                        } else {
                            alert(response.message || $.mage.__('Error submitting enquiry. Please try again.'));
                        }
                    }

                });
            }
        });
    };
});
