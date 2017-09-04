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
<div class="PmtSimulator PmtSimulatorSelectable--claim"
     data-pmt-num-quota="4" data-pmt-max-ins="12" data-pmt-style="blue"
     data-pmt-type="{if $simulatorType != 1}{$simulatorType|escape:'quotes'}{else}2{/if}"
     data-pmt-discount="{$discount|escape:'quotes'}" data-pmt-amount="{$amount|escape:'quotes'}" data-pmt-expanded="{if $simulatorType == 1}no{else}yes{/if}">
</div>
<script type="text/javascript">
    if (typeof pmtClient !== 'undefined') {
        pmtClient.simulator.init();
    }
</script>
