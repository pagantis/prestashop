{*
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integrations@pagantis.com>
 * @copyright 2019 Pagantis
 * @license   proprietary
 *}
{if $MAIN_IS_ENABLED}
    <div class="row">
        <div class="col-xs-12">
            <p class="payment_module Pagantis ps_version_{$MAIN_PS_VERSION|escape:'htmlall':'UTF-8'}">
                <a class="pagantis-checkout ps_version_{$MAIN_PS_VERSION|escape:'htmlall':'UTF-8'} locale_{$MAIN_LOCALE|escape:'htmlall':'UTF-8'}" href="{$MAIN_PAYMENT_URL|escape:'htmlall':'UTF-8'}" title="{$MAIN_TITLE|escape:'htmlall':'UTF-8'}">
                    {if $MAIN_PS_VERSION !== '1-7'}{$MAIN_TITLE|escape:'quotes'}&nbsp;{/if}
                    <span class="mainPagantisSimulator ps_version_{$MAIN_PS_VERSION|escape:'htmlall':'UTF-8'}">
                         {$MAIN_TITLE nofilter} <img class="mainImageLogo" src="{$MAIN_SIMULATOR_DISPLAY_IMAGE|escape:'htmlall':'UTF-8'}">
                    </span>

                </a>
            </p>
            <style>
                .mainPagantisSimulator {
                    display: inline-block;
                }
                .mainPagantisSimulator .mainImageLogo{
                    width: 20px;
                    height: 20px;
                }
                .mainPagantisSimulator.ps_version_1-5 {
                    padding-top: 0px;
                    margin-top: -15px;
                }
                .mainPagantisSimulator.ps_version_1-6 {
                    vertical-align: top;
                    margin-left: 20px;
                }
                .mainPagantisSimulator.ps_version_1-7 {
                    padding-top: 0px;
                }
                p.payment_module.Pagantis.ps_version_1-5 {
                    min-height: 0px;
                }
                p.payment_module.Pagantis.ps_version_1-7 {
                    margin-left: -5px;
                    margin-top: -15px;
                    margin-bottom: 0px;
                }
                p.payment_module a.pagantis-checkout {
                    background: url(https://cdn.digitalorigin.com/assets/master/logos/pg-favicon.png) 5px 5px no-repeat #fbfbfb;
                    background-size: 90px;
                }
                p.payment_module a.pagantis-checkout.ps_version_1-7 {
                    background: none;
                }
                .payment-option img[src*='cdn.digitalorigin.com'] {
                    height: 18px;
                    padding-left: 5px;
                    content:url('https://cdn.digitalorigin.com/assets/master/logos/pg.png');

                }
                p.payment_module a.pagantis-checkout.ps_version_1-6 {
                    background-color: #fbfbfb;
                }
                p.payment_module a.pagantis-checkout.ps_version_1-6:after {
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
                p.payment_module a.pagantis-checkout.ps_version_1-5 {
                    height: 90px;
                    padding-left: 99px;
                    padding-top: 45px;
                }
                p.payment_module a:hover {
                    background-color: #f6f6f6;
                }
                {$MAIN_SIMULATOR_CSS_CHECKOUT_PAGE_STYLES|escape:'javascript':'UTF-8'}
            </style>
        </div>
    </div>
{/if}