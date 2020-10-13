{*
 * This file is part of the official Clearpay module for PrestaShop.
 *
 * @author    Clearpay <integrations@clearpay.com>
 * @copyright 2020 Clearpay
 * @license   proprietary
 *}
<script type="application/javascript">
    if (typeof pgSDK !== 'undefined') {
        document.addEventListener("DOMContentLoaded", function(){
            pgSDK.modal.open(
                "{$url|escape:'javascript':'UTF-8'}",
                {
                    closeOnBackDropClick: false,
                    closeOnEscPress: false,
                    backDropDark: false,
                    largeSize: true,
                    closeConfirmationMessage: "{l s='Sure you want to leave?' mod='clearpay'}"
                }
            );
        });
        pgSDK.modal.onClose(function() {
            window.location.href = "{$checkoutUrl|escape:'javascript':'UTF-8'}";
        });
    }
</script>