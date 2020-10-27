{*
 * This file is part of the official Clearpay module for PrestaShop.
 *
 * @author    Clearpay <integrations@clearpay.com>
 * @copyright 2020 Clearpay
 * @license   proprietary
 *}
<form id="clearpay_form" action="{$PAYMENT_URL|escape:'htmlall':'UTF-8'}">
</form>
<p class="payment_module clearpay clearpay" id="clearpay_payment_button">
    <a href="javascript:$('#clearpay_form').submit();" title="{$TITLE|escape:'htmlall':'UTF-8'}">
        {$TITLE|escape:'htmlall':'UTF-8'}
    </a>
</p>
