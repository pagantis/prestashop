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
        $this->version = '7.0.4';
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
        if (!version_compare(phpversion(), '5.3.0', '>=')) {
            $this->_errors[] = $this->l('The PHP version bellow 5.3.0 is not supported');
            return false;
        }
        $curl_info = curl_version();
        $curl_version = $curl_info['version'];
        if (!version_compare($curl_version, '7.34.0', '>=')) {
            $this->_errors[] = $this->l('Curl Version is lower than 7.34.0 and does not support TLS 1.2');
            return false;
        }

        $sql_file = dirname(__FILE__).'/sql/install.sql';
        $this->loadSQLFile($sql_file);

        Configuration::updateValue('pmt_public_key', '');
        Configuration::updateValue('pmt_private_key', '');
        Configuration::updateValue('pmt_iframe', 0);
        Configuration::updateValue('pmt_title', $this->l('Instant Financing'));
        Configuration::updateValue('pmt_url_ok', $this->context->link->getPageLink(
            'order-confirmation',
            null,
            null
        ));
        Configuration::updateValue('pmt_url_ko', $this->context->link->getPageLink(
            'order',
            null,
            null,
            array('step'=>3)
        ));
        Configuration::updateValue('pmt_sim_checkout', 0); //TODO Back to 6 after simulator in orders
        Configuration::updateValue('pmt_sim_product', 6);
        Configuration::updateValue('pmt_sim_product_hook', 'hookDisplayProductButtons');
        Configuration::updateValue('pmt_sim_quotes_start', 3);
        Configuration::updateValue('pmt_sim_quotes_max', 12);
        Configuration::updateValue('pmt_display_min_amount', 1);

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
        $pmtDisplayMinAmount          = Configuration::get('pmt_display_min_amount');
        $pmtPublicKey               = Configuration::get('pmt_public_key');
        $pmtPrivateKey             = Configuration::get('pmt_private_key');

        return (
            $cart->getOrderTotal() >= $pmtDisplayMinAmount &&
            in_array($currency->iso_code, $availableCurrencies) &&
            $pmtPublicKey &&
            $pmtPrivateKey
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
        $cart                       = $this->context->cart;
        $orderTotal                 = $cart->getOrderTotal();
        $link                       = $this->context->link;
        $pmtPublicKey               = Configuration::get('pmt_public_key');
        $pmtSimulatorCheckout       = Configuration::get('pmt_sim_checkout');
        $pmtSimulatorQuotesStart    = Configuration::get('pmt_sim_quotes_start');
        $pmtSimulatorQuotesMax      = Configuration::get('pmt_sim_quotes_max');
        $pmtTitle                   = Configuration::get('pmt_title');

        $this->context->smarty->assign($this->getButtonTemplateVars($cart));
        $this->context->smarty->assign(array(
            'amount'                => $orderTotal,
            'pmtPublicKey'          => $pmtPublicKey,
            'pmtQuotesStart'        => $pmtSimulatorQuotesStart,
            'pmtQuotesMax'          => $pmtSimulatorQuotesMax,
            'pmtSimulatorCheckout'  => $pmtSimulatorCheckout,
            'pmtTitle'              => $pmtTitle,
            'paymentUrl'            => $link->getModuleLink('paylater', 'payment'),
        ));

        $paymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $paymentOption
            ->setCallToActionText($pmtTitle)
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
                    'title' => $this->l('Basic Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'name' => 'pmt_public_key',
                        'suffix' => $this->l('ej: pk_fd53cd467ba49022e4gf215e'),
                        'type' => 'text',
                        'size' => 60,
                        'label' => $this->l('Public Key'),
                        'prefix' => '<i class="icon icon-key"></i>',
                        'col' => 6,
                        'required' => true,
                    ),
                    array(
                        'name' => 'pmt_private_key',
                        'suffix' => $this->l('ej: 21e5723a97459f6a'),
                        'type' => 'text',
                        'size' => 60,
                        'label' => $this->l('Secret Key'),
                        'prefix' => '<i class="icon icon-key"></i>',
                        'col' => 6,
                        'required' => true,
                    ),
                    array(
                        'type' => 'radio',
                        'class' => 't',
                        'label' => $this->l('How to open payment'),
                        'name' => 'pmt_iframe',
                        'prefix' => '<i class="icon icon-desktop"></i>',
                        'values' => array(
                            array(
                                'id' => 'frame',
                                'value' => 1,
                                'label' => $this->l('iFrame') . '<br>',
                            ),
                            array(
                                'id' => 'redirection',
                                'value' => 0,
                                'label' => '<strong style="color: green">' . $this->l('Redirect').'</strong><br>'

                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'size' => 60,
                        'col' => 6,
                        'desc' =>  $this->l('ex: Instant Financing'),
                        'label' => $this->l('Title'),
                        'name' => 'pmt_title',
                        'required' => true,
                        'prefix' => '<i class="icon icon-puzzle-piece"></i>',
                    ),
                    //TODO UNCOMMENT WHEN SIMULATOR READY IN ORDERS
                    /*
                    array(
                        'type' => 'radio',
                        'class' => 't',
                        'label' => $this->l('Simulator in checkout page'),
                        'name' => 'pmt_sim_checkout',
                        'prefix' => '<i class="icon icon-puzzle-piece"></i>',
                        'is_bool' => false,
                        'values' => array(
                            array(
                                'id' => 'checkout-simulator-hide',
                                'value' => 0,
                                'label' => $this->l('Hide'). '<br>'
                            ),
                            array(
                                'id' => 'checkout-simulator-simple',
                                'value' => 1,
                                'label' => $this->l('Simple Simulator'). '<br>'
                            ),
                            array(
                                'id' => 'checkout-simulator-complete',
                                'value' => 2,
                                'label' => $this->l('Complete Simulator'). '<br>'
                            ),
                            array(
                                'id' => 'checkout-simulator-selectable',
                                'value' => 3,
                                'label' => $this->l('Selectable Simulator'). '<br>'
                            ),
                            array(
                                'id' => 'checkout-simulator-text',
                                'value' => 4,
                                'label' => $this->l('Text Simulator'). '<br>'
                            ),
                            array(
                                'id' => 'checkout-simulator-mini',
                                'value' => 6,
                                'label' => '<strong style="color: green">' . $this->l('Mini Simulator').'</strong><br>'
                            ),
                        ),
                    ),
                    */
                    array(
                        'type' => 'radio',
                        'class' => 't',
                        'label' => $this->l('Product simulator'),
                        'name' => 'pmt_sim_product',
                        'prefix' => '<i class="icon icon-puzzle-piece"></i>',
                        'is_bool' => false,
                        'values' => array(
                            array(
                                'id' => 'product-simulator-hide',
                                'value' => 0,
                                'label' => $this->l('Hide'). '<br>'
                            ),
                            array(
                                'id' => 'product-simulator-simple',
                                'value' => 1,
                                'label' => $this->l('Simple Simulator'). '<br>'
                            ),
                            array(
                                'id' => 'product-simulator-complete',
                                'value' => 2,
                                'label' => $this->l('Complete Simulator'). '<br>'
                            ),
                            array(
                                'id' => 'product-simulator-selectable',
                                'value' => 3,
                                'label' => $this->l('Selectable Simulator'). '<br>'
                            ),
                            array(
                                'id' => 'product-simulator-text',
                                'value' => 4,
                                'label' => $this->l('Text Simulator'). '<br>'
                            ),
                            array(
                                'id' => 'product-simulator-mini',
                                'value' => 6,
                                'label' => '<strong style="color: green">' . $this->l('Mini Simulator').'</strong><br>'
                            ),
                        ),
                    ),
                    array(
                        'type' => 'radio',
                        'class' => 't',
                        'label' => $this->l('Simulator product position'),
                        'name' => 'pmt_sim_product_hook',
                        'prefix' => '<i class="icon icon-puzzle-piece"></i>',
                        'is_bool' => false,
                        'values' => array(
                            array(
                                'id' => 'hookDisplayRightColumn-page-hook',
                                'value' => 'hookDisplayRightColumn',
                                'label' => $this->l('display in right column'). '<br>'
                            ),
                            array(
                                'id' => 'hookDisplayLeftColumn',
                                'value' => 'hookDisplayLeftColumn',
                                'label' => $this->l('display in left column'). '<br>'
                            ),
                            array(
                                'id' => 'hookDisplayRightColumnProduct',
                                'value' => 'hookDisplayRightColumnProduct',
                                'label' => $this->l('display in right column of product'). '<br>'
                            ),
                            array(
                                'id' => 'hookDisplayLeftColumnProduct',
                                'value' => 'hookDisplayLeftColumnProduct',
                                'label' => $this->l('display in left column of product'). '<br>'
                            ),
                            array(
                                'id' => 'hookDisplayProductButtons',
                                'value' => 'hookDisplayProductButtons',
                                'label' => $this->l('display in product buttons (PS 1.7)'). '<br>'
                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'col' => 1,
                        'desc' => $this->l('Between: 2-12'),
                        'label' => $this->l('Number of installments by default'),
                        'name' => 'pmt_sim_quotes_start',
                        'required' => false,
                        'prefix' => '<i class="icon icon-puzzle-piece"></i>',
                    ),
                    array(
                        'type' => 'text',
                        'col' => 1,
                        'desc' => $this->l('Between: 2-12'),
                        'label' => $this->l('Maximum numbers of installments'),
                        'name' => 'pmt_sim_quotes_max',
                        'required' => false,
                        'prefix' => '<i class="icon icon-puzzle-piece"></i>',
                    ),
                    array(
                        'type' => 'text',
                        'col' => 2,
                        'desc' => $this->l('ej: 20'),
                        'label' => $this->l('Minimum amount'),
                        'name' => 'pmt_display_min_amount',
                        'required' => false,
                        'prefix' => '<i class="icon icon-bank"></i>',
                        'suffix' => '€'
                    ),
                    array(
                        'type' => 'text',
                        'col' => 6,
                        'label' => $this->l('Ok url'),
                        'name' => 'pmt_url_ok',
                        'required' => false,
                        'prefix' => '<i class="icon icon-puzzle-piece"></i>',
                    ),
                    array(
                        'type' => 'text',
                        'col' => 6,
                        'label' => $this->l('Ko url'),
                        'name' => 'pmt_url_ko',
                        'required' => false,
                        'prefix' => '<i class="icon icon-puzzle-piece"></i>',
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
        $settings['pmt_public_key'] = Configuration::get('pmt_public_key');
        $settings['pmt_private_key'] = Configuration::get('pmt_private_key');
        $settings['pmt_title'] = Configuration::get('pmt_title');
        $settings['pmt_display_min_amount'] = 0;
        $settingsKeys = array(
            'pmt_public_key',
            'pmt_private_key',
            'pmt_iframe',
            'pmt_title',
            'pmt_sim_checkout',
            'pmt_sim_product',
            'pmt_sim_product_hook',
            'pmt_sim_quotes_start',
            'pmt_sim_quotes_max',
            'pmt_display_min_amount',
            'pmt_url_ok',
            'pmt_url_ko',
        );

        //Different Behavior depending on 1.6 or earlier
        if (Tools::isSubmit('submit'.$this->name)) {
            foreach ($settingsKeys as $key) {
                switch ($key) {
                    case 'pmt_display_min_amount':
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
                    case 'pmt_public_key':
                        $value = Tools::getValue($key);
                        if (!$value) {
                            $error = $this->l('Please add a Paga+Tarde API Public Key');
                            break;
                        }
                        Configuration::updateValue($key, $value);
                        $settings[$key] = $value;
                        break;
                    case 'pmt_private_key':
                        $value = Tools::getValue($key);
                        if (!$value) {
                            $error = $this->l('Please add a Paga+Tarde API Private Key');
                            break;
                        }
                        Configuration::updateValue($key, $value);
                        $settings[$key] = $value;
                        break;
                    case 'pmt_title':
                        $value = Tools::getValue($key);
                        if (!$value) {
                            $error = $this->l('Please add a Title for the payment method');
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
        $cart                       = $params['cart'];
        $orderTotal                 = $cart->getOrderTotal();
        $link                       = $this->context->link;
        $pmtPublicKey               = Configuration::get('pmt_public_key');
        $pmtSimulatorCheckout       = Configuration::get('pmt_sim_checkout');
        $pmtSimulatorQuotesStart    = Configuration::get('pmt_sim_quotes_start');
        $pmtSimulatorQuotesMax      = Configuration::get('pmt_sim_quotes_max');
        $pmtTitle                   = Configuration::get('pmt_title');
        $this->context->smarty->assign($this->getButtonTemplateVars($cart));
        $this->context->smarty->assign(array(
            'amount'                => $orderTotal,
            'pmtPublicKey'          => $pmtPublicKey,
            'pmtQuotesStart'        => $pmtSimulatorQuotesStart,
            'pmtQuotesMax'          => $pmtSimulatorQuotesMax,
            'pmtSimulatorCheckout'  => $pmtSimulatorCheckout,
            'pmtTitle'              => $pmtTitle,
            'paymentUrl'            => $link->getModuleLink('paylater', 'payment'),
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
        $productConfiguration = Configuration::get('pmt_sim_product_hook');
        /** @var ProductCore $product */
        $product = new Product(Tools::getValue('id_product'));
        $amount = $product->getPublicPrice();
        $pmtPublicKey             = Configuration::get('pmt_public_key');
        $pmtSimulatorProduct      = Configuration::get('pmt_sim_product');
        $pmtSimulatorQuotesStart  = Configuration::get('pmt_sim_quotes_start');
        $pmtSimulatorQuotesMax    = Configuration::get('pmt_sim_quotes_max');
        $pmtDisplayMinAmount      = Configuration::get('pmt_display_min_amount');

        if ($functionName != $productConfiguration ||
            $amount <= 0 ||
            $amount < $pmtDisplayMinAmount ||
            !$pmtSimulatorProduct
        ) {
            return null;
        }

        $this->context->smarty->assign(array(
            'amount'                => $amount,
            'pmtPublicKey'          => $pmtPublicKey,
            'pmtSimulatorProduct'   => $pmtSimulatorProduct,
            'pmtQuotesStart'        => $pmtSimulatorQuotesStart,
            'pmtQuotesMax'          => $pmtSimulatorQuotesMax,
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
