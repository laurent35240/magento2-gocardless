define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'gocardless',
                component: 'Laurent35240_GoCardless/js/view/payment/method-renderer/gocardless'
            }
        );
        return Component.extend({});
    }
);