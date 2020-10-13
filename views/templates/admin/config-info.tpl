{*
 * This file is part of the official Clearpay module for PrestaShop.
 *
 * @author    Clearpay <integrations@clearpay.com>
 * @copyright 2020 Clearpay
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
        .clearpay-content-form {
            overflow-x: hidden;
            overflow-y: hidden;
            text-align: center;
            width: 97%;
        }

        .clearpay-content-form input{
            margin-left: 15px;
            margin-right: 5px;
        }

        .clearpay-content-form label{
            margin-left: 15px;
        }

        .clearpay-content-form img{
            margin-top: 20px;
            display: inline-block;
            vertical-align: middle;
            float: none;
            width: 150px;
        }
    </style>
    {$message|escape:'quotes'}
    <div class="panel clearpay-content-form">
        <h3><i class="icon icon-credit-card"></i> {l s='Clearpay Configuration Panel' mod='clearpay'}</h3>
        <div class="column-left">
            <a target="_blank" href="https://bo.clearpay.com" class="btn btn-default" title="Login Clearpay"><i class="icon-user"></i> {l s='Clearpay Backoffice Login' mod='clearpay'}</a>
        </div>
        <div class="column-center">
            <p>
                {l s='Clearpay configuration panel, please take your time to configure the payment method behavior' mod='clearpay'}
            </p>
            <p>
                {l s='If you need help or want to customize the module, please take a look to our documentation on' mod='clearpay'}
                <a href="https://github.com/clearpay/prestashop/tree/{$version|escape:'htmlall':'UTF-8'}">GitHub </a>
            </p>
        </div>
        <div class="column-right">
            <img src="{$logo|escape:'htmlall':'UTF-8'}"/>
        </div>
    </div>
    {$form|escape:'quotes':'UTF-8'}
    {if version_compare($smarty.const._PS_VERSION_,'1.6','<')}
        <script type="text/javascript">
            var d = document.getElementById("module_form");
            d.className += " panel";
        </script>
    {/if}
{/block}