{*
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integrations@pagantis.com>
 * @copyright 2019 Pagantis
 * @license   proprietary
 *}
<form id="pagantis_form_{$12X_CODE|escape:'htmlall':'UTF-8'}" action="{$12X_PAYMENT_URL|escape:'htmlall':'UTF-8'}"></form>
{if version_compare($smarty.const._PS_VERSION_,'1.6.0.0','<') && $12X_PAYMENT_URL}
    <div class="payment_module pagantis{$12X_CODE|escape:'htmlall':'UTF-8'}" id="pagantis_payment_button">
        <a href="javascript:$('#pagantis_form').submit();" title="{$12X_TITLE|escape:'htmlall':'UTF-8'}">
            {$12X_TITLE|escape:'htmlall':'UTF-8'}
        </a>
    </div>
{else}
    <p class="payment_module pagantis pagantis{$12X_CODE|escape:'htmlall':'UTF-8'}" id="pagantis_payment_button">
        <a href="javascript:$('#pagantis_form').submit();" title="{$12X_TITLE|escape:'htmlall':'UTF-8'}">
            {$12X_TITLE|escape:'htmlall':'UTF-8'}
        </a>
    </p>
{/if}
<script type="text/javascript">
    function checkSimulatorContent() {
        var pgContainer = document.getElementsByClassName("pagantisSimulator{$12X_CODE|escape:'htmlall':'UTF-8'}");
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
<span class="pagantisSimulator{$12X_CODE|escape:'htmlall':'UTF-8'}></span>
<style>
    .pgSimulatorPlaceholder {
        display: inline-block;
    }
    {$12X_SIMULATOR_CSS_CHECKOUT_PAGE_STYLES|escape:'javascript':'UTF-8'}
</style>