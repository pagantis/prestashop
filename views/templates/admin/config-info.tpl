{*
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integrations@pagantis.com>
 * @copyright 2015-2016 Pagantis
 * @license   proprietary
 *}

{block name="form"}
    <style>
        .column-left {
            text-align: left;
            float: left;
            width: 33%;
        }

        .column-right {
            text-align: right;
            float: right;
            width: 33%;
        }

        .column-center {
            text-align: center;
            display: inline-block;
            width: 33%;
        }
        .pagantis-content-form {
            overflow-x: hidden;
            overflow-y: hidden;
            text-align: center;
            width: 97%;
        }

        .pagantis-content-form input{
            margin-left: 15px;
            margin-right: 5px;
        }

        .pagantis-content-form label{
            margin-left: 15px;
        }

        .pagantis-content-form img{
            margin-top: 20px;
            display: inline-block;
            vertical-align: middle;
            float: none;
            width: 100px;
        }
    </style>
    {$message|escape:'quotes'}
    <div class="panel pagantis-content-form">
        <h3><i class="icon icon-credit-card"></i> {l s='Pagantis Configuration Panel' mod='pagantis'}</h3>
        <div class="column-left">
                <a target="_blank" href="https://bo.pagantis.com" class="btn btn-default" title="Login Pagantis"><i class="icon-user"></i> {l s='Pagantis Backoffice Login' mod='pagantis'}</a>
            </div>
            <div class="column-center">
                <p>
                    {l s='Pagantis configuration panel, please take your time to configure the payment method behavior' mod='pagantis'}
                </p>
                <p>
                    {l s='If you need help or want to customize the module, please take a look to our documentation on' mod='pagantis'}
                    <a href="https://github.com/Pagantis/prestashop/tree/{$version|escape:'quotes'}">GitHub </a>
                </p>
            </div>
            <div class="column-right">
                <img src="{$logo|escape:'quotes'}"/>
            </div>
    </div>
    {$form}
    {if version_compare($smarty.const._PS_VERSION_,'1.6','<')}
        <script type="text/javascript">
            var d = document.getElementById("module_form");
            d.className += " panel";
        </script>
    {/if}
{/block}
