<table class="std">
    <thead>
    <tr>
        <th>{l s='Product' mod='psconfresume'}</th>
        <th>{l s='Price' mod='psconfresume'}</th>
        <th>{l s='Qty' mod='psconfresume'}</th>
    </tr>
    </thead>
    <tbody>
    {foreach from=$order_products item=product}
        <tr>
            <td>{$product.product_name}</td>
            <td>
                {if $use_taxes}
                    {displayPrice price=$product.total_price_tax_incl}
                {else}
                    {displayPrice price=$product.total_price_tax_excl}
                {/if}
            </td>
            <td>{$product.product_quantity}</td>
        </tr>
    {/foreach}
    </tbody>
    <tfoot>
    <tr>
        <td style="text-align:right">
            {l s='Products Total' mod='psconfresume'}
        </td>
        <td colspan="2">
            {if $use_taxes}
                {displayPrice price=$order->total_products_wt}
            {else}
                {displayPrice price=$order->total_products}
            {/if}

        </td>
    </tr>
    <tr>
        <td style="text-align:right">
            {l s='Shipping' mod='psconfresume'}
        </td>
        <td colspan="2">
            {if $use_taxes}
                {displayPrice price=$order->total_shipping_tax_incl}
            {else}
                {displayPrice price=$order->total_shipping_tax_excl}
            {/if}

        </td>
    </tr>
    {if $order->total_discounts != '0.00'}
        <tr>
            <td style="text-align:right">
                {l s='Discounts' mod='psconfresume'}
            </td>
            <td colspan="2">-
                {if $use_taxes}
                    {displayPrice price=$order->total_discounts_tax_incl}
                {else}
                    {displayPrice price=$order->total_discounts_tax_excl}
                {/if}
            </td>
        </tr>
    {/if}
    {if $use_taxes}
        <tr>
            <td style="text-align:right">
                {l s='Taxes Paid' mod='psconfresume'}
            </td>
            <td colspan="2">
                {$taxamt = $order->total_paid_tax_incl - $order->total_paid_tax_excl}
                {displayPrice price=$taxamt}<p></p>
            </td>
        </tr>
    {/if}
    <tr>
        <td style="text-align:right">
            {l s='TOTAL' mod='psconfresume'}
        </td>
        <td colspan="2">
            {if $use_taxes}
                {displayPrice price=$order->total_paid_tax_incl}
            {else}
                {displayPrice price=$order->total_paid_tax_excl}
            {/if}
        </td>
    </tr>
    </tfoot>
</table>
