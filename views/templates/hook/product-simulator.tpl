{*
 * This file is part of the official Paga+Tarde module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2015-2016 Paga+Tarde
 * @license   proprietary
 *}
{if ($pmtIsEnabled && $pmtSimulatorIsEnabled)}
<script type="text/javascript" src="https://cdn.pagamastarde.com/pmt-js-client-sdk/3/js/client-sdk.min.js"></script>
<script type="text/javascript">
    if (typeof pmtClient !== 'undefined') {
        pmtClient.setPublicKey('{$pmtPublicKey|escape:'quotes'}');
    }

</script>
<span class="js-pmt-payment-type"></span>
<div class="PmtSimulator PmtSimulatorSelectable--claim"
     data-pmt-num-quota="{$pmtQuotesStart|escape:'quotes'}"
     data-pmt-max-ins="{$pmtQuotesMax|escape:'quotes'}"
     data-pmt-style="Blue"
     data-pmt-type="{$pmtSimulatorProduct|escape:'quotes'}"
     data-pmt-discount="no"
     data-pmt-amount="{$amount|escape:'quotes'}"
     data-pmt-expanded="yes">
</div>
<script type="text/javascript">
    function findPrice()    {
        var price = '';
        var priceDOM = document.getElementById("our_price_display");
        if (priceDOM != null) {
            return price.innerText;
        } else {
            priceDOM = document.querySelector(".current-price span")
            if (priceDOM != null) {
                return priceDOM.innerText;
            }
        }

        var all = document.getElementsByTagName("*");
        // Extra search
        var attribute = "itemprop";
        var value = "price";
        for (var i = 0; i < all.length; i++) {
            if (all[i].getAttribute(attribute) == value) {
                return all[i].innerText;
            }
        }
        return false;
    }
    function changePrice(miliseconds=1000)
    {
        setTimeout(
            function() {
                var newPrice = findPrice();
                if (newPrice) {
                    var currentPrice = document.getElementsByClassName('PmtSimulator')[0].getAttribute('data-pmt-amount');

                    if (newPrice != currentPrice) {
                        document.getElementsByClassName('PmtSimulator')[0].setAttribute('data-pmt-amount', newPrice);
                        if (typeof pmtClient !== 'undefined') {
                            pmtClient.simulator.reload();
                        }
                    }
                }
            }
        ,miliseconds)
    }
    changePrice(0); //Load the screen price into simulator to avoid the reload event
    window.onload = function() {
        var productAttributeModifiers = {};
        var productAttributeModifiersDOM = document.getElementById('attributes');
        //<select> for size, <a> for color/texture, <input>for checkbox
        if (productAttributeModifiersDOM != null) {
            productAttributeModifiers = productAttributeModifiersDOM.querySelectorAll('input, select, a');
        } else {
            productAttributeModifiers = document.getElementsByClassName('product-variants')[0].querySelectorAll('input, select, a');
        }
        productAttributeModifiers.forEach(function(modifier, index) {
            var eventType = (modifier.tagName == 'SELECT') ? 'change':'click';
            modifier.addEventListener(eventType, changePrice);
        });
    }

    setInterval(function(){ changePrice(0); }, 2000);

    if (typeof pmtClient !== 'undefined') {
        pmtClient.simulator.init();
    }
</script>
{/if}
