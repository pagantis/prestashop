{*
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integrations@pagantis.com>
 * @copyright 2019 Pagantis
 * @license   proprietary
*}
{if ($P12X_IS_ENABLED && $P12X_SIMULATOR_IS_ENABLED)}
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
        .pagantisSimulator12x {
            clear: both;
        }
        .pagantisSimulator12x > div.preposition {
            display:inline-block;
            vertical-align: top;
            margin-right: 5px;
            width: inherit;
            height: 15px;
        }
        .pagantisSimulator12x > div {
            height: 35px;
            display:inline-block;
            width: 90%
        }
        {$P12X_SIMULATOR_CSS_PRODUCT_PAGE_STYLES|escape:'javascript':'UTF-8'}
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
            var priceSelector = '{$P12X_SIMULATOR_CSS_PRICE_SELECTOR|escape:'javascript':'UTF-8'}';
            var quantitySelector = '{$P12X_SIMULATOR_CSS_QUANTITY_SELECTOR|escape:'javascript':'UTF-8'}';
            var sdkPositionSelector = '{$P12X_SIMULATOR_CSS_POSITION_SELECTOR|escape:'javascript':'UTF-8'}';

            if ('{$P12X_SIMULATOR_CSS_POSITION_SELECTOR|escape:'javascript':'UTF-8'}' === 'default') {
                sdkPositionSelector = '.pagantisSimulator12x';
            }

            if (priceSelector === 'default') {
                priceSelector = findPriceSelector();
                if (priceSelector === 'default') {
                    price = '{$P12X_AMOUNT|escape:'javascript':'UTF-8'}'
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
            sdk.product_simulator.locale = '{$P12X_LOCALE|escape:'javascript':'UTF-8'}'.toLowerCase();
            sdk.product_simulator.country = '{$P12X_COUNTRY|escape:'javascript':'UTF-8'}'.toLowerCase();
            sdk.product_simulator.publicKey = '{$P12X_PUBLIC_KEY|escape:'javascript':'UTF-8'}';
            sdk.product_simulator.selector = sdkPositionSelector;
            sdk.product_simulator.numInstalments = '{$P12X_SIMULATOR_START_INSTALLMENTS|escape:'javascript':'UTF-8'}';
            sdk.product_simulator.type = {$P12X_SIMULATOR_DISPLAY_TYPE|escape:'javascript':'UTF-8'};
            sdk.product_simulator.skin = {$P12X_SIMULATOR_DISPLAY_SKIN|escape:'javascript':'UTF-8'};
            sdk.product_simulator.position = {$P12X_SIMULATOR_DISPLAY_CSS_POSITION|escape:'javascript':'UTF-8'};
            sdk.product_simulator.amountParserConfig =  {
                thousandSeparator: '{$P12X_SIMULATOR_THOUSAND_SEPARATOR|escape:'javascript':'UTF-8'}',
                decimalSeparator: '{$P12X_SIMULATOR_DECIMAL_SEPARATOR|escape:'javascript':'UTF-8'}',
            };

            if (priceSelector !== 'default') {
                sdk.product_simulator.itemAmountSelector = priceSelector;
                {if $P12X_IS_PROMOTED_PRODUCT == true}
                sdk.product_simulator.itemPromotedAmountSelector = priceSelector;
                {/if}
            }
            if (quantitySelector !== 'default' && quantitySelector !== 'none') {
                sdk.product_simulator.itemQuantitySelector = quantitySelector;
            }
            if (price != null) {
                sdk.product_simulator.itemAmount = price.toString().replace('.', ',');
                {if $P12X_IS_PROMOTED_PRODUCT == true}
                sdk.product_simulator.itemPromotedAmount = price.toString().replace('.', ',');
                {/if}
            }
            if (quantity != null) {
                sdk.product_simulator.itemQuantity = quantity;
            }

            var sim = sdk.simulator.init(sdk.product_simulator);
            console.log("renderizado el sim", sim);
            if (checkSimulatorContent()) {
                clearInterval(window.PSSimulatorId);
                console.log("exit4")
                return true;
            }
            return false;
        }
        window.PSSimulatorAttempts = 0;
        console.log("---->", typeof window.PSSimulatorId);
        if (typeof window.PSSimulatorId == "undefined") {
            console.log("----2--->", typeof window.PSSimulatorId, 'creado');
            window.PSSimulatorId = setInterval(function () {
                console.log('----3---> interval ', window.PSSimulatorId, " attemps ", window.PSSimulatorAttempts)
                loadSimulator();
            }, 2000);
        }
    </script>
    {if $P12X_IS_PROMOTED_PRODUCT == true}
            <span class="pagantis-promotion ps_version_{$P12X_PS_VERSION|escape:'htmlall':'UTF-8'}" id="pagantis-promotion-extra">{$P12X_PROMOTION_EXTRA nofilter}</span>
    {/if}
    <div class="pagantisSimulator12x"></div>
{/if}
