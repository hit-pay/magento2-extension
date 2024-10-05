/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @api */
define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/action/place-order',
    'Magento_Checkout/js/model/payment/additional-validators',
    'ko',
], function ($,
        Component,
        placeOrderAction,
        additionalValidators,
        ko
        ) {
    'use strict';

    return Component.extend({

        defaults: {
            template: 'SoftBuild_HitPay/hitpay',
            visa: window.checkoutConfig.payment.hitpay.status.visa,
            master: window.checkoutConfig.payment.hitpay.status.master,
            american_express: window.checkoutConfig.payment.hitpay.status.american_express,
            apple_pay: window.checkoutConfig.payment.hitpay.status.apple_pay,
            google_pay: window.checkoutConfig.payment.hitpay.status.google_pay,
            paynow: window.checkoutConfig.payment.hitpay.status.paynow,
            grabpay: window.checkoutConfig.payment.hitpay.status.grabpay,
            wechatpay: window.checkoutConfig.payment.hitpay.status.wechatpay,
            alipay: window.checkoutConfig.payment.hitpay.status.alipay,
            shopeepay: window.checkoutConfig.payment.hitpay.status.shopeepay,
            fpx: window.checkoutConfig.payment.hitpay.status.fpx,
            zip: window.checkoutConfig.payment.hitpay.status.zip,
            atomeplus: window.checkoutConfig.payment.hitpay.status.atomeplus,
            unionbank: window.checkoutConfig.payment.hitpay.status.unionbank,
            qrph: window.checkoutConfig.payment.hitpay.status.qrph,
            pesonet: window.checkoutConfig.payment.hitpay.status.pesonet,
            gcash: window.checkoutConfig.payment.hitpay.status.gcash,
            billease: window.checkoutConfig.payment.hitpay.status.billease,
            eftpos: window.checkoutConfig.payment.hitpay.status.eftpos,
            maestro: window.checkoutConfig.payment.hitpay.status.maestro,
            alfamart: window.checkoutConfig.payment.hitpay.status.alfamart,
            indomaret: window.checkoutConfig.payment.hitpay.status.indomaret,
            dana: window.checkoutConfig.payment.hitpay.status.dana,
            gopay: window.checkoutConfig.payment.hitpay.status.gopay,
            linkaja: window.checkoutConfig.payment.hitpay.status.linkaja,
            ovo: window.checkoutConfig.payment.hitpay.status.ovo,
            qris: window.checkoutConfig.payment.hitpay.status.qris,
            danamononline: window.checkoutConfig.payment.hitpay.status.danamononline,
            permata: window.checkoutConfig.payment.hitpay.status.permata,
            bsi: window.checkoutConfig.payment.hitpay.status.bsi,
            bca: window.checkoutConfig.payment.hitpay.status.bca,
            bni: window.checkoutConfig.payment.hitpay.status.bni,
            bri: window.checkoutConfig.payment.hitpay.status.bri,
            cimb: window.checkoutConfig.payment.hitpay.status.cimb,
            doku: window.checkoutConfig.payment.hitpay.status.doku,
            mandiri: window.checkoutConfig.payment.hitpay.status.mandiri,
            akulaku: window.checkoutConfig.payment.hitpay.status.akulaku,
            kredivo: window.checkoutConfig.payment.hitpay.status.kredivo,
            philtrustbank: window.checkoutConfig.payment.hitpay.status.philtrustbank,
            allbank: window.checkoutConfig.payment.hitpay.status.allbank,
            aub: window.checkoutConfig.payment.hitpay.status.aub,
            chinabank: window.checkoutConfig.payment.hitpay.status.chinabank,
            instapay: window.checkoutConfig.payment.hitpay.status.instapay,
            landbank: window.checkoutConfig.payment.hitpay.status.landbank,
            metrobank: window.checkoutConfig.payment.hitpay.status.metrobank,
            pnb: window.checkoutConfig.payment.hitpay.status.pnb,
            queenbank: window.checkoutConfig.payment.hitpay.status.queenbank,
            rcbc: window.checkoutConfig.payment.hitpay.status.rcbc,
            tayocash: window.checkoutConfig.payment.hitpay.status.tayocash,
            ussc: window.checkoutConfig.payment.hitpay.status.ussc,
            bayad: window.checkoutConfig.payment.hitpay.status.bayad,
            cebuanalhuillier: window.checkoutConfig.payment.hitpay.status.cebuanalhuillier,
            ecpay: window.checkoutConfig.payment.hitpay.status.ecpay,
            palawan: window.checkoutConfig.payment.hitpay.status.palawan,
            bpi: window.checkoutConfig.payment.hitpay.status.bpi,
            psbank: window.checkoutConfig.payment.hitpay.status.psbank,
            robinsonsbank: window.checkoutConfig.payment.hitpay.status.robinsonsbank,
            diners_club: window.checkoutConfig.payment.hitpay.status.diners_club,
            discover: window.checkoutConfig.payment.hitpay.status.discover,
            doku_wallet: window.checkoutConfig.payment.hitpay.status.doku_wallet,
            grab_paylater: window.checkoutConfig.payment.hitpay.status.grab_paylater,
            favepay: window.checkoutConfig.payment.hitpay.status.favepay,
            shopback_paylater: window.checkoutConfig.payment.hitpay.status.shopback_paylater,
            duitnow: window.checkoutConfig.payment.hitpay.status.duitnow,
            touchngo: window.checkoutConfig.payment.hitpay.status.touchngo,
            boost: window.checkoutConfig.payment.hitpay.status.boost,
            pos_enabled: window.checkoutConfig.payment.hitpay.pos_enabled,
            terminal_ids: window.checkoutConfig.payment.hitpay.terminal_ids,
            only_one_terminal_id: window.checkoutConfig.payment.hitpay.only_one_terminal_id,
        },
        getSingleTerminalId: function () {
            if (this.pos_enabled && this.only_one_terminal_id) {
                return this.terminal_ids[0];
            }
        },
        getTerminals: function () {
            if (this.pos_enabled) {
                var terminalsDict = [];
                this.terminal_ids.forEach(element => {
                    terminalsDict.push({'terminal_id': element});
                });
                return ko.observableArray(terminalsDict)
            }
        },
        getInstructions: function () {
            return window.checkoutConfig.payment.instructions[this.item.method];
        },
        getHitpayPaymentOption: function() {
            var self = this;
            var hitpay_payment_option = '';
            if (self.pos_enabled) {
                hitpay_payment_option = $('input[name="hitpay_payment_option"]:checked').val();
            }
            return hitpay_payment_option;
            
        },
        getData: function () {
            return {
                "method": 'hitpay',
                "additional_data": {
                    'hitpay_payment_option': this.getHitpayPaymentOption(),
                }
            };
        },
        getHitpayLogoPath: function (logo) {
            return window.checkoutConfig.payment.hitpay.images[logo];
        },
        placeOrder: function (data, event) {
            if (event) {
                event.preventDefault();
            }
            var self = this,
                    placeOrder;
            if (additionalValidators.validate()) {
                this.isPlaceOrderActionAllowed(false);
                placeOrder = placeOrderAction(this.getData(), false, this.messageContainer);

                $.when(placeOrder).fail(function () {
                    self.isPlaceOrderActionAllowed(true);
                }).done(this.afterPlaceOrder.bind(this));
                return true;
            }
            return false;
        },
        afterPlaceOrder: function () {
            var self = this;
            var method = this.getCode();
            var urlRedirect = window.checkoutConfig.payment[method].redirectUrl;
            
            if (window.checkoutConfig.payment[method].dropIn == 1) {
                $.getJSON(urlRedirect, {drop_in_ajax: 1}, function (apiResponse) {
                    $.ajaxSetup({
                        cache: false
                    });
                    if (apiResponse.status == 'error') {
                        alert(apiResponse.message);
                        window.location.replace(apiResponse.redirect_url);
                    } else{
                        if (!window.HitPay.inited) {
                            window.HitPay.init(apiResponse.payment_url, {
                              domain: apiResponse.domain,
                              apiDomain: apiResponse.apiDomain,
                            },
                            {
                              onClose: self.onHitpayDropInClose,
                              onSuccess: self.onHitpayDropInSuccess,
                              onError: self.onHitpayDropInError
                            });
                        }
 
                        hitpayRedirectUrl = apiResponse.redirect_url;
                        hitpayPaymentId = apiResponse.payment_request_id;

                        window.HitPay.toggle({
                            paymentRequest: apiResponse.payment_request_id,          
                        });
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown) { 
                    alert('Site server error while creating a payment request.'+errorThrown);
                });
            } else {
                window.location.replace(urlRedirect);
            }
        },
        onHitpayDropInSuccess: function (data) {
            location.href = hitpayRedirectUrl+'?reference='+hitpayPaymentId+'&status='
        },
        onHitpayDropInClose: function (data) {
            location.href = hitpayRedirectUrl+'?reference='+hitpayPaymentId+'&status=canceled'
        },
        onHitpayDropInError: function (error) {
            alert('Site server error while creating a payment request. Error: ' + error);
            location.href = window.checkoutConfig.payment[method].cartUrl;
        }
    });
});