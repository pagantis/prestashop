{*
 * This file is part of the official Paga+Tarde module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2015-2016 Paga+Tarde
 * @license   proprietary
 *}
{if ($pmtIsEnabled && $pmtSimulatorIsEnabled)}
<script type="text/javascript" src="https://cdn.pagamastarde.com/js/pmt-v2/sdk.js"></script>
<script type="text/javascript">
    function findPriceSelector()    {
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

    window.onload = function() {
        if (typeof pmtSDK != 'undefined') {
            var price = null;
            var positionSelector = '{$pmtCSSSelector|escape:'quotes'}';
            var priceSelector = '{$pmtPriceSelector|escape:'quotes'}';
            if (positionSelector === 'default') {
                positionSelector = '.PmtSimulator';
            }
            if (priceSelector === 'default') {
                priceSelector = findPriceSelector();
                if (priceSelector === 'default') {
                    price = '{$amount|escape:'quotes'}'
                }
            }
            var options = {
                publicKey: '{$pmtPublicKey|escape:'quotes'}',
                selector: positionSelector,
                numInstalments: '{$pmtQuotesStart|escape:'quotes'}',
                type: {$pmtSimulatorType|escape:'quotes'},
                skin: {$pmtSimulatorSkin|escape:'quotes'},
                position: {$pmtSimulatorPosition|escape:'quotes'}
            };
            if (priceSelector !== 'default') {
                options.itemAmountSelector = priceSelector;
            }
            if (price != null) {
                options.totalAmount = price;
            }
            pmtSDK.simulator.init(options);
        }
    }
</script>
<div class="PmtSimulator"></div>
{/if}
