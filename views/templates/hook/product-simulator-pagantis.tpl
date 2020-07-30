{*
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integrations@pagantis.com>
 * @copyright 2019 Pagantis
 * @license   proprietary
*}
{if ($PAGANTIS_IS_ENABLED && $PAGANTIS_SIMULATOR_IS_ENABLED)}
    <style>
        .pagantis-promotion {
            font-size: 11px;
            display: inline-block;
            width: 100%;
            text-align: center;
            color: #828282;
            max-width: 370px;
        }
        .pagantis-promotion .pmt-no-interest{
            color: #00c1d5
        }
        .pagantisSimulatorPagantis {
            clear: both;
        }
        .pagantisSimulatorPagantis > div.preposition {
            display:inline-block;
            vertical-align: top;
            margin-right: 5px;
            width: inherit;
            height: 15px;
        }
        .pagantisSimulatorPagantis > div {
            height: 35px;
            display:inline-block;
            width: 90%
        }
        iframe#pg-iframe-product-simulator {
            display: block;
        }
        {$PAGANTIS_SIMULATOR_CSS_PRODUCT_PAGE_STYLES|escape:'javascript':'UTF-8'}
    </style>
    <script>
        function findPriceSelector()
        {
            var priceDOM = document.getElementById("our_price_display");
            if (priceDOM != null) {
                return '#our_price_display';
            } else {
                priceDOM = document.querySelector(".current-price span[itemprop=price]")
                if (priceDOM != null) {
                    return ".current-price span[itemprop=price]";
                }
            }

            return 'default';
        }

        function findQuantitySelector()
        {
            var quantityDOM = document.getElementById("quantity_wanted");
            if (quantityDOM != null) {
                return '#quantity_wanted';
            }
            return 'default';
        }
        function checkSimulatorContent() {
            // if simulator is into an iframe
            if(document.getElementById("pg-iframe-product-simulator") != null){
                return true;
            }
            // if simulator is inline
            if(document.getElementById("pg-sim-custom-product-simulator") != null){
                return true;
            }

            return false;
        }

        function loadSimulator()
        {
            window.PSSimulatorAttempts = window.PSSimulatorAttempts + 1;
            if (window.PSSimulatorAttempts > 20 )
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
            var price = null;
            var quantity = null;
            var priceSelector = '{$PAGANTIS_SIMULATOR_CSS_PRICE_SELECTOR|escape:'javascript':'UTF-8'}';
            var quantitySelector = '{$PAGANTIS_SIMULATOR_CSS_QUANTITY_SELECTOR|escape:'javascript':'UTF-8'}';
            var sdkPositionSelector = '{$PAGANTIS_SIMULATOR_CSS_POSITION_SELECTOR|escape:'javascript':'UTF-8'}';

            if ('{$PAGANTIS_SIMULATOR_CSS_POSITION_SELECTOR|escape:'javascript':'UTF-8'}' === 'default') {
                sdkPositionSelector = '.pagantisSimulatorPagantis';
            }

            if (priceSelector === 'default') {
                priceSelector = findPriceSelector();
                if (priceSelector === 'default') {
                    price = '{$PAGANTIS_AMOUNT|escape:'javascript':'UTF-8'}'
                }
            }

            if (quantitySelector === 'default') {
                quantitySelector = findQuantitySelector();
                if (quantitySelector === 'default') {
                    quantity = '1'
                }
            }

            sdk.product_simulator = {};
            sdk.product_simulator.id = 'product-simulator';
            sdk.product_simulator.locale = '{$PAGANTIS_LOCALE|escape:'javascript':'UTF-8'}'.toLowerCase();
            sdk.product_simulator.country = '{$PAGANTIS_COUNTRY|escape:'javascript':'UTF-8'}'.toLowerCase();
            sdk.product_simulator.publicKey = '{$PAGANTIS_PUBLIC_KEY|escape:'javascript':'UTF-8'}';
            sdk.product_simulator.selector = sdkPositionSelector;
            sdk.product_simulator.numInstalments = '{$PAGANTIS_SIMULATOR_START_INSTALLMENTS|escape:'javascript':'UTF-8'}';
            sdk.product_simulator.type = {$PAGANTIS_SIMULATOR_DISPLAY_TYPE|escape:'javascript':'UTF-8'};
            sdk.product_simulator.skin = {$PAGANTIS_SIMULATOR_DISPLAY_SKIN|escape:'javascript':'UTF-8'};
            sdk.product_simulator.position = {$PAGANTIS_SIMULATOR_DISPLAY_CSS_POSITION|escape:'javascript':'UTF-8'};
            sdk.product_simulator.amountParserConfig =  {
                thousandSeparator: '{$PAGANTIS_SIMULATOR_THOUSAND_SEPARATOR|escape:'javascript':'UTF-8'}',
                decimalSeparator: '{$PAGANTIS_SIMULATOR_DECIMAL_SEPARATOR|escape:'javascript':'UTF-8'}',
            };

            if (priceSelector !== 'default') {
                sdk.product_simulator.itemAmountSelector = priceSelector;
                {if $PAGANTIS_IS_PROMOTED_PRODUCT == true}
                sdk.product_simulator.itemPromotedAmountSelector = priceSelector;
                {/if}
            }
            if (quantitySelector !== 'default' && quantitySelector !== 'none') {
                sdk.product_simulator.itemQuantitySelector = quantitySelector;
            }
            if (price != null) {
                sdk.product_simulator.itemAmount = price.toString().replace('.', ',');
                {if $PAGANTIS_IS_PROMOTED_PRODUCT == true}
                sdk.product_simulator.itemPromotedAmount = price.toString().replace('.', ',');
                {/if}
            }
            if (quantity != null) {
                sdk.product_simulator.itemQuantity = quantity;
            }

            var sim = sdk.simulator.init(sdk.product_simulator);
            if (checkSimulatorContent()) {
                clearInterval(window.PSSimulatorId);
                return true;
            }
            return false;
        }
        window.PSSimulatorAttempts = 0;
        if (typeof window.PSSimulatorId == "undefined") {
            window.PSSimulatorId = setInterval(function () {
                loadSimulator();
            }, 2000);
        }
    </script>
    {if $PAGANTIS_IS_PROMOTED_PRODUCT == true}
            <span class="pagantis-promotion ps_version_{$PAGANTIS_PS_VERSION|escape:'htmlall':'UTF-8'}" id="pagantis-promotion-extra">{$PAGANTIS_PROMOTION_EXTRA nofilter}</span>
    {/if}
    <div class="pagantisSimulatorPagantis"></div>
{/if}
