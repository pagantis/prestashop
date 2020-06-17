{*
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integrations@pagantis.com>
 * @copyright 2019 Pagantis
 * @license   proprietary
 *}
<form id="pagantis_form3x" action="{$paymentUrl3x|escape:'htmlall':'UTF-8'}"></form>
{if version_compare($smarty.const._PS_VERSION_,'1.6.0.0','<')}
    <div class="payment_module Pagantis3x" id="pagantis_payment_button">
        <a href="javascript:$('#pagantis_form3x').submit();" title="Compra ahora, paga más tarde">
            Compra ahora, paga más tarde
        </a>
    </div>
{else}
    <p class="payment_module pagantis Pagantis3x" id="pagantis_payment_button">
        <a href="javascript:$('#pagantis_form3x').submit();" title="Compra ahora, paga más tarde">
            Compra ahora, paga más tarde
        </a>
    </p>
{/if}
<style>
    .pgSimulatorPlaceholder {
        display: inline-block;
    }
    {$pagantisSimulatorStyles|escape:'javascript':'UTF-8'}
</style>