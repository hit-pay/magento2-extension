/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Paypal/js/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/customer-data'
    ],
    function ($, Component, setPaymentMethodAction, additionalValidators, quote, customerData) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'SoftBuild_HitPay/payment/form',
                transactionResult: '',
                paynow: window.checkoutConfig.payment.hitpay_gateway.paynow,
                wechat: window.checkoutConfig.payment.hitpay_gateway.wechat,
                cards: window.checkoutConfig.payment.hitpay_gateway.cards,
            },

            initObservable: function () {

                this._super()
                    .observe([
                        'transactionResult',
                    ]);
                return this;
            },

            getCode: function() {
                return 'hitpay_gateway';
            },

            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'transaction_result': this.transactionResult()
                    }
                };
            },

            getWebPayImagePath: function () {
                return window.checkoutConfig.payment.hitpay_gateway.images.paynow;
            },

            getWechatImagePath: function () {
                return window.checkoutConfig.payment.hitpay_gateway.images.wechat;
            },

            getCardsImagePath: function () {
                return window.checkoutConfig.payment.hitpay_gateway.images.cards;
            },

            goToRedirect: function () {
                window.location.href = '/hitpay/redirect';
            },

            /** Redirect to hitpay */
            continueToPay: function () {
                if (additionalValidators.validate()) {
                    //update payment method information if additional data was changed
                    this.selectPaymentMethod();
                    setPaymentMethodAction(this.messageContainer).done(
                        function () {
                            customerData.invalidate(['cart']);
                            $.mage.redirect(
                                '/hitpay/redirect'
                            );
                        }
                    );

                    return false;
                }
            },

            getTransactionResults: function() {
                return _.map(window.checkoutConfig.payment.hitpay_gateway.transactionResults, function(value, key) {
                    return {
                        'value': key,
                        'transaction_result': value
                    }
                });
            }
        });
    }
);