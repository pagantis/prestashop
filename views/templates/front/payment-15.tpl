{*
 * This file is part of the official Paga+Tarde module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2015-2016 Paga+Tarde
 * @license   proprietary
 *}
<script type="text/javascript" src="https://cdn.pagamastarde.com/pmt-js-client-sdk/3/js/client-sdk.min.js"></script>
<script type="application/javascript">
    if (typeof pmtClient !== 'undefined') {
        document.addEventListener("DOMContentLoaded", function(){
            pmtClient.modal.open(
                "{$url|escape:'quotes'}",
                {
                    closeOnBackDropClick: false,
                    closeOnEscPress: false,
                    backDropDark: false,
                    largeSize: true,
                    closeConfirmationMessage: "{l s='Sure you want to leave?' mod='paylater'}"
                }
            );
        });
        pmtClient.modal.onClose(function() {
            window.location.href = "{$checkoutUrl|escape:'quotes'}";
        });
    }
</script>
