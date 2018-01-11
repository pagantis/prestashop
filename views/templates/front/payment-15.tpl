{*
 * This file is part of the official Paga+Tarde module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2015-2016 Paga+Tarde
 * @license   proprietary
 *}

<link rel="stylesheet" type="text/css" media="all" href="{$prestashopCss|escape:'quotes'}">
<link rel="stylesheet" type="text/css" media="all" href="{$css|escape:'quotes'}">
<div class="paylater-content">
    <div id="myModal" class="paylater_modal" style="display: block;">
        <div class="paylater_modal-content">
            <iframe id="iframe-pagantis" name="iframe-pagantis" style="width: 100%; height: 100%; display: block" frameborder="0" src="{$url|escape:'quotes'}"></iframe>
            <button class="paylater_modal-close" id="paylater_close" title="Cerrar" type="button">X</button>
        </div>
    </div>

    <script type="text/javascript">
        var closeModal = function closeModal(evt) {
            evt.preventDefault();
            window.location.href = "{$checkoutUrl|escape:'quotes'}";
        };
        var elements = document.querySelectorAll('#paylater_close, #myModal');
        Array.prototype.forEach.call(elements, function(el){
            el.addEventListener('click', closeModal);
        });
    </script>
</div>
