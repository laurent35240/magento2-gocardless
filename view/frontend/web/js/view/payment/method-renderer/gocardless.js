define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Paypal/js/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Customer/js/customer-data'
    ],
    function ($,
              Component,
              setPaymentMethodAction,
              additionalValidators,
              customerData) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Laurent35240_GoCardless/payment/gocardless'
            },

            continueToGoCardless: function () {
                if (additionalValidators.validate()) {
                    //update payment method information if additional data was changed
                    this.selectPaymentMethod();
                    setPaymentMethodAction(this.messageContainer).done(
                        function () {
                            customerData.invalidate(['cart']);
                            $.mage.redirect('/gocardless/redirect/start');
                        }
                    );

                    return false;
                }
            }
        });
    }
);