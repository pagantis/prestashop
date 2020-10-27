{*
 * This file is part of the official Clearpay module for PrestaShop.
 *
 * @author    Clearpay <integrations@clearpay.com>
 * @copyright 2020 Clearpay
 * @license   proprietary
 *}
<style>
    .payment-method-note.clearpay-cart-note {
        padding: 20px 0 10px 0;
        text-align: left;
    }
    .payment-method-note.clearpay-cart-note.ps-1-6{
        float: right;
        width: 350px;
    }
    .clearpay-cart-note span {
        font-size: 0.85rem;
    }
    .clearpay-cart-note.ps-1-6 span {
        font-size: 1.20rem;
    }
    .clearpay-price-text {
        font-weight: bold;
    }
    .clearpay-more-info {
        text-align: right;
        font-size: 0.85rem;
    }
    .clearpay-cart-note.ps-1-6 .clearpay-more-info {
        font-size: 1.20rem;
    }

</style>
<div class="payment-method-note clearpay-cart-note ps-{$PS_VERSION|escape:'htmlall':'UTF-8'}" style="">
    <span class="clearpay-price-text">
        {$PRICE_TEXT|escape:'htmlall':'UTF-8'} {$AMOUNT_WITH_CURRENCY|escape:'htmlall':'UTF-8'}
    </span>
    <br><br>
    <span>{$DESCRIPTION_TEXT_ONE|escape:'htmlall':'UTF-8'}</span>
    <br><br>
    <span>{$DESCRIPTION_TEXT_TWO|escape:'htmlall':'UTF-8'}</span>
    <br/>
    <div class="clearpay-more-info">
		<br/> <a href="javascript:void(0)" onclick="Afterpay.launchModal('{$ISO_COUNTRY_CODE|escape:'javascript':'UTF-8'}');">
            {$MORE_INFO|escape:'htmlall':'UTF-8'}
        </a>
	</div>
</div>