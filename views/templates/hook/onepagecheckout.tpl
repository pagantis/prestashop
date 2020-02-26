{*
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integrations@pagantis.com>
 * @copyright 2019 Pagantis
 * @license   proprietary
 *}
<form id="pagantis_form" action="{$paymentUrl|escape:'htmlall':'UTF-8'}"></form>
{if version_compare($smarty.const._PS_VERSION_,'1.6.0.0','<')}
    <div class="payment_module" id="pagantis_payment_button">
        <a href="javascript:$('#pagantis_form').submit();" title="{$pagantisTitle|escape:'htmlall':'UTF-8'}">
            {$pagantisTitle|escape:'javascript':'UTF-8'}
        </a>
    </div>
{else}
    <p class="payment_module pagantis" id="pagantis_payment_button">
        <a href="javascript:$('#pagantis_form').submit();" title="{$pagantisTitle|escape:'htmlall':'UTF-8'}">
            {$pagantisTitle|escape:'javascript':'UTF-8'}
        </a>
    </p>
{/if}
<script type="text/javascript">
    function checkSimulatorContent() {
        var pgContainer = document.getElementsByClassName("pagantisSimulator");
        if(pgContainer.length > 0) {
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
            $(".pagantisSimulator").appendTo(".pgSimulatorPlaceholder");
            clearInterval(window.PSSimulatorId);
            return true;
        }
        window.PSSimulatorAttempts = window.attempts + 1;
        if (window.attempts > 4 )
        {
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

        if (typeof pgSDK == 'undefined') {
            return false;
        }
        var sdk = pgSDK;

        if (!checkSimulatorContent()) {
            sdk.simulator.init({
                type: sdk.simulator.types.SELECTABLE_TEXT_CUSTOM,
                locale: '{$locale|escape:'javascript':'UTF-8'}'.toLowerCase(),
                country: '{$country|escape:'javascript':'UTF-8'}'.toLowerCase(),
                publicKey: '{$pagantisPublicKey|escape:'javascript':'UTF-8'}',
                selector: '.pagantisSimulator',
                totalAmount: '{$amount|escape:'javascript':'UTF-8'}'.replace('.', ','),
                amountParserConfig: {
                    thousandSeparator: '{$pagantisSimulatorThousandSeparator|escape:'javascript':'UTF-8'}',
                    decimalSeparator: '{$pagantisSimulatorDecimalSeparator|escape:'javascript':'UTF-8'}',
                }
            });
        }
        return false;
    }

    window.PSSimulatorAttempts = 0;
    if (!loadSimulator()) {
        window.PSSimulatorId = setInterval(function () {
            loadSimulator();
        }, 500);
    }
</script>
<span class="pagantisSimulator"></span>
<style>
    .pgSimulatorPlaceholder {
        display: inline-block;
    }
</style>