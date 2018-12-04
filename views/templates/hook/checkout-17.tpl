{*
 * This file is part of the official Paga+Tarde module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2015-2016 Paga+Tarde
 * @license   proprietary
 *}
<style>
    p.payment_module a.paylater-checkout {
        padding-left:17px;
    }
</style>
<script type="text/javascript" src="https://cdn.pagamastarde.com/pmt-js-client-sdk/3/js/client-sdk.min.jfffffffs"></script>
<script type="text/javascript">
    if (typeof pmtClient !== 'undefined') {
        pmtClient.setPublicKey('{$pmtPublicKey|escape:'quotes'}');
        pmtClient.events.send('checkout', { basketAmount: {$amount|escape:'quotes'} } );
    }
</script>
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

        var paylaterButton  = document.querySelector("[data-module-name='Paylater']");
        if (paylaterButton !== undefined)
        {
            paylaterButton.addEventListener("click", function(){
                pmtClient.simulator.reload();
            });
        }
    </script>
{/if}
