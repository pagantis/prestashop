{*
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integrations@pagantis.com>
 * @copyright 2019 Pagantis
 * @license   proprietary
 *}
{if $12X_IS_ENABLED}
    <div class="row">
        <div class="col-xs-12">
            <p class="payment_module Pagantis ps_version_{$12X_PS_VERSION|escape:'htmlall':'UTF-8'}">
                <a class="pagantis-checkout ps_version_{$12X_PS_VERSION|escape:'htmlall':'UTF-8'} locale_{$12X_LOCALE|escape:'htmlall':'UTF-8'}" href="{$12X_PAYMENT_URL|escape:'htmlall':'UTF-8'}" title="{$12X_TITLE|escape:'htmlall':'UTF-8'}">
                    {if $12X_PS_VERSION !== '1-7'}{$12X_TITLE|escape:'quotes'}&nbsp;{/if}
                    <span class="pagantisSimulator12x ps_version_{$12X_PS_VERSION|escape:'htmlall':'UTF-8'}"></span>

                </a>
            </p>
            <script type="text/javascript">
                function checkSimulatorContent12x() {
                    var pgContainer = document.getElementsByClassName("pagantisSimulator12x");
                    if(pgContainer.length > 0) {
                        var pgElement = pgContainer[0];
                        if (pgElement.innerHTML != '') {
                            return true;
                        }
                    }
                    return false;
                }

                function loadSimulator12x()
                {
                    window.PSSimulatorAttempts12x = window.PSSimulatorAttempts12x + 1;
                    if (window.PSSimulatorAttempts12x > 10 )
                    {
                        clearInterval(window.PSSimulatorId12x);
                        return true;
                    }

                    if (checkSimulatorContent12x()) {
                        clearInterval(window.PSSimulatorId12x);
                        return true;
                    }

                    if (typeof pgSDK == 'undefined') {
                        return false;
                    }
                    var sdk = pgSDK;

                    sdk.simulator.init({
                        type: {$12X_SIMULATOR_DISPLAY_TYPE_CHECKOUT|escape:'javascript':'UTF-8'},
                        locale: '{$12X_LOCALE|escape:'javascript':'UTF-8'}'.toLowerCase(),
                        country: '{$12X_COUNTRY|escape:'javascript':'UTF-8'}'.toLowerCase(),
                        publicKey: '{$12X_PUBLIC_KEY|escape:'javascript':'UTF-8'}',
                        selector: '.pagantisSimulator12x',
                        numInstalments: '{$12X_SIMULATOR_START_INSTALLMENTS|escape:'javascript':'UTF-8'}',
                        totalAmount: '{$12X_AMOUNT|escape:'javascript':'UTF-8'}'.replace('.', ','),
                        totalPromotedAmount: '{$12X_PROMOTED_AMOUNT|escape:'javascript':'UTF-8'}'.replace('.', ','),
                        amountParserConfig: {
                            thousandSeparator: '{$12X_SIMULATOR_THOUSAND_SEPARATOR|escape:'javascript':'UTF-8'}',
                            decimalSeparator: '{$12X_SIMULATOR_DECIMAL_SEPARATOR|escape:'javascript':'UTF-8'}',
                        }
                    });
                    return true;
                }
                window.PSSimulatorAttempts12x = 0;
                if (!loadSimulator12x()) {
                    window.PSSimulatorId12x = setInterval(function () {
                        loadSimulator12x();
                    }, 2000);
                }
            </script>
            <style>
                .pagantisSimulator12x {
                    display: inline-block;
                }
                .pagantisSimulator12x .mainImageLogo{
                    width: 20px;
                    height: 20px;
                }
                .pagantisSimulator12x.ps_version_1-5 {
                    padding-top: 0px;
                    margin-top: -15px;
                }
                .pagantisSimulator12x.ps_version_1-6 {
                    vertical-align: top;
                    margin-left: 20px;
                    margin-top: -5px;

                }
                .pagantisSimulator12x.ps_version_1-7 {
                    padding-top: 0px;
                }
                p.payment_module.Pagantis.ps_version_1-5 {
                    min-height: 0px;
                }
                p.payment_module.Pagantis.ps_version_1-7 {
                    margin-left: -5px;
                    margin-top: -15px;
                    margin-bottom: 0px;
                }
                p.payment_module a.pagantis-checkout {
                    background: url(https://cdn.digitalorigin.com/assets/master/logos/pg-favicon.png) 5px 5px no-repeat #fbfbfb;
                    background-size: 80px;
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
                    max-height: 90px;
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
                {$12X_SIMULATOR_CSS_CHECKOUT_PAGE_STYLES|escape:'javascript':'UTF-8'}
            </style>
        </div>
    </div>
{/if}