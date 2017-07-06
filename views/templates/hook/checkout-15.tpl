<p class="payment_module paylater_payment_button" id="paylater_payment_button">
    <a href="javascript:$('#paylater_form').submit();" title="{l s='Pay later' mod='paylater'}">
        <img id="logo_pagamastarde" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/logo-86x49.png"
             alt="{l s='Logo Paga Mas Tarde' mod='paylater'}" style="max-width: 80px"/>
        {l s='Financing using Paga+Tarde' mod='paylater'}
    </a>
</p>

<script type="text/javascript" src="https://cdn.pagamastarde.com/pmt-js-client-sdk/3/js/client-sdk.min.js"></script>
<div class="PmtSimulator"
     data-pmt-num-quota="4" data-pmt-max-ins="12" data-pmt-style="blue"
     data-pmt-type="2"
     data-pmt-discount="{$discount}" data-pmt-amount="{$amount}" data-pmt-expanded="no">
</div>
<script type="text/javascript">
    (function(){
        pmtClient.setPublicKey('{$publicKey}');
        pmtClient.simulator.init();
    })();
</script>
