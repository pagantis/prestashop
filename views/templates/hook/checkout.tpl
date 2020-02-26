{*
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integrations@pagantis.com>
 * @copyright 2019 Pagantis
 * @license   proprietary
 *}
{if $pagantisIsEnabled}
    <div class="row">
        <div class="col-xs-12">
            <p class="payment_module Pagantis ps_version_{$ps_version|escape:'htmlall':'UTF-8'}">
                <a class="pagantis-checkout ps_version_{$ps_version|escape:'htmlall':'UTF-8'} locale_{$locale|escape:'htmlall':'UTF-8'}" href="{$paymentUrl|escape:'htmlall':'UTF-8'}" title="{$pagantisTitle|escape:'htmlall':'UTF-8'}">
                    {if $ps_version !== '1-7'}{$pagantisTitle|escape:'javascript':'UTF-8'}&nbsp;{/if}
                    <span class="pagantisSimulator ps_version_{$ps_version|escape:'htmlall':'UTF-8'}"></span>
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

                    if (typeof pgSDK == 'undefined') {
                        return false;
                    }
                    var sdk = pgSDK;

                    sdk.simulator.init({
                        type: sdk.simulator.types.SELECTABLE_TEXT_CUSTOM,
                        locale: '{$locale|escape:'javascript':'UTF-8'}'.toLowerCase(),
                        country: '{$country|escape:'javascript':'UTF-8'}'.toLowerCase(),
                        publicKey: '{$pagantisPublicKey|escape:'javascript':'UTF-8'}',
                        selector: '.pagantisSimulator',
                        totalAmount: '{$amount|escape:'javascript':'UTF-8'}'.replace('.', ','),
                        totalPromotedAmount: '{$promotedAmount|escape:'javascript':'UTF-8'}'.replace('.', ','),
                        amountParserConfig: {
                            thousandSeparator: '{$pagantisSimulatorThousandSeparator|escape:'javascript':'UTF-8'}',
                            decimalSeparator: '{$pagantisSimulatorDecimalSeparator|escape:'javascript':'UTF-8'}',
                        }
                    });
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
                    display: inline-block;
                }
                .pagantisSimulator.ps_version_1-5 {
                    padding-top: 0px;
                    margin-top: -15px;
                }
                .pagantisSimulator.ps_version_1-7 {
                    padding-top: 0px;
                }
                p.payment_module.Pagantis {
                    min-height: 150px;
                }
                p.payment_module.Pagantis.ps_version_1-5 {
                    min-height: 0px;
                }
                p.payment_module.Pagantis.ps_version_1-7 {
                    min-height: 0px;
                    margin-left: -5px;
                    margin-top: -15px;
                }
                p.payment_module a.pagantis-checkout {
                    background: url(https://cdn.digitalorigin.com/assets/master/logos/pg-favicon.png) 5px 5px no-repeat #fbfbfb;
                    background-size: 90px;
                }
                p.payment_module a.pagantis-checkout.ps_version_1-7 {
                    background: none;
                }
                .payment-option img[src*='cdn.digitalorigin.com'] {
                    height: 18px;
                    padding-left: 5px;
                    content:url('https://cdn.digitalorigin.com/assets/master/logos/pg.png');

                }
                p.payment_module a.pagantis-checkout.ps_version_1-6 {
                    background-color: #fbfbfb;
                }
                p.payment_module a.pagantis-checkout.ps_version_1-6:after {
                    display: block;
                    content: "\f054";
                    position: absolute;
                    right: 15px;
                    margin-top: -11px;
                    top: 50%;
                    font-family: "FontAwesome";
                    font-size: 25px;
                    height: 22px;
                    width: 14px;
                    color: #777;
                }
                p.payment_module a.pagantis-checkout.ps_version_1-5 {
                    height: 90px;
                    padding-left: 99px;
                    padding-top: 45px;
                }
                p.payment_module a:hover {
                    background-color: #f6f6f6;
                }
            </style>
        </div>
    </div>
{/if}