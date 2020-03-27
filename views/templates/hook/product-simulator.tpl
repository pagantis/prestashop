{*
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integrations@pagantis.com>
 * @copyright 2019 Pagantis
 * @license   proprietary
*}
{if ($pagantisIsEnabled && $pagantisSimulatorIsEnabled)}
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
        .pagantisSimulator {
            clear: both;
        }
        .pagantisSimulator > div.preposition {
            display:inline-block;
            vertical-align: top;
            margin-right: 5px;
            width: inherit;
            height: 15px;
        }
        .pagantisSimulator > div {
            height: 35px;
            display:inline-block;
            width: 90%
        }
        {$pagantisSimulatorStyles|escape:'javascript':'UTF-8'}
    </style>
    <script>
        function checkSimulatorContent(clearDiv = false) {
            var pgContainer = document.getElementsByClassName("pagantisSimulator");
            if(pgContainer.length > 0) {
                var pgElement = pgContainer[0];
                if (pgElement.innerHTML != '')
                {
                    return true;
                }
                if (clearDiv) {
                    pgElement.innerHTML = '';
                    return true
                }
            }
            return false;
        }

        function loadSimulator()
        {
            window.PSSimulatorAttempts = window.attempts + 1;
            if (window.attempts > 4 )
            {
                clearInterval(window.PSSimulatorId);
                checkSimulatorContent(true);
                return true;
            }
            if (checkSimulatorContent()) {
                clearInterval(window.PSSimulatorId);
                checkSimulatorContent(true);
                return true;
            }
            if (typeof pgSDK == 'undefined') {
                return false;
            }
            var sdk = pgSDK;

            var price = null;
            var quantity = null;
            var type = '{$pagantisSimulatorType|escape:'javascript':'UTF-8'}';
            var positionSelector = '{$pagantisCSSSelector|escape:'javascript':'UTF-8'}';
            var priceSelector = '{$pagantisPriceSelector|escape:'javascript':'UTF-8'}';

            var quantitySelector = '{$pagantisQuantitySelector|escape:'javascript':'UTF-8'}';
            if (positionSelector === 'default') {
                positionSelector = '.pagantisSimulator';

            }

            if ((type ===  'sdk.simulator.types.SELECTABLE_TEXT_CUSTOM' || type === 'sdk.simulator.types.PRODUCT_PAGE')
            && '{$pagantisCSSSelector|escape:'javascript':'UTF-8'}' === 'default') {
                positionSelector = '.our_price_display';
            }

            if (priceSelector === 'default') {
                priceSelector = findPriceSelector();
                if (priceSelector === 'default') {
                    price = '{$amount|escape:'javascript':'UTF-8'}'
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
            sdk.product_simulator.locale = '{$locale|escape:'javascript':'UTF-8'}'.toLowerCase();
            sdk.product_simulator.country = '{$country|escape:'javascript':'UTF-8'}'.toLowerCase();
            sdk.product_simulator.publicKey = '{$pagantisPublicKey|escape:'javascript':'UTF-8'}';
            sdk.product_simulator.selector = positionSelector;
            sdk.product_simulator.numInstalments = '{$pagantisQuotesStart|escape:'javascript':'UTF-8'}';
            sdk.product_simulator.type = {$pagantisSimulatorType|escape:'javascript':'UTF-8'};
            sdk.product_simulator.skin = {$pagantisSimulatorSkin|escape:'javascript':'UTF-8'};
            sdk.product_simulator.position = {$pagantisSimulatorPosition|escape:'javascript':'UTF-8'};
            sdk.product_simulator.amountParserConfig =  {
                thousandSeparator: '{$pagantisSimulatorThousandSeparator|escape:'javascript':'UTF-8'}',
                decimalSeparator: '{$pagantisSimulatorDecimalSeparator|escape:'javascript':'UTF-8'}',
            };

            if (priceSelector !== 'default') {
                sdk.product_simulator.itemAmountSelector = priceSelector;
                {if $isPromotedProduct == true}
                sdk.product_simulator.itemPromotedAmountSelector = priceSelector;
                {/if}
            }
            if (quantitySelector !== 'default' && quantitySelector !== 'none') {
                sdk.product_simulator.itemQuantitySelector = quantitySelector;
            }
            if (price != null) {
                sdk.product_simulator.itemAmount = price.toString().replace('.', ',');
                {if $isPromotedProduct == true}
                sdk.product_simulator.itemPromotedAmount = price.toString().replace('.', ',');
                {/if}
            }
            if (quantity != null) {
                sdk.product_simulator.itemQuantity = quantity;
            }

            sdk.simulator.init(sdk.product_simulator);
            if (checkSimulatorContent()) {
                return true;
            }
            return false;
        }
        window.PSSimulatorAttempts = 0;
        if (!loadSimulator()) {
            window.PSSimulatorId = setInterval(function () {
                loadSimulator();
            }, 1000);
        }
    </script>
    {if $isPromotedProduct == true}
        <span class="pagantis-promotion ps_version_{$ps_version|escape:'htmlall':'UTF-8'}" id="pagantis-promotion-extra">{$pagantisPromotionExtra nofilter}</span>
    {/if}
    <div class="pagantisSimulator"></div>
{/if}