{*
 * This file is part of the official Paga+Tarde module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2015-2016 Paga+Tarde
 * @license   proprietary
 *}
<link rel="stylesheet" type="tbankwireext/css" media="all" href="{$css|escape:'quotes'}">
<div class="row">
    <div class="col-xs-12">
        <p class="payment_module">
            <a class="paylater-checkout" href="{$paymentUrl|escape:'html'}" title="{l s='Finance using Paylater' mod='paylater'}">
                <img width="64px" height="64px" id="logo_pagamastarde" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/logo-64x64.png">
                {l s='Finance using Paylater' mod='paylater'}
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
            </a>
        </p>
    </div>
</div>
