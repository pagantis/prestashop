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
     * @var array
     */
    public $installErrors = array();

    /**
     * Default module advanced configuration values
     *
     * @var array
     */
    public $defaultConfigs = array(
        'PMT_TITLE' => 'Instant Financing',
        'PMT_SIMULATOR_DISPLAY_TYPE' => 'pmtSDK.simulator.types.SIMPLE',
        'PMT_SIMULATOR_DISPLAY_SKIN' => 'pmtSDK.simulator.skins.BLUE',
        'PMT_SIMULATOR_DISPLAY_POSITION' => 'hookDisplayProductButtons',
        'PMT_SIMULATOR_START_INSTALLMENTS' => '3',
        'PMT_SIMULATOR_CSS_POSITION_SELECTOR' => 'default',
        'PMT_SIMULATOR_DISPLAY_CSS_POSITION' => 'pmtSDK.simulator.positions.INNER',
        'PMT_SIMULATOR_CSS_PRICE_SELECTOR' => 'default',
        'PMT_SIMULATOR_CSS_QUANTITY_SELECTOR' => 'default',
        'PMT_FORM_DISPLAY_TYPE' => '0',
        'PMT_DISPLAY_MIN_AMOUNT' => '1',
        'PMT_URL_OK' => '',
        'PMT_URL_KO' => '',
    );

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
        $this->version = '7.2.2';
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

        $this->loadEnvVariables();

        $this->migrate();

        $this->checkHooks();

        parent::__construct();
    }

    /**
     * Configure the variables for paga+tarde payment method.
     *
     * @return bool
     */
    public function install()
    {
        if (!extension_loaded('curl')) {
            $this->installErrors[] =
                $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }
        if (!version_compare(phpversion(), '5.3.0', '>=')) {
            $this->installErrors[] = $this->l('The PHP version bellow 5.3.0 is not supported');
            return false;
        }
        $curl_info = curl_version();
        $curl_version = $curl_info['version'];
        if (!version_compare($curl_version, '7.34.0', '>=')) {
            $this->installErrors[] = $this->l('Curl Version is lower than 7.34.0 and does not support TLS 1.2');
            return false;
        }

        Configuration::updateValue('pmt_is_enabled', 1);
        Configuration::updateValue('pmt_simulator_is_enabled', 1);
        Configuration::updateValue('pmt_public_key', '');
        Configuration::updateValue('pmt_private_key', '');

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
            && $this->registerHook('header')
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
     * Migrate the configs of older versions < 7x to new configurations
     */
    public function migrate()
    {
        if (Configuration::get('PAYLATER_MIN_AMOUNT')) {
            Db::getInstance()->update(
                'pmt_config',
                array('value' => Configuration::get('PAYLATER_MIN_AMOUNT')),
                'config = \'PMT_DISPLAY_MIN_AMOUNT\''
            );
            Configuration::updateValue('PAYLATER_MIN_AMOUNT', false);
            Configuration::updateValue('pmt_is_enabled', 1);
            Configuration::updateValue('pmt_simulator_is_enabled', 1);

            // migrating pk/tk from previous version
            if (Configuration::get('pmt_public_key') === false
                && Configuration::get('PAYLATER_PUBLIC_KEY_PROD')
            ) {
                Configuration::updateValue('pmt_public_key', Configuration::get('PAYLATER_PUBLIC_KEY_PROD'));
                Configuration::updateValue('PAYLATER_PUBLIC_KEY_PROD', false);
            } elseif (Configuration::get('pmt_public_key') === false
                && Configuration::get('PAYLATER_PUBLIC_KEY_TEST')
            ) {
                Configuration::updateValue('pmt_public_key', Configuration::get('PAYLATER_PUBLIC_KEY_TEST'));
                Configuration::updateValue('PAYLATER_PUBLIC_KEY_TEST', false);
            }

            if (Configuration::get('pmt_private_key') === false
                && Configuration::get('PAYLATER_PRIVATE_KEY_PROD')
            ) {
                Configuration::updateValue('pmt_private_key', Configuration::get('PAYLATER_PRIVATE_KEY_PROD'));
                Configuration::updateValue('PAYLATER_PRIVATE_KEY_PROD', false);
            } elseif (Configuration::get('pmt_private_key') === false
                && Configuration::get('PAYLATER_PRIVATE_KEY_TEST')
            ) {
                Configuration::updateValue('pmt_private_key', Configuration::get('PAYLATER_PRIVATE_KEY_TEST'));
                Configuration::updateValue('PAYLATER_PRIVATE_KEY_TEST', false);
            }
        }
    }

    /**
     * Check if new hooks used in new 7x versions are enabled and activate them if needed
     *
     * @throws PrestaShopDatabaseException
     */
    public function checkHooks()
    {
        try {
            $sql_content = 'select * from ' . _DB_PREFIX_. 'hook_module where 
            id_module = \'' . Module::getModuleIdByName($this->name) . '\' and 
            id_shop = \'' . Shop::getContextShopID() . '\' and 
            id_hook = \'' . Hook::getIdByName('header') . '\'';
            $hook_exists = Db::getInstance()->ExecuteS($sql_content);
            if (empty($hook_exists)) {
                $sql_insert = 'insert into ' . _DB_PREFIX_.  'hook_module 
            (id_module, id_shop, id_hook, position)
            values
            (\''. Module::getModuleIdByName($this->name) . '\',
            \''. Shop::getContextShopID() . '\',
            \''. Hook::getIdByName('header') . '\',
            150)';
                Db::getInstance()->execute($sql_insert);
            }
        } catch (\Exception $exception) {
            // continue without errors
        }
    }

    public function loadEnvVariables()
    {
        $sql_content = 'select * from ' . _DB_PREFIX_. 'pmt_config';
        $dbConfigs = Db::getInstance()->executeS($sql_content);

        // Convert a multimple dimension array for SQL insert statements into a simple key/value
        $simpleDbConfigs = array();
        foreach ($dbConfigs as $config) {
            $simpleDbConfigs[$config['config']] = $config['value'];
        }
        $newConfigs = array_diff_key($this->defaultConfigs, $simpleDbConfigs);
        if (!empty($newConfigs)) {
            $data = array();
            foreach ($newConfigs as $key => $value) {
                $data[] = array(
                    'config' => $key,
                    'value' => $value,
                );
            }
            Db::getInstance()->insert('pmt_config', $data);
        }

        foreach (array_merge($this->defaultConfigs, $simpleDbConfigs) as $key => $value) {
            putenv($key . '=' . $value);
        }

        putenv("PMT_DEFAULT_CONFIGS" . '=' . json_encode($this->defaultConfigs));
    }

    /**
     * @param $sql_file
     * @return bool
     */
    public function loadSQLFile($sql_file)
    {
        $sql_content = Tools::file_get_contents($sql_file);
        $sql_content = str_replace('PREFIX_', _DB_PREFIX_, $sql_content);
        $sql_requests = preg_split("/;\s*[\r\n]+/", $sql_content);

        $result = true;
        foreach ($sql_requests as $request) {
            if (!empty($request)) {
                $result &= Db::getInstance()->execute(trim($request));
            }
        }

        return $result;
    }

    /**
     * Check amount of order > minAmount
     * Check valid currency
     * Check API variables are set
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function isPaymentMethodAvailable()
    {
        $cart                       = $this->context->cart;
        $currency                   = new Currency($cart->id_currency);
        $availableCurrencies        = array('EUR');
        $pmtDisplayMinAmount        = getenv('PMT_DISPLAY_MIN_AMOUNT');
        $pmtPublicKey               = Configuration::get('pmt_public_key');
        $pmtPrivateKey              = Configuration::get('pmt_private_key');

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
     * @throws Exception
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
     * Header hook
     */
    public function hookHeader()
    {
        if (_PS_VERSION_ >= "1.7") {
            $this->context->controller->registerJavascript(
                sha1(mt_rand(1, 90000)),
                'https://cdn.pagamastarde.com/js/pmt-v2/sdk.js',
                array('server' => 'remote')
            );
        } else {
            $this->context->controller->addJS('https://cdn.pagamastarde.com/js/pmt-v2/sdk.js');
        }
        $this->context->controller->addJS($this->getPathUri(). 'views/js/simulator.js');
    }

    /**
     * @return array
     * @throws Exception
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
        $pmtSimulatorIsEnabled      = Configuration::get('pmt_simulator_is_enabled');
        $pmtIsEnabled               = Configuration::get('pmt_is_enabled');
        $pmtSimulatorType           = getenv('PMT_SIMULATOR_DISPLAY_TYPE');
        $pmtSimulatorCSSSelector    = getenv('PMT_SIMULATOR_CSS_POSITION_SELECTOR');
        $pmtSimulatorPriceSelector  = getenv('PMT_SIMULATOR_CSS_PRICE_SELECTOR');
        $pmtSimulatorQuotesStart    = getenv('PMT_SIMULATOR_START_INSTALLMENTS');
        $pmtSimulatorSkin           = getenv('PMT_SIMULATOR_DISPLAY_SKIN');
        $pmtSimulatorPosition       = getenv('PMT_SIMULATOR_DISPLAY_CSS_POSITION');
        $pmtTitle                   = $this->l(getenv('PMT_TITLE'));

        $this->context->smarty->assign($this->getButtonTemplateVars($cart));
        $this->context->smarty->assign(array(
            'amount'                => $orderTotal,
            'pmtPublicKey'          => $pmtPublicKey,
            'pmtCSSSelector'        => $pmtSimulatorCSSSelector,
            'pmtPriceSelector'      => $pmtSimulatorPriceSelector,
            'pmtQuotesStart'        => $pmtSimulatorQuotesStart,
            'pmtSimulatorIsEnabled' => $pmtSimulatorIsEnabled,
            'pmtSimulatorType'      => $pmtSimulatorType,
            'pmtSimulatorSkin'      => $pmtSimulatorSkin,
            'pmtSimulatorPosition'  => $pmtSimulatorPosition,
            'pmtIsEnabled'          => $pmtIsEnabled,
            'pmtTitle'              => $pmtTitle,
            'paymentUrl'            => $link->getModuleLink('paylater', 'payment'),
            'ps_version'            => str_replace('.', '-', Tools::substr(_PS_VERSION_, 0, 3)),
        ));

        $paymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $paymentOption
            ->setCallToActionText($pmtTitle)
            ->setAction($link->getModuleLink('paylater', 'payment'))
            ->setLogo($this->getPathUri(). 'logo.gif')
            ->setModuleName(__CLASS__)
        ;


        if (_PS_VERSION_ < 1.7) {
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
                        'name' => 'pmt_is_enabled',
                        'type' =>  (version_compare(_PS_VERSION_, '1.6')<0) ?'radio' :'switch',
                        'label' => $this->l('Module is enabled'),
                        'prefix' => '<i class="icon icon-key"></i>',
                        'class' => 't',
                        'required' => true,
                        'values'=> array(
                            array(
                                'id' => 'pmt_is_enabled_true',
                                'value' => 1,
                                'label' => $this->l('Yes', get_class($this), null, false),
                            ),
                            array(
                                'id' => 'pmt_is_enabled_false',
                                'value' => 0,
                                'label' => $this->l('No', get_class($this), null, false),
                            ),
                        )
                    ),
                    array(
                        'name' => 'pmt_public_key',
                        'suffix' => $this->l('ex: pk_fd53cd467ba49022e4gf215e'),
                        'type' => 'text',
                        'size' => 60,
                        'label' => $this->l('Public Key'),
                        'prefix' => '<i class="icon icon-key"></i>',
                        'col' => 6,
                        'required' => true,
                    ),
                    array(
                        'name' => 'pmt_private_key',
                        'suffix' => $this->l('ex: 21e5723a97459f6a'),
                        'type' => 'text',
                        'size' => 60,
                        'label' => $this->l('Secret Key'),
                        'prefix' => '<i class="icon icon-key"></i>',
                        'col' => 6,
                        'required' => true,
                    ),
                    array(
                        'name' => 'pmt_simulator_is_enabled',
                        'type' => (version_compare(_PS_VERSION_, '1.6')<0) ?'radio' :'switch',
                        'label' => $this->l('Simulator is enabled'),
                        'prefix' => '<i class="icon icon-key"></i>',
                        'class' => 't',
                        'required' => true,
                        'values'=> array(
                            array(
                                'id' => 'pmt_simulator_is_enabled_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ),
                            array(
                                'id' => 'pmt_simulator_is_enabled_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ),
                        )
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

        $helper->fields_value['pmt_url_ok'] = Configuration::get('pmt_url_ok');

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
        $settingsKeys = array(
            'pmt_is_enabled',
            'pmt_public_key',
            'pmt_private_key',
            'pmt_simulator_is_enabled',
        );

        //Different Behavior depending on 1.6 or earlier
        if (Tools::isSubmit('submit'.$this->name)) {
            foreach ($settingsKeys as $key) {
                switch ($key) {
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
            'version' => 'v'.$this->version,
        ));

        return $this->context->smarty->fetch($tpl);
    }

    /**
     * Hook to show payment method, this only applies on prestashop <= 1.6
     *
     * @param $params
     * @return bool | string
     * @throws Exception
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
        $pmtSimulatorIsEnabled      = Configuration::get('pmt_simulator_is_enabled');
        $pmtIsEnabled               = Configuration::get('pmt_is_enabled');
        $pmtSimulatorType           = getenv('PMT_SIMULATOR_DISPLAY_TYPE');
        $pmtSimulatorCSSSelector    = getenv('PMT_SIMULATOR_CSS_POSITION_SELECTOR');
        $pmtSimulatorPriceSelector  = getenv('PMT_SIMULATOR_CSS_PRICE_SELECTOR');
        $pmtSimulatorQuotesStart    = getenv('PMT_SIMULATOR_START_INSTALLMENTS');
        $pmtSimulatorSkin           = getenv('PMT_SIMULATOR_DISPLAY_SKIN');
        $pmtSimulatorPosition       = getenv('PMT_SIMULATOR_DISPLAY_CSS_POSITION');
        $pmtTitle                   = $this->l(getenv('PMT_TITLE'));
        $this->context->smarty->assign($this->getButtonTemplateVars($cart));
        $this->context->smarty->assign(array(
            'amount'                => $orderTotal,
            'pmtPublicKey'          => $pmtPublicKey,
            'pmtCSSSelector'        => $pmtSimulatorCSSSelector,
            'pmtPriceSelector'      => $pmtSimulatorPriceSelector,
            'pmtQuotesStart'        => $pmtSimulatorQuotesStart,
            'pmtSimulatorIsEnabled' => $pmtSimulatorIsEnabled,
            'pmtSimulatorType'      => $pmtSimulatorType,
            'pmtSimulatorSkin'      => $pmtSimulatorSkin,
            'pmtSimulatorPosition'  => $pmtSimulatorPosition,
            'pmtIsEnabled'          => $pmtIsEnabled,
            'pmtTitle'              => $pmtTitle,
            'paymentUrl'            => $link->getModuleLink('paylater', 'payment'),
            'ps_version'            => str_replace('.', '-', Tools::substr(_PS_VERSION_, 0, 3)),
        ));

        $supercheckout_enabled = Module::isEnabled('supercheckout');
        $onepagecheckoutps_enabled = Module::isEnabled('onepagecheckoutps');
        $onepagecheckout_enabled = Module::isEnabled('onepagecheckout');

        $return = true;
        if ($supercheckout_enabled || $onepagecheckout_enabled || $onepagecheckoutps_enabled) {
            $this->checkLogoExists();
            $return = $this->display(__FILE__, 'views/templates/hook/onepagecheckout.tpl');
        } elseif (_PS_VERSION_ < 1.7) {
            $return = $this->display(__FILE__, 'views/templates/hook/checkout-15.tpl');
        }
        return $return;
    }

    /**
     * @param string $functionName
     *:
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function productPageSimulatorDisplay($functionName)
    {
        $productConfiguration = getenv('PMT_SIMULATOR_DISPLAY_POSITION');
        /** @var ProductCore $product */
        $product = new Product(Tools::getValue('id_product'));
        $amount = $product->getPublicPrice();
        $pmtPublicKey                = Configuration::get('pmt_public_key');
        $pmtSimulatorIsEnabled        = Configuration::get('pmt_simulator_is_enabled');
        $pmtIsEnabled                 = Configuration::get('pmt_is_enabled');
        $pmtSimulatorType             = getenv('PMT_SIMULATOR_DISPLAY_TYPE');
        $pmtSimulatorCSSSelector      = getenv('PMT_SIMULATOR_CSS_POSITION_SELECTOR');
        $pmtSimulatorPriceSelector    = getenv('PMT_SIMULATOR_CSS_PRICE_SELECTOR');
        $pmtSimulatorQuantitySelector = getenv('PMT_SIMULATOR_CSS_QUANTITY_SELECTOR');
        $pmtSimulatorQuotesStart      = getenv('PMT_SIMULATOR_START_INSTALLMENTS');
        $pmtSimulatorSkin             = getenv('PMT_SIMULATOR_DISPLAY_SKIN');
        $pmtSimulatorPosition         = getenv('PMT_SIMULATOR_DISPLAY_CSS_POSITION');
        $pmtDisplayMinAmount          = getenv('PMT_DISPLAY_MIN_AMOUNT');

        if ($functionName != $productConfiguration ||
            $amount <= 0 ||
            $amount < $pmtDisplayMinAmount ||
            !$pmtSimulatorType
        ) {
            return null;
        }

        $this->context->smarty->assign(array(
            'amount'                => $amount,
            'pmtPublicKey'          => $pmtPublicKey,
            'pmtCSSSelector'        => $pmtSimulatorCSSSelector,
            'pmtPriceSelector'      => $pmtSimulatorPriceSelector,
            'pmtQuantitySelector'   => $pmtSimulatorQuantitySelector,
            'pmtSimulatorIsEnabled' => $pmtSimulatorIsEnabled,
            'pmtIsEnabled'          => $pmtIsEnabled,
            'pmtSimulatorType'      => $pmtSimulatorType,
            'pmtSimulatorSkin'      => $pmtSimulatorSkin,
            'pmtSimulatorPosition'  => $pmtSimulatorPosition,
            'pmtQuotesStart'        => $pmtSimulatorQuotesStart,
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
