{*
 * This file is part of the official Clearpay module for PrestaShop.
 *
 * @author    Clearpay <integrations@clearpay.com>
 * @copyright 2020 Clearpay
 * @license   proprietary
 *}
<style>
    p.payment_module.Clearpay.ps_version_1-5 {
        min-height: 0px;
    }
    p.payment_module.Clearpay.ps_version_1-7 {
        margin-left: -5px;
        margin-top: -15px;
        margin-bottom: 0px;
    }
    p.payment_module a.clearpay-checkout {
        background: url('{$ICON|escape:'htmlall':'UTF-8'}') 5px 5px no-repeat #fbfbfb;
        background-size: 80px;
    }
    p.payment_module a.clearpay-checkout.ps_version_1-7 {
        background: none;
    }
    .payment-option img[src*='clearpay'] {
        height: 30px;
        padding-left: 5px;
        content:url('{$LOGO|escape:'htmlall':'UTF-8'}');
    }
    p.payment_module a.clearpay-checkout.ps_version_1-6 {
        background-color: #fbfbfb;
        max-height: 90px;
    }
    p.payment_module a.clearpay-checkout.ps_version_1-6:after {
        display: block;
        content: "\f054";
        position: absolute;
        right: 15px;
        margin-top: -11px;
        top: 50%;
        font-family: "FontAwesome";
        font-size: 25px;
        height: 22px;
        width: 14px;
        color: #777;
    }
    p.payment_module a.clearpay-checkout.ps_version_1-5 {
        height: 90px;
        padding-left: 99px;
    }
    p.payment_module a:hover {
        background-color: #f6f6f6;
    }
    p.payment_module.Clearpay.ps_version_1-5 {
        min-height: 0px;
        display: inline;
    }
</style>
<div class="row">
    <div class="col-xs-12">
        <p class="payment_module Clearpay ps_version_{$PS_VERSION|escape:'htmlall':'UTF-8'}">
            <a class="clearpay-checkout clearpay-checkout ps_version_{$PS_VERSION|escape:'htmlall':'UTF-8'}" href="{$PAYMENT_URL|escape:'htmlall':'UTF-8'}">
                {if $PS_VERSION !== '1-7'}{$TITLE|escape:'quotes'}&nbsp;{/if}
            </a>
        </p>
    </div>
</div>
