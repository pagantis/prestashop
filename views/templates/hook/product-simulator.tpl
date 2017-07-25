{*
 * This file is part of the official Paga+Tarde module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2015-2016 Paga+Tarde
 * @license   proprietary
 *}

<script type="text/javascript" src="https://cdn.pagamastarde.com/pmt-js-client-sdk/3/js/client-sdk.min.js"></script>
<script type="text/javascript">
    if (typeof pmtClient !== 'undefined') {
        pmtClient.setPublicKey('{$publicKey|escape:'quotes'}');
    }
</script>
<span class="js-pmt-payment-type"></span>
<div class="PmtSimulator"
     data-pmt-num-quota="4" data-pmt-max-ins="12" data-pmt-style="blue"
     data-pmt-type="{$simulatorType|escape:'quotes'}"
     data-pmt-amount="{$amount|escape:'quotes'}" data-pmt-expanded="yes">
</div>
<script type="text/javascript">
    if (typeof pmtClient !== 'undefined') {
        pmtClient.simulator.init();
    }
</script>
