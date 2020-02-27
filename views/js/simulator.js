/**
 * This file is part of the official enCuotas module for PrestaShop.
 *
 * @author    Pagantis <integrations@pagantis.com>
 * @copyright 2019 Pagantis
 * @license   proprietary
 */
function findPriceSelector()
{
    var priceDOM = document.getElementById("our_price_display");
    if (priceDOM != null) {
        return '#our_price_display';
    } else {
        priceDOM = document.querySelector(".current-price span[itemprop=price]")
        if (priceDOM != null) {
            return ".current-price span[itemprop=price]";
        }
    }

    return 'default';
}

function findQuantitySelector()
{
    var quantityDOM = document.getElementById("quantity_wanted");
    if (quantityDOM != null) {
        return '#quantity_wanted';
    }
    return 'default';
}