<?php
/**
 * This file is part of the official Paylater module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2015-2016 Paga+Tarde
 * @license   proprietary
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

define('_PS_PAYLATER_DIR', _PS_MODULE_DIR_. '/paylater');

require _PS_PAYLATER_DIR.'/vendor/autoload.php';

/**
 * Class Paylater
 */
class Paylater extends PaymentModule
{
    /**
     * @var string
     */
    public $url = 'https://pagamastarde.com';

    /**
     * @var bool
     */
    public $bootstrap = true;

    /**
     * Paylater constructor.
     *
     * Define the module main properties so that prestashop understands what are the module requirements
     * and how to manage the module.
     *
     */
    public function __construct()
    {
        $this->name = 'paylater';
        $this->tab = 'payments_gateways';
        $this->version = '7.0.0';
        $this->author = 'Paga+Tarde';
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->module_key = '2b9bc901b4d834bb7069e7ea6510438f';
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);
        $this->displayName = $this->l('Paga+Tarde');
        $this->description = $this->l(
            'Instant, easy and effective financial tool for your customers'
        );

        $sql_file = dirname(__FILE__).'/sql/install.sql';
        $this->loadSQLFile($sql_file);

        parent::__construct();
    }

    /**
     * Configure the database variables for paga+tarde payment method.
     *
     * @return bool
     */
    public function install()
    {
        if (!extension_loaded('curl')) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }

        $sql_file = dirname(__FILE__).'/sql/install.sql';
        $this->loadSQLFile($sql_file);

        Configuration::updateValue('PAYLATER_PROD', false);
        Configuration::updateValue('PAYLATER_PUBLIC_KEY_TEST', '');
        Configuration::updateValue('PAYLATER_PRIVATE_KEY_TEST', '');
        Configuration::updateValue('PAYLATER_PUBLIC_KEY_PROD', '');
        Configuration::updateValue('PAYLATER_PRIVATE_KEY_PROD', '');
        Configuration::updateValue('PAYLATER_DISCOUNT', false);
        Configuration::updateValue('PAYLATER_ADD_SIMULATOR', false);
        Configuration::updateValue('PAYLATER_IFRAME', false);
        Configuration::updateValue('PAYLATER_MIN_AMOUNT', 0);
        Configuration::updateValue('PAYLATER_PRODUCT_HOOK', false);
        Configuration::updateValue('PAYLATER_PRODUCT_HOOK_TYPE', false);
        Configuration::updateValue('PAYLATER_NOTIFY_URL', false);

        return (parent::install()
                && $this->registerHook('displayShoppingCart')
                && $this->registerHook('payment')
                && $this->registerHook('paymentOptions')
                && $this->registerHook('displayRightColumn')
                && $this->registerHook('displayLeftColumn')
                && $this->registerHook('displayRightColumnProduct')
                && $this->registerHook('displayLeftColumnProduct')
                && $this->registerHook('displayProductButtons')
                && $this->registerHook('displayOrderConfirmation')
        );
    }

    /**
     * Remove the production private api key and remove the files
     *
     * @return bool
     */
    public function uninstall()
    {
        Configuration::deleteByName('PAYLATER_PRIVATE_KEY_PROD');

        return parent::uninstall();
    }

    /**
     * @param $sql_file
     *
     * @return bool
     */
    public function loadSQLFile($sql_file)
    {
        $sql_content = Tools::file_get_contents($sql_file);

        // Replace prefix and store SQL command in array
        $sql_content = str_replace('PREFIX_', _DB_PREFIX_, $sql_content);
        $sql_requests = preg_split("/;\s*[\r\n]+/", $sql_content);

        $result = true;
        foreach ($sql_requests as $request) {
            if (!empty($request)) {
                $result &= Db::getInstance()->execute(trim($request));
            }
        }

        // Return result
        return $result;
    }

    /**
     * Check amount of order > minAmount
     * Check valid currency
     * Check API variables are set
     *
     * @return bool
     */
    public function isPaymentMethodAvailable()
    {
        $cart                       = $this->context->cart;
        $currency                   = new Currency($cart->id_currency);
        $availableCurrencies        = array('EUR');
        $paylaterMinAmount          = Configuration::get('PAYLATER_MIN_AMOUNT');
        $paylaterProd               = Configuration::get('PAYLATER_PROD');
        $paylaterPublicKeyTest      = Configuration::get('PAYLATER_PUBLIC_KEY_TEST');
        $paylaterPrivateKeyTest     = Configuration::get('PAYLATER_PRIVATE_KEY_TEST');
        $paylaterPublicKeyProd      = Configuration::get('PAYLATER_PUBLIC_KEY_PROD');
        $paylaterPrivateKeyProd     = Configuration::get('PAYLATER_PRIVATE_KEY_PROD');

        return (
            $cart->getOrderTotal() >= $paylaterMinAmount &&
            in_array($currency->iso_code, $availableCurrencies) &&
            (
                ($paylaterProd && $paylaterPublicKeyProd && $paylaterPrivateKeyProd) ||
                (!$paylaterProd && $paylaterPublicKeyTest && $paylaterPrivateKeyTest)
            )
        );
    }

    /**
     * @param Cart $cart
     *
     * @return array
     */
    private function getButtonTemplateVars(Cart $cart)
    {
        $currency = new Currency(($cart->id_currency));

        return array(
            'paylater_button' => '#paylater_payment_button',
            'paylater_currency_iso' => $currency->iso_code,
            'paylater_cart_total' => $cart->getOrderTotal(),
        );
    }

    /**
     * @return array
     */
    public function hookPaymentOptions()
    {
        if (!$this->isPaymentMethodAvailable()) {
            return array();
        }

        /** @var Cart $cart */
        $cart                   = $this->context->cart;
        $orderTotal             = $cart->getOrderTotal();
        $link                   = $this->context->link;
        $paylaterProd           = Configuration::get('PAYLATER_PROD');
        $paylaterMode           = $paylaterProd == 1 ? 'PROD' : 'TEST';
        $paylaterPublicKey      = Configuration::get('PAYLATER_PUBLIC_KEY_'.$paylaterMode);
        $paylaterDiscount       = Configuration::get('PAYLATER_DISCOUNT');
        $paylaterAddSimulator   = Configuration::get('PAYLATER_ADD_SIMULATOR');

        $this->context->smarty->assign($this->getButtonTemplateVars($cart));
        $this->context->smarty->assign(array(
            'discount'              => $paylaterDiscount ? 1 : 0,
            'amount'                => $orderTotal,
            'publicKey'             => $paylaterPublicKey,
            'includeSimulator'      => $paylaterAddSimulator == 0 ? false : true,
            'simulatorType'         => $paylaterAddSimulator,
        ));

        $paymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $paymentOption
            ->setCallToActionText($this->l('Finance using Paylater'))
            ->setAction($link->getModuleLink('paylater', 'payment'))
            ->setLogo($this->getPathUri(). 'logo.gif')
            ->setModuleName(__CLASS__)
        ;

        if (_PS_VERSION_ >= 1.7) {
            $paymentOption->setAdditionalInformation(
                $this->fetch('module:paylater/views/templates/hook/checkout-17.tpl')
            );
        } else {
            $paymentOption->setAdditionalInformation(
                $this->fetch('module:paylater/views/templates/hook/checkout-15.tpl')
            );
        }

        return array($paymentOption);
    }

    /**
     * Get the form for editing the BackOffice options of the module
     *
     * @return array
     */
    private function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'radio',
                        'class' => 't',
                        'prefix' => '<i class="icon icon-gears"></i>',
                        'label' => $this->l('Working Mode'),
                        'name' => 'PAYLATER_PROD',
                        'values' => array(
                            array(
                                'id' => 'production',
                                'value' => 1,
                                'label' => $this->l('Production') . '<br>',
                            ),
                            array(
                                'id' => 'test',
                                'value' => 0,
                                'label' => $this->l('Test') . '<br>',
                            ),
                        ),
                    ),
                    array(
                        'name' => 'PAYLATER_PUBLIC_KEY_TEST',
                        'suffix' => $this->l('ej: tk_fd53cd467ba49022e4gf215e'),
                        'type' => 'text',
                        'size' => 35,
                        'label' => $this->l('Public TEST API Key'),
                        'prefix' => '<i class="icon icon-key"></i>',
                        'col' => 6,
                    ),
                    array(
                        'name' => 'PAYLATER_PRIVATE_KEY_TEST',
                        'suffix' => $this->l('ej: 21e5723a97459f6a'),
                        'type' => 'text',
                        'size' => 35,
                        'label' => $this->l('Private TEST API Key'),
                        'prefix' => '<i class="icon icon-key"></i>',
                        'col' => 6,
                    ),
                    array(
                        'name' => 'PAYLATER_PUBLIC_KEY_PROD',
                        'suffix' => $this->l('ej: pk_fd53cd4644a49022e4f8215e'),
                        'type' => 'text',
                        'size' => 35,
                        'label' => $this->l('Public PROD API Key'),
                        'prefix' => '<i class="icon icon-key"></i>',
                        'col' => 6,
                    ),
                    array(
                        'name' => 'PAYLATER_PRIVATE_KEY_PROD',
                        'suffix' => $this->l('ej: 21e57bcb97459f6a'),
                        'type' => 'text',
                        'size' => 35,
                        'label' => $this->l('Private PROD API Key'),
                        'prefix' => '<i class="icon icon-key"></i>',
                        'col' => 6,
                    ),
                    array(
                        'type' => 'radio',
                        'class' => 't',
                        'prefix' => '<i class="icon icon-money"></i>',
                        'label' => $this->l('The financial interests will be paid by'),
                        'name' => 'PAYLATER_DISCOUNT',
                        'values' => array(
                            array(
                                'id' => 'true',
                                'value' => 1,
                                'label' => $this->l('The online commerce will cover the cost') . '<br>',
                            ),
                            array(
                                'id' => 'false',
                                'value' => 0,
                                'label' => $this->l('The end client who buys will cover the cost') . '<br>',
                            ),
                        ),
                    ),
                    array(
                        'type' => 'radio',
                        'class' => 't',
                        'label' => $this->l('Payment behavior'),
                        'name' => 'PAYLATER_IFRAME',
                        'prefix' => '<i class="icon icon-desktop"></i>',
                        'values' => array(
                            array(
                                'id' => 'frame',
                                'value' => 1,
                                'label' => $this->l('open on iFrame inside the page') . '<br>',
                            ),
                            array(
                                'id' => 'redirection',
                                'value' => 0,
                                'label' => $this->l('redirect the user to the payment page') . '<br>',
                            ),
                        ),
                    ),
                    array(
                        'type' => 'radio',
                        'class' => 't',
                        'label' => $this->l('Include simulator in product page'),
                        'name' => 'PAYLATER_PRODUCT_HOOK',
                        'prefix' => '<i class="icon icon-puzzle-piece"></i>',
                        'is_bool' => false,
                        'values' => array(
                            array(
                                'id' => 'product-page-hook',
                                'value' => 'no',
                                'label' => $this->l('Don\'t display'). '<br>'
                            ),
                            array(
                                'id' => 'product-page-hook',
                                'value' => 'hookDisplayRightColumn',
                                'label' => $this->l('display in right column'). '<br>'
                            ),
                            array(
                                'id' => 'product-page-hook',
                                'value' => 'hookDisplayLeftColumn',
                                'label' => $this->l('display in left column'). '<br>'
                            ),
                            array(
                                'id' => 'product-page-hook',
                                'value' => 'hookDisplayRightColumnProduct',
                                'label' => $this->l('display in right column of product'). '<br>'
                            ),
                            array(
                                'id' => 'product-page-hook',
                                'value' => 'hookDisplayLeftColumnProduct',
                                'label' => $this->l('display in left column of product'). '<br>'
                            ),
                            array(
                                'id' => 'product-page-hook',
                                'value' => 'hookDisplayProductButtons',
                                'label' => $this->l('display in product buttons (PS 1.7)'). '<br>'
                            ),
                        ),
                    ),
                    array(
                        'type' => 'radio',
                        'class' => 't',
                        'label' => $this->l('Type of simulator in product page'),
                        'name' => 'PAYLATER_PRODUCT_HOOK_TYPE',
                        'prefix' => '<i class="icon icon-puzzle-piece"></i>',
                        'is_bool' => false,
                        'values' => array(
                            array(
                                'id' => 'simulator',
                                'value' => 1,
                                'label' => $this->l('Mini simulator Paylater'). '<br>'
                            ),
                            array(
                                'id' => 'simulator',
                                'value' => 2,
                                'label' => $this->l('Complete simulator Paylater'). '<br>'
                            ),
                            array(
                                'id' => 'simulator',
                                'value' => 3,
                                'label' => $this->l('Selectable simulator Paylater'). '<br>'
                            ),
                            array(
                                'id' => 'simulator',
                                'value' => 4,
                                'label' => $this->l('Descriptive text Paylater'). '<br>'
                            ),
                        ),
                    ),
                    array(
                        'type' => 'radio',
                        'class' => 't',
                        'label' => $this->l('Include simulator in checkout'),
                        'name' => 'PAYLATER_ADD_SIMULATOR',
                        'prefix' => '<i class="icon icon-puzzle-piece"></i>',
                        'is_bool' => false,
                        'values' => array(
                            array(
                                'id' => 'simulator',
                                'value' => 0,
                                'label' => $this->l('Don\'t display'). '<br>'
                            ),
                            array(
                                'id' => 'simulator',
                                'value' => 1,
                                'label' => $this->l('Mini simulator Paylater'). '<br>'
                            ),
                            array(
                                'id' => 'simulator',
                                'value' => 2,
                                'label' => $this->l('Complete simulator Paylater'). '<br>'
                            ),
                            array(
                                'id' => 'simulator',
                                'value' => 3,
                                'label' => $this->l('Selectable simulator Paylater'). '<br>'
                            ),
                            array(
                                'id' => 'simulator',
                                'value' => 4,
                                'label' => $this->l('Descriptive text Paylater'). '<br>'
                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'size' => 4,
                        'desc' => $this->l('ej: 20'),
                        'label' => $this->l('MinAmount to display Paylater'),
                        'name' => 'PAYLATER_MIN_AMOUNT',
                        'required' => false,
                        'prefix' => '<i class="icon icon-bank"></i>',
                        'suffix' => '€'
                    ),
                    array(
                        'type' => 'radio',
                        'class' => 't',
                        'desc' => $this->l('Info: change to canonical URL if you have compatibility issues'),
                        'label' => $this->l('Notification Callback URL'),
                        'name' => 'PAYLATER_NOTIFY_URL',
                        'prefix' => '<i class="icon icon-puzzle-piece"></i>',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'no',
                                'value' => false,
                                'label' => $this->l('Use friendly url'). '<br>'
                            ),
                            array(
                                'id' => 'yes',
                                'value' => true,
                                'label' => $this->l('Use canonical url'). '<br>'
                            ),
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Form configuration function
     *
     * @param array $settings
     *
     * @return string
     */
    private function renderForm(array $settings)
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit'.$this->name;
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $settings,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Function to update the variables of Paga+Tarde Module in the backoffice of prestashop
     *
     * @return string
     * @throws SmartyException
     */
    public function getContent()
    {
        $error = '';
        $message = '';
        $settings = array();
        $settings['PAYLATER_MIN_AMOUNT'] = 0;
        $settingsKeys = array(
            'PAYLATER_PROD',
            'PAYLATER_PUBLIC_KEY_TEST',
            'PAYLATER_PRIVATE_KEY_TEST',
            'PAYLATER_PUBLIC_KEY_PROD',
            'PAYLATER_PRIVATE_KEY_PROD',
            'PAYLATER_DISCOUNT',
            'PAYLATER_ADD_SIMULATOR',
            'PAYLATER_IFRAME',
            'PAYLATER_MIN_AMOUNT',
            'PAYLATER_PRODUCT_HOOK',
            'PAYLATER_PRODUCT_HOOK_TYPE',
            'PAYLATER_NOTIFY_URL',
        );

        //Different Behavior depending on 1.6 or earlier
        if (Tools::isSubmit('submit'.$this->name)) {
            foreach ($settingsKeys as $key) {
                switch ($key) {
                    case 'PAYLATER_MIN_AMOUNT':
                        $value = Tools::getValue($key);
                        if (!$value) {
                            $value = 0;
                        }
                        if (!is_numeric($value)) {
                            $error = $this->l('invalid value for MinAmount');
                            break;
                        }
                        Configuration::updateValue($key, $value);
                        $settings[$key] = $value;
                        break;

                    default:
                        $value = Tools::getValue($key);
                        Configuration::updateValue($key, $value);
                        $settings[$key] = $value;
                        break;
                }
                $message = $this->displayConfirmation($this->l('All changes have been saved'));
            }
        } else {
            foreach ($settingsKeys as $key) {
                    $settings[$key] = Configuration::get($key);
            }
        }

        if ($error) {
            $message = $this->displayError($error);
        }

        $logo = $this->getPathUri(). 'views/img/logo_pagamastarde.png';
        $tpl = $this->local_path.'views/templates/admin/config-info.tpl';
        $this->context->smarty->assign(array(
            'logo' => $logo,
            'form' => $this->renderForm($settings),
            'message' => $message,
        ));

        return $this->context->smarty->fetch($tpl);
    }

    /**
     * Hook to show payment method, this only applies on prestashop <= 1.6
     *
     * @param mixed $params
     *
     * @return string
     */
    public function hookPayment($params)
    {
        if (!$this->isPaymentMethodAvailable()) {
            return false;
        }

        /** @var Cart $cart */
        $cart                   = $params['cart'];
        $orderTotal             = $cart->getOrderTotal();
        $link                   = $this->context->link;
        $paylaterProd           = Configuration::get('PAYLATER_PROD');
        $paylaterMode           = $paylaterProd == 1 ? 'PROD' : 'TEST';
        $paylaterPublicKey      = Configuration::get('PAYLATER_PUBLIC_KEY_'.$paylaterMode);
        $paylaterDiscount       = Configuration::get('PAYLATER_DISCOUNT');
        $paylaterAddSimulator   = Configuration::get('PAYLATER_ADD_SIMULATOR');

        $this->context->smarty->assign($this->getButtonTemplateVars($cart));
        $this->context->smarty->assign(array(
            'discount'              => $paylaterDiscount ? 1 : 0,
            'amount'                => $orderTotal,
            'publicKey'             => $paylaterPublicKey,
            'includeSimulator'      => $paylaterAddSimulator == 0 ? false : true,
            'simulatorType'         => $paylaterAddSimulator,
            'paymentUrl'            => $link->getModuleLink('paylater', 'payment')
        ));

        $supercheckout_enabled = Module::isEnabled('supercheckout');
        $onepagecheckoutps_enabled = Module::isEnabled('onepagecheckoutps');
        $onepagecheckout_enabled = Module::isEnabled('onepagecheckout');


        if ($supercheckout_enabled || $onepagecheckout_enabled || $onepagecheckoutps_enabled) {
            $this->checkLogoExists();
            return $this->display(__FILE__, 'views/templates/hook/onepagecheckout.tpl');
        } elseif (_PS_VERSION_ > 1.7) {
            return $this->display(__FILE__, 'views/templates/hook/checkout-17.tpl');
        } else {
            return $this->display(__FILE__, 'views/templates/hook/checkout-15.tpl');
        }
    }

    /**
     * @param string $functionName
     *
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function productPageSimulatorDisplay($functionName)
    {
        $productConfiguration = Configuration::get('PAYLATER_PRODUCT_HOOK');
        /** @var ProductCore $product */
        $product = new Product(Tools::getValue('id_product'));
        $amount = $product->getPublicPrice();
        $simulatorType          = Configuration::get('PAYLATER_PRODUCT_HOOK_TYPE');
        $paylaterProd           = Configuration::get('PAYLATER_PROD');
        $paylaterMode           = $paylaterProd == 1 ? 'PROD' : 'TEST';
        $paylaterPublicKey      = Configuration::get('PAYLATER_PUBLIC_KEY_'.$paylaterMode);
        $paylaterDiscount       = Configuration::get('PAYLATER_DISCOUNT');
        $minAmount              = Configuration::get('PAYLATER_MIN_AMOUNT');

        if ($functionName != $productConfiguration || $amount <= 0 || $amount < $minAmount) {
            return null;
        }

        $this->context->smarty->assign(array(
            'amount'                => $amount,
            'publicKey'             => $paylaterPublicKey,
            'simulatorType'         => $simulatorType,
            'discount'              => $paylaterDiscount ? 1 : 0,
        ));

        return $this->display(__FILE__, 'views/templates/hook/product-simulator.tpl');
    }

    /**
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookDisplayRightColumn()
    {

        return $this->productPageSimulatorDisplay(__FUNCTION__);
    }

    /**
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookDisplayLeftColumn()
    {
        return $this->productPageSimulatorDisplay(__FUNCTION__);
    }

    /**
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookDisplayRightColumnProduct()
    {
        return $this->productPageSimulatorDisplay(__FUNCTION__);
    }

    /**
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookDisplayLeftColumnProduct()
    {
        return $this->productPageSimulatorDisplay(__FUNCTION__);
    }

    /**
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookDisplayProductButtons()
    {
        return $this->productPageSimulatorDisplay(__FUNCTION__);
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function hookDisplayOrderConfirmation($params)
    {
        $paymentMethod = (_PS_VERSION_ < 1.7) ? ($params["objOrder"]->payment) : ($params["order"]->payment);

        if ($paymentMethod == $this->displayName) {
            return $this->display(__FILE__, 'views/templates/hook/payment-return.tpl');
        }

        return null;
    }

    /**
     * Check logo exists in OPC module
     */
    public function checkLogoExists()
    {
        $logo = _PS_MODULE_DIR_ . '/onepagecheckoutps/views/img/payments/'. Tools::strtolower(__CLASS__). '.png';
        if (!file_exists($logo) && is_dir(_PS_MODULE_DIR_ . '/onepagecheckoutps/views/img/payments')) {
            copy(
                _PS_PAYLATER_DIR . '/views/img/logo-64x64.png',
                $logo
            );
        }
    }
}
