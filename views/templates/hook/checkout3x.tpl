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
            <p class="payment_module Pagantis3x ps_version_{$ps_version|escape:'htmlall':'UTF-8'}">
                <a class="pagantis-checkout3x ps_version_{$ps_version|escape:'htmlall':'UTF-8'} locale_{$locale|escape:'htmlall':'UTF-8'}" href="{$paymentUrl|escape:'htmlall':'UTF-8'}&product=3x" title="{$pagantisTitle|escape:'htmlall':'UTF-8'}">
                    {if $ps_version !== '1-7'}3x Product&nbsp;{/if}
                    <span class="pagantisSimulator3x 3x ps_version_{$ps_version|escape:'htmlall':'UTF-8'}"></span>
                </a>
            </p>
            <script type="text/javascript">
                function checkSimulatorContent3x() {
                    var pgContainer = document.getElementsByClassName("pagantisSimulator3x");
                    if(pgContainer.length > 0) {
                        var pgElement = pgContainer[0];
                        if (pgElement.innerHTML != '') {
                            return true;
                        }
                    }
                    return false;
                }

                function loadSimulator3x()
                {
                    window.PSSimulatorAttempts3x = window.attempts + 1;
                    if (window.attempts > 4 )
                    {
                        clearInterval(window.PSSimulatorId3x);
                        return true;
                    }

                    if (checkSimulatorContent3x()) {
                        clearInterval(window.PSSimulatorId3x);
                        return true;
                    }

                    if (typeof pgSDK == 'undefined') {
                        return false;
                    }
                    var sdk = pgSDK;

                    sdk.simulator.init({
                        id: 'sim3x',
                        type: {$pagantisSimulatorType|escape:'javascript':'UTF-8'},
                        locale: '{$locale|escape:'javascript':'UTF-8'}'.toLowerCase(),
                        country: '{$country|escape:'javascript':'UTF-8'}'.toLowerCase(),
                        publicKey: '{$pagantisPublicKey|escape:'javascript':'UTF-8'}',
                        selector: '.pagantisSimulator3x',
                        numInstalments: '{$pagantisQuotesStart|escape:'javascript':'UTF-8'}',
                        totalAmount: '{$amount|escape:'javascript':'UTF-8'}'.replace('.', ','),
                        totalPromotedAmount: '{$promotedAmount|escape:'javascript':'UTF-8'}'.replace('.', ','),
                        amountParserConfig: {
                            thousandSeparator: '{$pagantisSimulatorThousandSeparator|escape:'javascript':'UTF-8'}',
                            decimalSeparator: '{$pagantisSimulatorDecimalSeparator|escape:'javascript':'UTF-8'}',
                        }
                    });
                    return true;
                }
                window.PSSimulatorAttempts3x = 0;
                if (!loadSimulator3x()) {
                    window.PSSimulatorId3x = setInterval(function () {
                        loadSimulator3x();
                    }, 2000);
                }
            </script>
            <style>
                .pagantisSimulator3x {
                    display: inline-block;
                }
                .pagantisSimulator3x.ps_version_1-5 {
                    padding-top: 0px;
                    margin-top: -15px;
                }
                .pagantisSimulator3x.ps_version_1-6 {
                    vertical-align: top;
                    margin-left: 20px;
                    margin-top: -5px;
                }
                .pagantisSimulator3x.ps_version_1-7 {
                    padding-top: 0px;
                }
                p.payment_module.Pagantis3x.ps_version_1-5 {
                    min-height: 0px;
                }
                p.payment_module.Pagantis3x.ps_version_1-7 {
                    margin-left: -5px;
                    margin-top: -15px;
                    margin-bottom: 0px;
                }
                p.payment_module a.pagantis-checkout3x {
                    background: url(https://www.weswap.com/content/uploads/2019/08/Contactless-Button-1.png) 5px 5px no-repeat #fbfbfb;
                    background-size: 80px;
                }
                p.payment_module a.pagantis-checkout3x.ps_version_1-7 {
                    background: none;
                }
                .payment-option img[src*='weswap'] {
                    height: 24px;
                    padding-left: 5px;
                }
                p.payment_module a.pagantis-checkout3x.ps_version_1-6 {
                    background-color: #fbfbfb;
                }
                p.payment_module a.pagantis-checkout3x.ps_version_1-6:after {
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
                p.payment_module a.pagantis-checkout3x.ps_version_1-5 {
                    height: 90px;
                    padding-left: 99px;
                    padding-top: 45px;
                }
                p.payment_module a:hover {
                    background-color: #f6f6f6;
                }
                {$pagantisSimulatorStyles|escape:'javascript':'UTF-8'}
            </style>
        </div>
    </div>
{/if}