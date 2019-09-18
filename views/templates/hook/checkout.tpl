{*
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integrations@pagantis.com>
 * @copyright 2015-2016 Pagantis
 * @license   proprietary
 *}
{if $pagantisIsEnabled}
<div class="row">
    <div class="col-xs-12">
        <p class="payment_module Pagantis ps_version_{$ps_version|escape:'quotes'}">
            <a class="pagantis-checkout ps_version_{$ps_version|escape:'quotes'} locale_{$locale|escape:'quotes'}" href="{$paymentUrl|escape:'html'}" title="{$pagantisTitle|escape:'quotes'}">
                {$pagantisTitle|escape:'quotes'}
                <span class="pagantisSimulator ps_version_{$ps_version|escape:'quotes'}"></span>
            </a>
        </p>
        <script type="text/javascript">
            function checkSimulatorContent() {
                var pgContainer = document.getElementsByClassName("pagantisSimulator");
                if(pgContainer.length > 0) {
                    var pgElement = pgContainer[0];
                    if (pgElement.innerHTML != '') {
                        return true;
                    }
                }
                return false;
            }

            function loadSimulator()
            {
                window.PSSimulatorAttempts = window.attempts + 1;
                if (window.attempts > 4 )
                {
                    clearInterval(window.PSSimulatorId);
                    return true;
                }

                if (checkSimulatorContent()) {
                    clearInterval(window.PSSimulatorId);
                    return true;
                }

                if ('{$locale|escape:'quotes'}' == 'ES') {
                    if (typeof pmtSDK == 'undefined') {
                        return false;
                    }
                    var sdk = pmtSDK;
                } else {
                    if (typeof pgSDK == 'undefined') {
                        return false;
                    }
                    var sdk = pgSDK;
                }

                if (sdk != 'undefined') {
                    sdk.simulator.init({
                        locale: '{$locale|escape:'quotes'}'.toLowerCase(),
                        country: '{$locale|escape:'quotes'}'.toLowerCase(),
                        publicKey: '{$pagantisPublicKey|escape:'quotes'}',
                        selector: '.pagantisSimulator',
                        totalAmount: '{$amount|escape:'quotes'}'.replace('.', ','),
                        totalPromotedAmount: '{$promotedAmount|escape:'quotes'}'.replace('.', ','),
                        amountParserConfig: {
                            thousandSeparator: '{$pagantisSimulatorThousandSeparator|escape:'quotes'}',
                            decimalSeparator: '{$pagantisSimulatorDecimalSeparator|escape:'quotes'}',
                        }
                    });
                    return false;
                }
                return true;
            }
            window.PSSimulatorAttempts = 0;
            if (!loadSimulator()) {
                window.PSSimulatorId = setInterval(function () {
                    loadSimulator();
                }, 2000);
            }
        </script>
        <style>
            .pagantisSimulator {
                max-width: 300px;
                display: block;
                margin-left: -85px;
                padding-top: 10px;
            }
            .pagantisSimulator.ps_version_1-5 {
                padding-top: 0px;
                margin-top: -15px;
            }
            .pagantisSimulator.ps_version_1-7 {
                padding-top: 0px;
                margin-top: -35px;
                margin-left: -48px;
            }
            p.payment_module.Pagantis {
                min-height: 150px;
            }
            p.payment_module a.pagantis-checkout {
                background: url(/modules/pagantis/views/img/logo_pagantis.png) no-repeat;
                background-position: 4px;
                background-position-y: 25px;
                background-size: 95px;
            }
            p.payment_module a.pagantis-checkout.locale_ES{
                background: url(/modules/pagantis/views/img/logo_pagamastarde.png) no-repeat;
                background-position: 15px;
                background-position-y: 10px;
                background-size: 80px;
            }
            p.payment_module a.pagantis-checkout.ps_version_1-7 {
                background: none;
                font-size: 0px;
            }
            .payment-option img[src*='/modules/pagantis/views/img/'] {
                height: 20px;
                padding-left: 5px;
            }
            p.payment_module a.pagantis-checkout.ps_version_1-6 {
                background-color: #fbfbfb;
            }
            p.payment_module a.pagantis-checkout.ps_version_1-5 {
                height: 60px;
                padding-left: 99px;
                margin-top: -10px;
                line-height: 90px;
            }
            p.payment_module a:hover {
                background-color: #f6f6f6;
            }
            p.payment_module.Pagantis.ps_version_1-7 {
                min-height: 0px;
            }
        </style>
    </div>
</div>
{/if}