{*
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integration@pagantis.com>
 * @copyright 2015-2016 Pagantis
 * @license   proprietary
 *}
{if ($pagantisIsEnabled && $pagantisSimulatorIsEnabled)}
    <script>
        function loadSimulator()
        {
            if (typeof pmtSDK != 'undefined') {
                var price = null;
                var quantity = null;
                var positionSelector = '{$pagantisCSSSelector|escape:'quotes'}';
                var priceSelector = '{$pagantisPriceSelector|escape:'quotes'}';
                var quantitySelector = '{$pagantisQuantitySelector|escape:'quotes'}';

                if (positionSelector === 'default') {
                    positionSelector = '.pagantisSimulator';
                }

                if (priceSelector === 'default') {
                    priceSelector = findPriceSelector();
                    if (priceSelector === 'default') {
                        price = '{$amount|escape:'quotes'}'
                    }
                }

                if (quantitySelector === 'default') {
                    quantitySelector = findQuantitySelector();
                    if (quantitySelector === 'default') {
                        quantity = '1'
                    }
                }

                pmtSDK.product_simulator = {};
                pmtSDK.product_simulator.id = 'product-simulator';
                pmtSDK.product_simulator.publicKey = '{$pagantisPublicKey|escape:'quotes'}';
                pmtSDK.product_simulator.selector = positionSelector;
                pmtSDK.product_simulator.numInstalments = '{$pagantisQuotesStart|escape:'quotes'}';
                pmtSDK.product_simulator.type = {$pagantisSimulatorType|escape:'quotes'};
                pmtSDK.product_simulator.skin = {$pagantisSimulatorSkin|escape:'quotes'};
                pmtSDK.product_simulator.position = {$pagantisSimulatorPosition|escape:'quotes'};

                if (priceSelector !== 'default') {
                    pmtSDK.product_simulator.itemAmountSelector = priceSelector;
                }
                if (quantitySelector !== 'default' && quantitySelector !== 'none') {
                    pmtSDK.product_simulator.itemQuantitySelector = quantitySelector;
                }
                if (price != null) {
                    pmtSDK.product_simulator.itemAmount = price;
                }
                if (quantity != null) {
                    pmtSDK.product_simulator.itemQuantity = quantity;
                }

                pmtSDK.simulator.init(pmtSDK.product_simulator);
                clearInterval(window.PSSimulatorId);
            }
        }
        window.PSSimulatorId = setInterval(function () {
            loadSimulator();
        }, 2000);
    </script>
    <div class="pagantisSimulator"></div>
{/if}
