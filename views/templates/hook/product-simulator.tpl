{*
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integrations@pagantis.com>
 * @copyright 2019 Pagantis
 * @license   proprietary
*}
{if ($MAIN_IS_ENABLED && $MAIN_SIMULATOR_IS_ENABLED)}
    <style>
        @import url('https://fonts.googleapis.com/css?family=Open+Sans:400');
        .mainPagantisSimulator {
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
        }
        .mainPagantisSimulator .mainImageLogo{
            height: 18px;
        }
    </style>
    <div class="mainPagantisSimulator">
        {$MAIN_SIMULATOR_TITLE nofilter} {$MAIN_AMOUNT4X nofilter}€, {$MAIN_SIMULATOR_SUBTITLE nofilter} <img class="mainImageLogo" src="{$MAIN_SIMULATOR_DISPLAY_IMAGE|escape:'htmlall':'UTF-8'}">
    </div>
{/if}
