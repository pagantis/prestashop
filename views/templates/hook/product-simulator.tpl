{*
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integrations@pagantis.com>
 * @copyright 2015-2016 Pagantis
 * @license   proprietary
 *}
{if ($pagantisIsEnabled && $pagantisSimulatorIsEnabled)}
    <script>
        function loadSimulator()
        {
            if (typeof pgSDK != 'undefined') {
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

                pgSDK.product_simulator = {};
                pgSDK.product_simulator.id = 'product-simulator';
                pgSDK.product_simulator.publicKey = '{$pagantisPublicKey|escape:'quotes'}';
                pgSDK.product_simulator.selector = positionSelector;
                pgSDK.product_simulator.numInstalments = '{$pagantisQuotesStart|escape:'quotes'}';
                pgSDK.product_simulator.type = {$pagantisSimulatorType|escape:'quotes'};
                pgSDK.product_simulator.skin = {$pagantisSimulatorSkin|escape:'quotes'};
                pgSDK.product_simulator.position = {$pagantisSimulatorPosition|escape:'quotes'};

                if (priceSelector !== 'default') {
                    pgSDK.product_simulator.itemAmountSelector = priceSelector;
                }
                if (quantitySelector !== 'default' && quantitySelector !== 'none') {
                    pgSDK.product_simulator.itemQuantitySelector = quantitySelector;
                }
                if (price != null) {
                    pgSDK.product_simulator.itemAmount = price;
                }
                if (quantity != null) {
                    pgSDK.product_simulator.itemQuantity = quantity;
                }

                pgSDK.simulator.init(pgSDK.product_simulator);
                clearInterval(window.PSSimulatorId);
                return true;
            }
            return false;
        }
        if (!loadSimulator()) {
            window.PSSimulatorId = setInterval(function () {
                loadSimulator();
            }, 2000);
        }
    </script>
    <div class="pagantisSimulator"></div>
{/if}

