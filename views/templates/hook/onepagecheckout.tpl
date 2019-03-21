{*
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integration@pagantis.com>
 * @copyright 2015-2016 Pagantis
 * @license   proprietary
 *}
<form id="pagantis_form" action="{$paymentUrl|escape:'html'}"></form>
<div class="row">
    <div class="col-xs-12">
        {if version_compare($smarty.const._PS_VERSION_,'1.6.0.0','<')}
            <div class="payment_module" id="pagantis_payment_button">
                <a href="javascript:$('#pagantis_form').submit();" title="{$pagantisTitle|escape:'quotes'}">
                    <img id="logo_pagantis" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/logo-64x64.png"
                         alt="{$pagantisTitle|escape:'quotes'}" style="max-width: 80px"/>
                    {$pagantisTitle|escape:'quotes'}
                </a>
            </div>
        {else}
            <p class="payment_module pagantis" id="payment_button">
                <a href="javascript:$('#pagantis_form').submit();" title="{$pagantisTitle|escape:'quotes'}">
                    <img id="logo_pagantis" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/logo-64x64.png"
                         alt="{$pagantisTitle|escape:'quotes'}" style="max-width: 80px"/>
                    {$pagantisTitle|escape:'quotes'}
                </a>
            </p>
            <style>p.payment_module.pagantis a
                {
                    padding-left:17px;
                }</style>
        {/if}
    </div>
</div>
