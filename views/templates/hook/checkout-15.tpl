{*
 * This file is part of the official Paga+Tarde module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2015-2016 Paga+Tarde
 * @license   proprietary
 *}

<p class="payment_module paylater_payment_button" id="paylater_payment_button">
    <a href="javascript:$('#paylater_form').submit();" title="{l s='Paylater' mod='paylater'}">
        <img id="logo_pagamastarde" src="{$module_dir|escape:'quotes'}views/img/logo-86x49.png"
             alt="{l s='Paylater logo' mod='paylater'}" style="max-width: 80px"/>
        {l s='Finance using Paylater' mod='paylater'}
    </a>
</p>

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
