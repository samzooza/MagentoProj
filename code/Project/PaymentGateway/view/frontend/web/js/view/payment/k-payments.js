/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
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
        // var isContextCheckout = window.checkoutConfig.payment.paypalExpress.isContextCheckout,
        //     paypalExpress = 'Magento_Paypal/js/view/payment/method-renderer' +
        //         (isContextCheckout ? '/in-context/checkout-express' : '/paypal-express');
        // rendererList.push(
        //     {
        //         type: 'paypal_express',
        //         component: paypalExpress,
        //         config: window.checkoutConfig.payment.paypalExpress.inContextConfig
        //     },

        rendererList.push(
            {
                type: 'sample_gateway',
                component: 'Project_PaymentGateway/js/view/payment/method-renderer/iframe-methods'
            },
            {
                type: 'payflow_link',
                component: 'Project_PaymentGateway/js/view/payment/method-renderer/iframe-methods'
            },
            {
                type: 'payflow_advanced',
                component: 'Project_PaymentGateway/js/view/payment/method-renderer/iframe-methods'
            },
            {
                type: 'hosted_pro',
                component: 'Project_PaymentGateway/js/view/payment/method-renderer/iframe-methods'
            }
        );

        /** Add view logic here if needed */
        return Component.extend({});
    }
);
