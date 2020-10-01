/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'ko',
        'Project_PaymentGateway/js/model/iframe',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (Component, ko, iframe, fullScreenLoader) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Project_PaymentGateway/payment/iframe-methods',
                //paymentReady: false
                transactionResult: ''
            },
            redirectAfterPlaceOrder: false,
            isInAction: iframe.isInAction,

            /**
             * @return {exports}
             */
            initObservable: function () {
                this._super()
                    //.observe('paymentReady');
                    .observe([
                        'transactionResult'
                    ]);
                return this;
            },

            /**
             * @return {*}
             */
            isPaymentReady: function () {
                return this.paymentReady();
            },

            /**
             * Get action url for payment method iframe.
             * @returns {String}
             */
            getActionUrl: function () {
                // return this.isInAction() ? window.checkoutConfig.payment.paypalIframe.actionUrl[this.getCode()] : '';
                return this.isInAction() ? 'https://dev-kpaymentgateway-services.kasikornbank.com/card/v2/loadconfig' : '';
            },

            /**
             * Places order in pending payment status.
             */
            placePendingPaymentOrder: function () {
                if (this.placeOrder()) {
                    fullScreenLoader.startLoader();
                    this.isInAction(true);
                    // capture all click events
                    document.addEventListener('click', iframe.stopEventPropagation, true);
                }
            },

            getPlaceOrderDeferredObject: function () {
                var self = this;
                return this._super()
                    .fail(
                        function () {
                            fullScreenLoader.stopLoader();
                            self.isInAction(false);
                            document.removeEventListener('click', iframe.stopEventPropagation, true);
                        }
                    );
            },

            /**
             * After place order callback
             */
            afterPlaceOrder: function () {
                if (this.iframeIsLoaded) {
                    document.getElementById(this.getCode() + '-iframe')
                        .contentWindow.location.reload();
                }

                this.paymentReady(true);
                this.iframeIsLoaded = true;
                this.isPlaceOrderActionAllowed(true);
                fullScreenLoader.stopLoader();
            },

            /**
             * Hide loader when iframe is fully loaded.
             */
            iframeLoaded: function () {
                fullScreenLoader.stopLoader();
            },

            getCode: function() {
                return 'sample_gateway';
            },

            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'transaction_result': this.transactionResult()
                    }
                };
            },

            getTransactionResults: function() {
                return _.map(window.checkoutConfig.payment.sample_gateway.transactionResults, function(value, key) {
                    return {
                        'value': key,
                        'transaction_result': value
                    }
                });
            }
        });
    }
);
