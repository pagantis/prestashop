{*
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integrations@pagantis.com>
 * @copyright 2015-2016 Pagantis
 * @license   proprietary
 *}
<form id="pagantis_form" action="{$paymentUrl|escape:'html'}"></form>
        {if version_compare($smarty.const._PS_VERSION_,'1.6.0.0','<')}
            <div class="payment_module" id="pagantis_payment_button">
                <a href="javascript:$('#pagantis_form').submit();" title="{$pagantisTitle|escape:'quotes'}">
                    {$pagantisTitle|escape:'quotes'}
                </a>
            </div>
        {else}
            <p class="payment_module pagantis" id="pagantis_payment_button">
                <a href="javascript:$('#pagantis_form').submit();" title="{$pagantisTitle|escape:'quotes'}">
                    {$pagantisTitle|escape:'quotes'}
                </a>
            </p>
        {/if}
        <script type="text/javascript">
            function checkSimulatorContent() {
                var pgContainer = document.getElementsByClassName("PagantisSimulator");
                if(typeof pgContainer != 'undefined' && typeof pgContainer[0] != 'undefined') {
                    var pgElement = pgContainer[0];
                    if (pgElement.innerHTML != '')
                    {
                        return true;
                    }
                }
                return false;
            }
            function checkSimulatorContainer() {
                var container = $('input[value="pagantis"]').parent().parent().find('.payment_content > p');
                if (container.length > 0) {
                    $('input[value="pagantis"]').parent().parent().find('.payment_content > p').addClass('pgSimulatorPlaceholder');
                    $(".PagantisSimulator").appendTo(".pgSimulatorPlaceholder");
                    clearInterval(window.PSSimulatorId);
                    return true;
                }
                return false;
            }

            function loadSimulator()
            {
                if (checkSimulatorContent() && checkSimulatorContainer()) {
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

                if (typeof sdk != 'undefined' && !checkSimulatorContent()) {
                    sdk.simulator.init({
                        locale: '{$locale|escape:'quotes'}'.toLowerCase(),
                        publicKey: '{$pagantisPublicKey|escape:'quotes'}',
                        selector: '.PagantisSimulator',
                        type: sdk.simulator.types.SELECTABLE,
                        totalAmount: '{$amount|escape:'quotes'}'
                    });
                }
                return false;
            }

            if (!loadSimulator()) {
                window.PSSimulatorId = setInterval(function () {
                    loadSimulator();
                }, 500);
            }
        </script>
        <style>
            img[src="/modules/onepagecheckoutps/views/img/payments/pagantis.png"] {
                content: url(/modules/onepagecheckoutps/views/img/payments/{$logo|escape:'quotes'});
            }
        </style>
<span class="PagantisSimulator"></span>
