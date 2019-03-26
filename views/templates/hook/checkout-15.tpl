{*
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integrations@pagantis.com>
 * @copyright 2015-2016 Pagantis
 * @license   proprietary
 *}
{if $pagantisIsEnabled}
<div class="row">
    <div class="col-xs-12">
        <p class="payment_module">
            <a class="paylater-checkout ps_version_{$ps_version|escape:'quotes'}" href="{$paymentUrl|escape:'html'}" title="{$pmtTitle|escape:'quotes'}">
                {$pmtTitle|escape:'quotes'}
            </a>
        </p>
        <style>
            p.payment_module a.pagantis-checkout {
                background: url(/modules/pagantis/views/img/logo_pagantis.png) no-repeat;
                background-position: 4px;
                background-size: 95px;
            }
            p.payment_module a.pagantis-checkout.ps_version_1-6 {
                background-color: #fbfbfb;
            }
            p.payment_module a.pagantis-checkout.ps_version_1-5 {
                height: 60px;
                padding-left: 99px;
                margin-top: -10px;
                line-height: 45px;
            }
            p.payment_module a:hover {
                background-color: #f6f6f6;
            }
        </style>
    </div>
</div>
{/if}