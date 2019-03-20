{*
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integration@pagantis.com>
 * @copyright 2015-2016 Pagantis
 * @license   proprietary
 *}
<script type="application/javascript">
    if (typeof pmtSDK !== 'undefined') {
        document.addEventListener("DOMContentLoaded", function(){
            pmtSDK.modal.open(
                "{$url|escape:'quotes'}",
                {
                    closeOnBackDropClick: false,
                    closeOnEscPress: false,
                    backDropDark: false,
                    largeSize: true,
                    closeConfirmationMessage: "{l s='Sure you want to leave?' mod='pagantis'}"
                }
            );
        });
        pmtSDK.modal.onClose(function() {
            window.location.href = "{$checkoutUrl|escape:'quotes'}";
        });
    }
</script>
