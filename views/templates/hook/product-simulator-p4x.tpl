{*
 * This file is part of the official Clearpay module for PrestaShop.
 *
 * @author    Clearpay <integrations@clearpay.com>
 * @copyright 2019 Clearpay
 * @license   proprietary
*}
{if ($P4X_IS_ENABLED && $P4X_SIMULATOR_IS_ENABLED)}
    <style>
        @import url('https://fonts.googleapis.com/css?family=Open+Sans:400');
        .ClearpaySimulator4x {
            font-family: Open Sans,sans-serif!important;
            font-size: 14px!important;
            font-weight: 400;
            text-align: left!important;
            color: #828282!important;
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -ms-user-select: none;
            user-select: none;
            padding: 5px 0 10px 0;
            min-width: 250px;
            display: block;
        }
        .ClearpaySimulator4x.ps-version-1-5 {
            display: inline-block;
        }
        .ClearpaySimulator4x .image4x{
            height: 18px;
        }
        {$P4X_SIMULATOR_CSS_PRODUCT_PAGE_STYLES|escape:'javascript':'UTF-8'}
    </style>
    <div class="ClearpaySimulator4x ps-version-{$P4X_PS_VERSION|escape:'htmlall':'UTF-8'}">
        {$P4X_SIMULATOR_TITLE nofilter} {$P4X_AMOUNT4X nofilter}€, {$P4X_SIMULATOR_SUBTITLE nofilter} <img class="image4x" src="{$P4X_SIMULATOR_DISPLAY_IMAGE|escape:'htmlall':'UTF-8'}">
    </div>
{/if}
