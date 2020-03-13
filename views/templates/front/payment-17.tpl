{*
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integrations@pagantis.com>
 * @copyright 2019 Pagantis
 * @license   proprietary
 *}

{extends file='page.tpl'}
{block name="page_content"}
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
                        closeConfirmationMessage: "{l s='Sure you want to leave?' mod='pagantis'}"
                    }
                );
            });
            pgSDK.modal.onClose(function() {
                window.location.href = "{$checkoutUrl|escape:'javascript':'UTF-8'}";
            });
        }
    </script>
{/block}