{*
 * This file is part of the official Clearpay module for PrestaShop.
 *
 * @author    Clearpay <integrations@clearpay.com>
 * @copyright 2019 Clearpay
 * @license   proprietary
*}
{if ($CLEARPAY_IS_ENABLED && $CLEARPAY_SIMULATOR_IS_ENABLED)}
    <style>
        .clearpay-promotion {
            font-size: 11px;
            display: inline-block;
            width: 100%;
            text-align: center;
            color: #828282;
            max-width: 370px;
        }
        .clearpay-promotion .pmt-no-interest{
            color: #00c1d5
        }
        .clearpaySimulatorClearpay {
            clear: both;
        }
        .clearpaySimulatorClearpay > div.preposition {
            display:inline-block;
            vertical-align: top;
            margin-right: 5px;
            width: inherit;
            height: 15px;
        }
        .clearpaySimulatorClearpay > div {
            height: 35px;
            display:inline-block;
            width: 90%
        }
        iframe#pg-iframe-product-simulator {
            display: block;
        }
        {$CLEARPAY_SIMULATOR_CSS_PRODUCT_PAGE_STYLES|escape:'javascript':'UTF-8'}
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
            var priceSelector = '{$CLEARPAY_SIMULATOR_CSS_PRICE_SELECTOR|escape:'javascript':'UTF-8'}';
            var quantitySelector = '{$CLEARPAY_SIMULATOR_CSS_QUANTITY_SELECTOR|escape:'javascript':'UTF-8'}';
            var sdkPositionSelector = '{$CLEARPAY_SIMULATOR_CSS_POSITION_SELECTOR|escape:'javascript':'UTF-8'}';

            if ('{$CLEARPAY_SIMULATOR_CSS_POSITION_SELECTOR|escape:'javascript':'UTF-8'}' === 'default') {
                sdkPositionSelector = '.clearpaySimulatorClearpay';
            }

            if (priceSelector === 'default') {
                priceSelector = findPriceSelector();
                if (priceSelector === 'default') {
                    price = '{$CLEARPAY_AMOUNT|escape:'javascript':'UTF-8'}'
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
            sdk.product_simulator.locale = '{$CLEARPAY_LOCALE|escape:'javascript':'UTF-8'}'.toLowerCase();
            sdk.product_simulator.country = '{$CLEARPAY_COUNTRY|escape:'javascript':'UTF-8'}'.toLowerCase();
            sdk.product_simulator.publicKey = '{$CLEARPAY_PUBLIC_KEY|escape:'javascript':'UTF-8'}';
            sdk.product_simulator.selector = sdkPositionSelector;
            sdk.product_simulator.numInstalments = '{$CLEARPAY_SIMULATOR_START_INSTALLMENTS|escape:'javascript':'UTF-8'}';
            sdk.product_simulator.type = {$CLEARPAY_SIMULATOR_DISPLAY_TYPE|escape:'javascript':'UTF-8'};
            sdk.product_simulator.skin = {$CLEARPAY_SIMULATOR_DISPLAY_SKIN|escape:'javascript':'UTF-8'};
            sdk.product_simulator.position = {$CLEARPAY_SIMULATOR_DISPLAY_CSS_POSITION|escape:'javascript':'UTF-8'};
            sdk.product_simulator.amountParserConfig =  {
                thousandSeparator: '{$CLEARPAY_SIMULATOR_THOUSAND_SEPARATOR|escape:'javascript':'UTF-8'}',
                decimalSeparator: '{$CLEARPAY_SIMULATOR_DECIMAL_SEPARATOR|escape:'javascript':'UTF-8'}',
            };

            if (priceSelector !== 'default') {
                sdk.product_simulator.itemAmountSelector = priceSelector;
                {if $CLEARPAY_IS_PROMOTED_PRODUCT == true}
                sdk.product_simulator.itemPromotedAmountSelector = priceSelector;
                {/if}
            }
            if (quantitySelector !== 'default' && quantitySelector !== 'none') {
                sdk.product_simulator.itemQuantitySelector = quantitySelector;
            }
            if (price != null) {
                sdk.product_simulator.itemAmount = price.toString().replace('.', ',');
                {if $CLEARPAY_IS_PROMOTED_PRODUCT == true}
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
        window.PSSimulatorReloadAttempts = 0;
        setInterval(function () {
            if (!checkSimulatorContent()
                && typeof pgSDK.product_simulator != "undefined"
                && window.PSSimulatorReloadAttempts <= 5
            ) {
                pgSDK.simulator.init(pgSDK.product_simulator);
                window.PSSimulatorReloadAttempts = window.PSSimulatorReloadAttempts + 1;
            }
        }, 2000);
    </script>
    {if $CLEARPAY_IS_PROMOTED_PRODUCT == true}
            <span class="clearpay-promotion ps_version_{$CLEARPAY_PS_VERSION|escape:'htmlall':'UTF-8'}" id="clearpay-promotion-extra">{$CLEARPAY_PROMOTION_EXTRA nofilter}</span>
    {/if}
    <div class="clearpaySimulatorClearpay"></div>
{/if}
