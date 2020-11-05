{*
 * This file is part of the official Clearpay module for PrestaShop.
 *
 * @author    Clearpay <integrations@clearpay.com>
 * @copyright 2020 Clearpay
 * @license   proprietary
 *}
<style>
    p.payment_module.Clearpay.ps_version_1-7 {
        margin-left: -5px;
        margin-top: -15px;
        margin-bottom: 0px;
    }
    p.payment_module a.clearpay-checkout {
        background: url('{$ICON|escape:'htmlall':'UTF-8'}') 5px 5px no-repeat #fbfbfb;
        background-size: 79px;
    }
    p.payment_module a.clearpay-checkout.ps_version_1-7 {
        background: none;
    }
    .payment-option img[src*='clearpay'] {
        height: 25px;
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
    p.payment_module a:hover {
        background-color: #f6f6f6;
    }

    #clearpay-method-content {
        color: #7a7a7a;
        border: 1px solid #000;
        margin-bottom: 10px;
    }
    .clearpay-header {
        color: #7a7a7a;
        position: relative;
        line-height: 40px;
        text-align: right;
        padding-right: 20px;
        background-color: #b2fce4;
    }
    .clearpay-header img {
        height: 40px;
        position: absolute;
        top: 0px;
        left: 5px;
    }
    .clearpay-checkout-ps1-6-logo {
        height: 45px;
        margin-left: 10px;
        top: 25%;
        position: absolute;
    }
    .clearpay-more-info-text {
        padding: 1em 3em;
        text-align: center;
    }
    .clearpay-terms {
        margin-top: 10px;
        display: inline-block;
    }
    @media only screen and (max-width: 1200px) {
        .clearpay-header {
            text-align: center;
            padding-top: 30px;
            display: block;
            height: 65px !important;
        }
    }
    @media only screen and (max-width: 1200px) and (min-width: 990px)  {
        .clearpay-header img {
            left:35%;
        }
    }
    @media only screen and (max-width: 989px) and (min-width: 768px)  {
        .clearpay-header img {
            left:30%;
        }
        .clearpay-header {
            height: 100px !important;
        }
    }
    @media only screen and (max-width: 767px) and (min-width: 575px)  {
        .clearpay-header img {
            left:35%;
        }
        .clearpay-header {
            height: 65px !important;
        }
    }
    @media only screen and (max-width: 575px) {
        .clearpay-header img {
            left:28%;
        }
        .clearpay-header {
            height: 100px !important;
        }
    }
</style>
{if $PS_VERSION !== '1-7'}
    <div class="row">
        <div class="col-xs-12">
            <p class="payment_module">
                <a class="clearpay-checkout clearpay-checkout ps_version_{$PS_VERSION|escape:'htmlall':'UTF-8'}" href="{$PAYMENT_URL|escape:'htmlall':'UTF-8'}">
                    {$TITLE|escape:'htmlall':'UTF-8'}
                    <img class="clearpay-checkout-ps{$PS_VERSION|escape:'htmlall':'UTF-8'}-logo" src="{$LOGO|escape:'htmlall':'UTF-8'}">
                </a>
            </p>
        </div>
    </div>
{/if}
{if $PS_VERSION === '1-7'}
<section>
    <div class="payment-method ps_version_{$PS_VERSION|escape:'htmlall':'UTF-8'}" id="clearpay-method" >
        <div class="payment-method-content clearpay ps_version_{$PS_VERSION|escape:'htmlall':'UTF-8'}" id="clearpay-method-content">
            <div class="clearpay-header">
                <img src="{$LOGO|escape:'htmlall':'UTF-8'}"> {$MOREINFO_HEADER|escape:'htmlall':'UTF-8'}
            </div>
            <div class="clearpay-more-info-text">
                <div class="clearpay-more-info">
                    {$MOREINFO_ONE|escape:'htmlall':'UTF-8'}
                </div>
                <afterpay-price-table
                        data-type="price-table"
                        data-amount="{$TOTAL_AMOUNT|escape:'htmlall':'UTF-8'}"
                        data-price-table-theme="white"
                        data-locale="{$ISO_COUNTRY_CODE|escape:'htmlall':'UTF-8'}"
                        data-currency="{$CURRENCY|escape:'htmlall':'UTF-8'}">
                </afterpay-price-table>
                <a class="clearpay-terms" href="{$TERMS_AND_CONDITIONS_LINK|escape:'htmlall':'UTF-8'}" TARGET="_blank">
                    {$TERMS_AND_CONDITIONS|escape:'htmlall':'UTF-8'}
                </a>
            </div>
        </div>
    </div>
</section>
{/if}