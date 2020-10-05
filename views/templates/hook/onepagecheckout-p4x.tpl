{*
 * This file is part of the official Clearpay module for PrestaShop.
 *
 * @author    Clearpay <integrations@clearpay.com>
 * @copyright 2019 Clearpay
 * @license   proprietary
 *}
<form id="clearpay_form_{$P4X_CODE|escape:'htmlall':'UTF-8'}" action="{$P4X_PAYMENT_URL|escape:'htmlall':'UTF-8'}">
    <input type="hidden" name="product" id="product" value="{$P4X_CODE|escape:'htmlall':'UTF-8'}">
</form>
{if version_compare($smarty.const._PS_VERSION_,'1.6.0.0','<') && $P4X_PAYMENT_URL}
    <div class="payment_module clearpay{$P4X_CODE|escape:'htmlall':'UTF-8'}" id="clearpay_payment_button">
        <a href="javascript:$('#clearpay_form_P4X').submit();" title="{$P4X_TITLE|escape:'htmlall':'UTF-8'}">
            {$P4X_TITLE|escape:'htmlall':'UTF-8'}
        </a>
    </div>
{else}
    <p class="payment_module clearpay clearpay{$P4X_CODE|escape:'htmlall':'UTF-8'}" id="clearpay_payment_button">
        <a href="javascript:$('#clearpay_form_P4X').submit();" title="{$P4X_TITLE|escape:'htmlall':'UTF-8'}">
            {$P4X_TITLE|escape:'htmlall':'UTF-8'}
        </a>
    </p>
{/if}
<script type="text/javascript">
    function checkSimulatorContent() {
        var pgContainer = document.getElementsByClassName("clearpaySimulator{$P4X_CODE|escape:'htmlall':'UTF-8'}");
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
        var container = $('input[value="clearpay"]').parent().parent().find('.payment_content > p');
        if (container.length > 0) {
            $('input[value="clearpay"]').parent().parent().find('.payment_content > p').addClass('pgSimulatorPlaceholder');
            $(".clearpaySimulatorP4X").appendTo(".pgSimulatorPlaceholder");
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
                id: 'checkout-simulator',
                type: {$P4X_SIMULATOR_DISPLAY_TYPE_CHECKOUT|escape:'javascript':'UTF-8'},
                locale: '{$P4X_LOCALE|escape:'javascript':'UTF-8'}'.toLowerCase(),
                country: '{$P4X_COUNTRY|escape:'javascript':'UTF-8'}'.toLowerCase(),
                publicKey: '{$P4X_PUBLIC_KEY|escape:'javascript':'UTF-8'}',
                selector: '.clearpaySimulatorP4X',
                numInstalments: '{$P4X_SIMULATOR_START_INSTALLMENTS|escape:'javascript':'UTF-8'}',
                totalAmount: '{$P4X_AMOUNT|escape:'javascript':'UTF-8'}'.replace('.', ','),
                totalPromotedAmount: '{$P4X_PROMOTED_AMOUNT|escape:'javascript':'UTF-8'}'.replace('.', ','),
                amountParserConfig: {
                    thousandSeparator: '{$P4X_SIMULATOR_THOUSAND_SEPARATOR|escape:'javascript':'UTF-8'}',
                    decimalSeparator: '{$P4X_SIMULATOR_DECIMAL_SEPARATOR|escape:'javascript':'UTF-8'}',
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
<span class="clearpaySimulator{$P4X_CODE|escape:'htmlall':'UTF-8'}"></span>
<style>
    .pgSimulatorPlaceholder {
        display: inline-block;
    }
    {$P4X_SIMULATOR_CSS_CHECKOUT_PAGE_STYLES|escape:'javascript':'UTF-8'}
</style>
