{*
 * This file is part of the official Paga+Tarde module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2015-2016 Paga+Tarde
 * @license   proprietary
 *}
<style>
    .pmt-promotion {
        text-align: center;
        font-weight: bold;
        color: black;
        font-size: medium;
    }
</style>
<script type="text/javascript" src="https://cdn.pagamastarde.com/pmt-js-client-sdk/3/js/client-sdk.min.js"></script>
<script type="text/javascript">
    if (typeof pmtClient !== 'undefined') {
        pmtClient.setPublicKey('{$publicKey|escape:'quotes'}');
    }

</script>
<span class="js-pmt-payment-type"></span>
{if $isPromotionProduct == true}
    <span class="pmt-promotion" id="pmt-promotion-extra">{$promotionExtra|escape:'quotes'}</span>
{/if}

<div class="PmtSimulator PmtSimulatorSelectable--claim"
     data-pmt-num-quota="4"
     data-pmt-max-ins="12"
     data-pmt-style="blue"
     data-pmt-type="{if $simulatorType != 1}{$simulatorType|escape:'quotes'}{else}2{/if}"
     data-pmt-discount="{$discount|escape:'quotes'}"
     data-pmt-amount="{$amount|escape:'quotes'}"
     data-pmt-expanded="{if $simulatorType == 1}no{else}yes{/if}">
</div>
<script type="text/javascript">
    function changePrice(miliseconds=1000)
    {
        setTimeout(
            function() {
                var newPrice = document.getElementById("our_price_display").innerText;
                var currentPrice = document.getElementsByClassName('PmtSimulator')[0].getAttribute('data-pmt-amount');

                if (newPrice != currentPrice) {
                    document.getElementsByClassName('PmtSimulator')[0].setAttribute('data-pmt-amount', newPrice);
                    if (typeof pmtClient !== 'undefined') {
                        pmtClient.simulator.reload();
                    }
                }
            }
        ,miliseconds)
    }
    changePrice(0); //Load the screen price into simulator to avoid the reload event
    window.onload = function() {
        var productAttributeModifiers = document.getElementById('attributes').querySelectorAll('input, select, a');
        //<select> for size, <a> for color/texture, <input>for checkbox
        productAttributeModifiers.forEach(function(modifier, index) {
            var eventType = (modifier.tagName == 'SELECT') ? 'change':'click';
            modifier.addEventListener(eventType, changePrice);
        });
    }

    if (typeof pmtClient !== 'undefined') {
        pmtClient.simulator.init();
    }
</script>
