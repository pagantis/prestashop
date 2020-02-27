{*
 * This file is part of the official enCuotas module for PrestaShop.
 *
 * @author    Pagantis <integrations@pagantis.com>
 * @copyright 2019 Pagantis
 * @license   proprietary
 *}

{extends file='page.tpl'}
{block name="page_content"}
    <script type="application/javascript">
        if (typeof encuotasSDK !== 'undefined') {
            document.addEventListener("DOMContentLoaded", function(){
                encuotasSDK.modal.open(
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
            encuotasSDK.modal.onClose(function() {
                window.location.href = "{$checkoutUrl|escape:'quotes'}";
            });
        }
    </script>
{/block}