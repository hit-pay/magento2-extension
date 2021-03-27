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
                type: 'hitpay_gateway',
                component: 'SoftBuild_HitPay/js/view/payment/method-renderer/hitpay_gateway'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
