{*
 * This file is part of the official Paga+Tarde module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2015-2016 Paga+Tarde
 * @license   proprietary
 *}
<form id="paylater_form" action="{$paymentUrl|escape:'html'}"></form>
<div class="row">
    <div class="col-xs-12">
        {if version_compare($smarty.const._PS_VERSION_,'1.6.0.0','<')}
            <div class="payment_module" id="paylater_payment_button">
                <a href="javascript:$('#paylater_form').submit();" title="{$pmtTitle|escape:'quotes'}">
                    <img id="logo_paylater" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/logo-64x64.png"
                         alt="{$pmtTitle|escape:'quotes'}" style="max-width: 80px"/>
                    {$pmtTitle|escape:'quotes'}
                </a>
            </div>
        {else}
            <p class="payment_module paylater" id="payment_button">
                <a href="javascript:$('#paylater_form').submit();" title="{$pmtTitle|escape:'quotes'}">
                    <img id="logo_paylater" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/logo-64x64.png"
                         alt="{$pmtTitle|escape:'quotes'}" style="max-width: 80px"/>
                    {$pmtTitle|escape:'quotes'}
                </a>
            </p>
            <style>p.payment_module.paylater a
                {
                    padding-left:17px;
                }</style>
        {/if}
    </div>
</div>
