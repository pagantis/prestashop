{*
 * This file is part of the official Clearpay module for PrestaShop.
 *
 * @author    Clearpay <integrations@clearpay.com>
 * @copyright 2020 Clearpay
 * @license   proprietary
 *}
<style>
    .clearpay-declined-header {
        color: #7a7a7a;
        position: relative;
        line-height: 35px;
        text-align: center;
        font-size: 18px;
        width: 95%;
    }
    .clearpay-more-info-text {
        font-family: "FontAwesome";
        font-size: 16px;
        color: #777;
        text-align: center;
    }
    .ps-version-1-6 {
        color: #b2fce4;
    }
    .ps-version-1-6 a{
        color: #b2fce4;
    }
</style>
<div class="clearpay-declined-header ps-version-{$PS_VERSION|escape:'htmlall':'UTF-8'}">
    {l s='PAYMENT DECLINED' mod='clearpay'}
</div>
<div class="clearpay-more-info-text ps-version-{$PS_VERSION|escape:'htmlall':'UTF-8'}">
    {l s='We are sorry to inform you that your payment has been declined by Clearpay.' mod='clearpay'}
    <br><br>
    {l s='For more information, please contact the Clearpay Customer Service Team:' mod='clearpay'}
    <br>
    <a href="{l s='https://developers.clearpay.co.uk/clearpay-online/docs/customer-support' mod='clearpay'}">
        {l s='https://developers.clearpay.co.uk/clearpay-online/docs/customer-support' mod='clearpay'}
    </a>
    {if $REFERENCE_ID != ''}
    <br><br>
    {l s='For reference, the Order ID for this transaction is:' mod='clearpay'}
    <strong>
        {$REFERENCE_ID|escape:'htmlall':'UTF-8'}
    </strong>
    {/if}
</div>
