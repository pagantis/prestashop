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
            <a class="bankwire paylater_checkout" href="{$payment|escape:'html'}" title="{l s='Finance using Paylater' mod='paylater'}">
                {l s='Finance using Paylater' mod='paylater'}
                <span>
                <script type="text/javascript" src="https://cdn.pagamastarde.com/pmt-js-client-sdk/3/js/client-sdk.min.js"></script>
                <script type="text/javascript">
                    if (typeof pmtClient !== 'undefined') {
                        pmtClient.setPublicKey('{$publicKey|escape:'quotes'}');
                        pmtClient.events.send('checkout', { basketAmount: {$amount|escape:'quotes'} } );
                    }
                </script>
                {if $includeSimulator}
                    <span class="js-pmt-payment-type"></span>
                    <div class="PmtSimulator"
                         data-pmt-num-quota="4" data-pmt-max-ins="12" data-pmt-style="blue"
                         data-pmt-type="{$simulatorType|escape:'quotes'}"
                         data-pmt-discount="{$discount|escape:'quotes'}" data-pmt-amount="{$amount|escape:'quotes'}" data-pmt-expanded="yes">
                    </div>
                    <script type="text/javascript">
                        if (typeof pmtClient !== 'undefined') {
                            pmtClient.simulator.init();
                        }
                    </script>
                {/if}
                </span>
            </a>
        </p>
    </div>
</div>
