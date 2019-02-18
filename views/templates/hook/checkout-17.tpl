{*
 * This file is part of the official Paga+Tarde module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2015-2016 Paga+Tarde
 * @license   proprietary
 *}
{if ($pmtIsEnabled && $pmtSimulatorIsEnabled)}
    <script type="text/javascript">
        if (typeof pmtSDK != 'undefined') {
            var positionSelector = '.PmtSimulator';
            var price = '{$amount|escape:'quotes'}'
            var options = {
                publicKey: '{$pmtPublicKey|escape:'quotes'}',
                selector: positionSelector,
                numInstalments: '{$pmtQuotesStart|escape:'quotes'}',
                type: {$pmtSimulatorType|escape:'quotes'},
                skin: {$pmtSimulatorSkin|escape:'quotes'},
                position: {$pmtSimulatorPosition|escape:'quotes'},
                totalAmount: price
            };
            pmtSDK.simulator.init(options);
        }
    </script>
    <div class="PmtSimulator"></div>
{/if}

