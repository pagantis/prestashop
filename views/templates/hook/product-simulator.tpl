{*
 * This file is part of the official Clearpay module for PrestaShop.
 *
 * @author    Clearpay <integrations@clearpay.com>
 * @copyright 2020 Clearpay
 * @license   proprietary
*}
<style>

</style>
<!-- Afterpay.js  -->
<script
        src="{$SDK_URL|escape:'javascript':'UTF-8'}"
        data-min="{$DISPLAY_MIN_AMOUNT|escape:'javascript':'UTF-8'}"
        data-max="{$DISPLAY_MAX_AMOUNT|escape:'javascript':'UTF-8'}"
        async>
</script>
<!-- Afterpay.js -->
<div class="ClearpaySimulator ps-version-{$PS_VERSION|escape:'htmlall':'UTF-8'}">
    <afterpay-placement
            data-locale="{$ISO_COUNTRY_CODE|escape:'htmlall':'UTF-8'}"
            data-currency="{$CURRENCY|escape:'htmlall':'UTF-8'}GBD"
            data-amount="{$AMOUNT|escape:'htmlall':'UTF-8'">
    </afterpay-placement>
</div>
