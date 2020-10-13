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
        data-min="{$CLEARPAY_MIN_AMOUNT|escape:'javascript':'UTF-8'}"
        data-max="{$CLEARPAY_MAX_AMOUNT|escape:'javascript':'UTF-8'}"
        async>
</script>
<!-- Afterpay.js -->
<div class="ClearpaySimulator ps-version-{$PS_VERSION|escape:'htmlall':'UTF-8'}">
    <!-- <afterpay-placement
            data-locale="{$ISO_COUNTRY_CODE|escape:'htmlall':'UTF-8'}"
            data-currency="{$CURRENCY|escape:'htmlall':'UTF-8'}"
            data-amount="{$AMOUNT|escape:'htmlall':'UTF-8'}">
    </afterpay-placement> -->
    <afterpay-placement
            data-locale="en_GB"
            data-currency="GDB"
            data-amount="{$AMOUNT|escape:'htmlall':'UTF-8'}">
    </afterpay-placement>
</div>
