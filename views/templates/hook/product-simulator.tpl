{*
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integrations@pagantis.com>
 * @copyright 2019 Pagantis
 * @license   proprietary
*}
{if ($MAIN_IS_ENABLED && $MAIN_SIMULATOR_IS_ENABLED)}
    <style>
        .mainPagantisSimulator .mainImageLogo{
            width: 20px;
            height: 20px;
        }
    </style>
    <div class="mainPagantisSimulator">
        {$MAIN_TITLE nofilter} <img class="mainImageLogo" src="{$MAIN_SIMULATOR_DISPLAY_IMAGE|escape:'htmlall':'UTF-8'}">
    </div>
{/if}
