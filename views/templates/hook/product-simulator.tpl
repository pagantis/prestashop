{*
 * This file is part of the official Paga+Tarde module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2015-2016 Paga+Tarde
 * @license   proprietary
 *}
{if ($pmtIsEnabled && $pmtSimulatorIsEnabled)}
    <script>
        if (typeof pmtSDK != 'undefined') {
            var price = null;
            var quantity = null;
            var positionSelector = '{$pmtCSSSelector|escape:'quotes'}';
            var priceSelector = '{$pmtPriceSelector|escape:'quotes'}';
            var quantitySelector = '{$pmtQuantitySelector|escape:'quotes'}';

            if (positionSelector === 'default') {
                positionSelector = '.PmtSimulator';
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
            pmtSDK.product_simulator.publicKey = '{$pmtPublicKey|escape:'quotes'}';
            pmtSDK.product_simulator.selector = positionSelector;
            pmtSDK.product_simulator.numInstalments = '{$pmtQuotesStart|escape:'quotes'}';
            pmtSDK.product_simulator.type = {$pmtSimulatorType|escape:'quotes'};
            pmtSDK.product_simulator.skin = {$pmtSimulatorSkin|escape:'quotes'};
            pmtSDK.product_simulator.position = {$pmtSimulatorPosition|escape:'quotes'};

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
        }
    </script>
    <div class="PmtSimulator"></div>
{/if}
