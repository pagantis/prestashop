{*
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integrations@pagantis.com>
 * @copyright 2019 Pagantis
 * @license   proprietary
 *}
{if $pagantisIsEnabled}
    <div class="row">
        <div class="col-xs-12">
            <p class="payment_module Pagantis3x ps_version_{$ps_version|escape:'htmlall':'UTF-8'}">
                <a class="pagantis-checkout3x ps_version_{$ps_version|escape:'htmlall':'UTF-8'} locale_{$locale|escape:'htmlall':'UTF-8'}" href="{$paymentUrl|escape:'htmlall':'UTF-8'}&product=3x" title="Compra ahora, paga más tarde">
                    {if $ps_version !== '1-7'}3x Product&nbsp;{/if}
                    <span class="pagantisSimulator3x 3x ps_version_{$ps_version|escape:'htmlall':'UTF-8'}"></span>
                </a>
            </p>
            <style>
                .pagantisSimulator3x {
                    display: inline-block;
                }
                .pagantisSimulator3x.ps_version_1-5 {
                    padding-top: 0px;
                    margin-top: -15px;
                }
                .pagantisSimulator3x.ps_version_1-6 {
                    vertical-align: top;
                    margin-left: 20px;
                    margin-top: -5px;
                }
                .pagantisSimulator3x.ps_version_1-7 {
                    padding-top: 0px;
                }
                p.payment_module.Pagantis3x.ps_version_1-5 {
                    min-height: 0px;
                }
                p.payment_module.Pagantis3x.ps_version_1-7 {
                    margin-left: -5px;
                    margin-top: -15px;
                    margin-bottom: 0px;
                }
                p.payment_module a.pagantis-checkout3x {
                    background: url(https://cdn.digitalorigin.com/assets/master/logos/pg-favicon.png) 5px 5px no-repeat #fbfbfb;
                    background-size: 80px;
                }
                p.payment_module a.pagantis-checkout3x.ps_version_1-7 {
                    background: none;
                }
                .payment-option img[src*='digitalorigin'] {
                    height: 24px;
                    padding-left: 5px;
                }
                p.payment_module a.pagantis-checkout3x.ps_version_1-6 {
                    background-color: #fbfbfb;
                }
                p.payment_module a.pagantis-checkout3x.ps_version_1-6:after {
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
                p.payment_module a.pagantis-checkout3x.ps_version_1-5 {
                    height: 90px;
                    padding-left: 99px;
                    padding-top: 45px;
                }
                p.payment_module a:hover {
                    background-color: #f6f6f6;
                }
                {$pagantisSimulatorStyles|escape:'javascript':'UTF-8'}
            </style>
        </div>
    </div>
{/if}