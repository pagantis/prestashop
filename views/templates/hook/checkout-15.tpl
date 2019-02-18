{*
 * This file is part of the official Paga+Tarde module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2015-2016 Paga+Tarde
 * @license   proprietary
 *}
{if $pmtIsEnabled}
<div class="row">
    <div class="col-xs-12">
        <p class="payment_module">
            <a class="paylater-checkout ps_version_{$ps_version}" href="{$paymentUrl|escape:'html'}" title="{$pmtTitle|escape:'quotes'}">
                {$pmtTitle|escape:'quotes'}
            </a>
        </p>
        {if $pmtSimulatorIsEnabled}
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
        <style>
            p.payment_module a.paylater-checkout {
                background: url(/modules/paylater/views/img/logo-64x64.png) no-repeat;
                background-position: 15px;
                background-size: 64px 64px;
            }
            p.payment_module a.paylater-checkout.ps_version_1-6 {
                background-color: #fbfbfb;
            }
            p.payment_module a.paylater-checkout.ps_version_1-5 {
                height: 60px;
                padding-left: 99px;
                margin-top: -10px;
                line-height: 45px;
            }
            p.payment_module a:hover {
                background-color: #f6f6f6;
            }
        </style>
    </div>
</div>
{/if}