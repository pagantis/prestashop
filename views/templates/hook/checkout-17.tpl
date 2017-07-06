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
