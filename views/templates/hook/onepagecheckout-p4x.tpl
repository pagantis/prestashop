{*
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integrations@pagantis.com>
 * @copyright 2019 Pagantis
 * @license   proprietary
 *}

<form id="pagantis_form_{$PAGANTIS_CODE|escape:'htmlall':'UTF-8'}" action="{$PAGANTIS_PAYMENT_URL|escape:'htmlall':'UTF-8'}"></form>
{if version_compare($smarty.const._PS_VERSION_,'1.6.0.0','<') && $PAGANTIS_PAYMENT_URL}
    <div class="payment_module pagantis{$PAGANTIS_CODE|escape:'htmlall':'UTF-8'}" id="pagantis_payment_button">
        <a href="javascript:$('#pagantis_form').submit();" title="{$PAGANTIS_TITLE|escape:'htmlall':'UTF-8'}">
            {$PAGANTIS_TITLE|escape:'htmlall':'UTF-8'}
        </a>
    </div>
{else}
    <p class="payment_module pagantis pagantis{$PAGANTIS_CODE|escape:'htmlall':'UTF-8'}" id="pagantis_payment_button">
        <a href="javascript:$('#pagantis_form').submit();" title="{$PAGANTIS_TITLE|escape:'htmlall':'UTF-8'}">
            {$PAGANTIS_TITLE|escape:'htmlall':'UTF-8'}
        </a>
    </p>
{/if}
<script type="text/javascript">
    function checkSimulatorContent() {
        var pgContainer = document.getElementsByClassName("pagantisSimulator{$PAGANTIS_CODE|escape:'htmlall':'UTF-8'}");
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
                type: '{$PAGANTIS_SIMULATOR_DISPLAY_TYPE_CHECKOUT|escape:'javascript':'UTF-8'}',
                locale: '{$PAGANTIS_LOCALE|escape:'javascript':'UTF-8'}'.toLowerCase(),
                country: '{$PAGANTIS_COUNTRY|escape:'javascript':'UTF-8'}'.toLowerCase(),
                publicKey: '{$PAGANTIS_PUBLIC_KEY|escape:'javascript':'UTF-8'}',
                selector: '.pagantisSimulatorPagantis',
                numInstalments: '{$PAGANTIS_SIMULATOR_START_INSTALLMENTS|escape:'javascript':'UTF-8'}',
                totalAmount: '{$PAGANTIS_AMOUNT|escape:'javascript':'UTF-8'}'.replace('.', ','),
                totalPromotedAmount: '{$PAGANTIS_PROMOTED_AMOUNT|escape:'javascript':'UTF-8'}'.replace('.', ','),
                amountParserConfig: {
                    thousandSeparator: '{$PAGANTIS_SIMULATOR_THOUSAND_SEPARATOR|escape:'javascript':'UTF-8'}',
                    decimalSeparator: '{$PAGANTIS_SIMULATOR_DECIMAL_SEPARATOR|escape:'javascript':'UTF-8'}',
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
<span class="pagantisSimulator{$PAGANTIS_CODE|escape:'htmlall':'UTF-8'}"></span>
<style>
    .pgSimulatorPlaceholder {
        display: inline-block;
    }
    '{$PAGANTIS_SIMULATOR_CSS_CHECKOUT_PAGE_STYLES|escape:'javascript':'UTF-8'}'
</style>
