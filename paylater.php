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
define('PAYLATER_SHOPPER_URL', 'https://shopper.pagamastarde.com/prestashop/');
define('PROMOTIONS_CATEGORY', 'paylater-promotion-product');

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
        $this->version = '6.2.0';
        $this->author = 'Paga+Tarde';
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->module_key = '2b9bc901b4d834bb7069e7ea6510438f';
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);
        $this->displayName = $this->l('Paga+Tarde');
        $this->description = $this->l(
            'Instant, easy and effective financial tool for your customers'
        );

        $sql_content = "show tables like 'PREFIX_pmt_cart_process'";
        $sql_content = str_replace('PREFIX_', _DB_PREFIX_, $sql_content);
        $table_exists = Db::getInstance()->executeS($sql_content);
        if (empty($table_exists)) {
            $sql_file = dirname(__FILE__).'/sql/install.sql';
            $this->loadSQLFile($sql_file);
        }
        $this->checkPromotionCategory();

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

        return (parent::install()
                && $this->registerHook('displayShoppingCart')
                && $this->registerHook('payment')
                && $this->registerHook('paymentOptions')
                && $this->registerHook('displayRightColumn')
                && $this->registerHook('displayLeftColumn')
                && $this->registerHook('displayRightColumnProduct')
                && $this->registerHook('displayLeftColumnProduct')
                && $this->registerHook('displayProductButtons')
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
        // Get install SQL file content
        $sql_content = Tools::file_get_contents($sql_file);

        // Replace prefix and store SQL command in array
        $sql_content = str_replace('PREFIX_', _DB_PREFIX_, $sql_content);
        $sql_requests = preg_split("/;\s*[\r\n]+/", $sql_content);

        // Execute each SQL statement
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
     * checkPromotionCategory
     */
    public function checkPromotionCategory()
    {
        $categories = CategoryCore::getCategories(null, false, false);
        $categories = array_column($categories, 'name');
        if (!in_array(PROMOTIONS_CATEGORY, $categories)) {
            $category = new CategoryCore();
            $category->is_root_category = false;
            $category->link_rewrite = [ 1=> PROMOTIONS_CATEGORY ];
            $category->meta_description = [ 1=> PROMOTIONS_CATEGORY ];
            $category->meta_keywords = [ 1=> PROMOTIONS_CATEGORY ];
            $category->meta_title = [ 1=> PROMOTIONS_CATEGORY ];
            $category->name = [ 1=> PROMOTIONS_CATEGORY ];
            $category->id_parent = Configuration::get('PS_HOME_CATEGORY');
            $category->active=0;
            $category->description = <<<EOD
If assigned, this product will have free interests and the shop will cover the cost of the loan. Use this to promote
a special product or improve the average ticket by asume the interests of big products. You can also do promotions per
brand or by any other elegible attributes. Just add this category to the product and the customer of your shop will see
in the simulator and finally in the loan process that it\'s free for him.
EOD;
            $category->save();
        }
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
            'PAYLATER_PRODUCT_HOOK_TYPE'
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
        $css = 'https://shopper.pagamastarde.com/css/paylater-modal.min.css';
        $prestashopCss = 'https://shopper.pagamastarde.com/css/paylater-prestashop.min.css';
        $tpl = $this->local_path.'views/templates/admin/config-info.tpl';
        $this->context->smarty->assign(array(
            'logo' => $logo,
            'form' => $this->renderForm($settings),
            'message' => $message,
            'css' => $css,
            'prestashopCss' => $prestashopCss,
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
        $css = 'https://shopper.pagamastarde.com/css/paylater-modal.min.css';
        $prestashopCss = 'https://shopper.pagamastarde.com/css/paylater-prestashop.min.css';

        $this->context->smarty->assign($this->getButtonTemplateVars($cart));
        $this->context->smarty->assign(array(
            'discount'              => $paylaterDiscount ? 1 : 0,
            'amount'                => $orderTotal,
            'publicKey'             => $paylaterPublicKey,
            'includeSimulator'      => $paylaterAddSimulator == 0 ? false : true,
            'simulatorType'         => $paylaterAddSimulator,
            'css'                   => $css,
            'prestashopCss'         =>  $prestashopCss,
            'paymentUrl'            => $link->getModuleLink('paylater', 'payment')
        ));

        if (_PS_VERSION_ > 1.7) {
            return $this->display(__FILE__, 'views/templates/hook/checkout-17.tpl');
        } else {
            return $this->display(__FILE__, 'views/templates/hook/checkout-15.tpl');
        }
    }

    /**
     * @param $functionName
     *
     * @return string|null
     */
    public function productPageSimulatorDisplay($functionName)
    {
        $productConfiguration = Configuration::get('PAYLATER_PRODUCT_HOOK');
        $product = new Product(Tools::getValue('id_product'));
        $amount = $product->getPublicPrice();
        $simulatorType          = Configuration::get('PAYLATER_PRODUCT_HOOK_TYPE');
        $paylaterProd           = Configuration::get('PAYLATER_PROD');
        $paylaterMode           = $paylaterProd == 1 ? 'PROD' : 'TEST';
        $paylaterPublicKey      = Configuration::get('PAYLATER_PUBLIC_KEY_'.$paylaterMode);
        $paylaterDiscount       = Configuration::get('PAYLATER_DISCOUNT');

        if ($functionName != $productConfiguration || $amount <= 0) {
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
     */
    public function hookDisplayRightColumn()
    {

        return $this->productPageSimulatorDisplay(__FUNCTION__);
    }

    /**
     * @return string
     */
    public function hookDisplayLeftColumn()
    {
        return $this->productPageSimulatorDisplay(__FUNCTION__);
    }

    /**
     * @return string
     */
    public function hookDisplayRightColumnProduct()
    {
        return $this->productPageSimulatorDisplay(__FUNCTION__);
    }

    /**
     * @return string
     */
    public function hookDisplayLeftColumnProduct()
    {
        return $this->productPageSimulatorDisplay(__FUNCTION__);
    }

    /**
     * @return string
     */
    public function hookDisplayProductButtons()
    {
        return $this->productPageSimulatorDisplay(__FUNCTION__);
    }
}
