{*
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integration@pagantis.com>
 * @copyright 2015-2016 Pagantis
 * @license   proprietary
 *}
{if $IsEnabled}
<div class="row">
    <div class="col-xs-12">
        <p class="payment_module">
            <a class="pagantis-checkout ps_version_{$ps_version}" href="{$paymentUrl|escape:'html'}" title="{$pagantisTitle|escape:'quotes'}">
                {$pagantisTitle|escape:'quotes'}
            </a>
        </p>
        <style>
            p.payment_module a.pagantis-checkout {
                background: url(/modules/pagantis/views/img/logo-64x64.png) no-repeat;
                background-position: 15px;
                background-size: 64px 64px;
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