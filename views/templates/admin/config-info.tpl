{*
 * This file is part of the official Paga+Tarde module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2015-2016 Paga+Tarde
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
        .paylater-content-form {
            overflow-x: hidden;
            overflow-y: hidden;
            text-align: center;
            width: 97%;
        }

        .paylater-content-form input{
            margin-left: 15px;
            margin-right: 5px;
        }

        .paylater-content-form label{
            margin-left: 15px;
        }

        .paylater-content-form img{
            margin-top: 20px;
            display: inline-block;
            vertical-align: middle;
            float: none;
            width: 100px;
        }
    </style>
    {$message|escape:'quotes'}
    <div class="panel paylater-content-form">
        <h3><i class="icon icon-credit-card"></i> {l s='Paylater Configuration Panel' mod='paylater'}</h3>
        <div class="column-left">
                <a target="_blank" href="https://bo.pagamastarde.com" class="btn btn-default" title="Login Paga+Tarde"><i class="icon-user"></i> {l s='Paylater Backoffice Login' mod='paylater'}</a>
            </div>
            <div class="column-center">
                <p>
                    {l s='Paylater configuration panel, please take your time to configure the payment method behavior' mod='paylater'}
                </p>
                <p>
                    {l s='If you need help or want to customize the module, please take a look to our documentation on' mod='paylater'}
                    <a href="https://github.com/PagaMasTarde/prestashop/tree/{$version|escape:'quotes'}">GitHub </a>
                </p>
            </div>
            <div class="column-right">
                <img src="{$logo|escape:'quotes'}"/>
            </div>
    </div>
    {$form|escape:'quotes'}
    {if version_compare($smarty.const._PS_VERSION_,'1.6','<')}
        <script type="text/javascript">
            var d = document.getElementById("module_form");
            d.className += " panel";
        </script>
    {/if}
{/block}
