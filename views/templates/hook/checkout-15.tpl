{*
 * This file is part of the official Paga+Tarde module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2015-2016 Paga+Tarde
 * @license   proprietary
 *}
<div class="row">
    <div class="col-xs-12">
        <p class="payment_module">
            <a class="paylater-checkout ps_version_{$ps_version}" href="{$paymentUrl|escape:'html'}" title="{$pmtTitle|escape:'quotes'}">
                {$pmtTitle|escape:'quotes'}
            </a>
            {if $pmtSimulatorIsEnabled}
                <span class="js-pmt-payment-type"></span>
                <div class="PmtSimulator"
                     data-pmt-num-quota="{$pmtQuotesStart|escape:'quotes'}"
                     data-pmt-max-ins="{$pmtQuotesMax|escape:'quotes'}"
                     data-pmt-style="blue"
                     data-pmt-type="{$pmtSimulatorCheckout|escape:'quotes'}"
                     data-pmt-discount="no"
                     data-pmt-amount="{$amount|escape:'quotes'}"
                     data-pmt-expanded="yes">
                </div>
                <script type="text/javascript">
                    if (typeof pmtClient !== 'undefined') {
                        pmtClient.simulator.init();
                    }
                </script>
            {/if}
        </p>
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
    <script type="text/javascript" src="https://cdn.pagamastarde.com/pmt-js-client-sdk/3/js/client-sdk.min.js"></script>
    <script type="text/javascript">
        if (typeof pmtClient !== 'undefined') {
            pmtClient.setPublicKey('{$pmtPublicKey|escape:'quotes'}');
            pmtClient.events.send('checkout', { basketAmount: {$amount|escape:'quotes'} } );
        }
    </script>
</div>
