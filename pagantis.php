<?php
/**
 * This file is part of the official enCuotas module for PrestaShop.
 *
 * @author    Pagantis <integrations@pagantis.com>
 * @copyright 2019 Pagantis
 * @license   proprietary
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

define('_PS_PAGANTIS_DIR', _PS_MODULE_DIR_. '/pagantis');
define('PROMOTIONS_CATEGORY', 'pagantis-promotion-product');
define('PROMOTIONS_CATEGORY_NAME', 'enCuotas Promoted Product');

require _PS_PAGANTIS_DIR.'/vendor/autoload.php';

/**
 * Class Pagantis
 */
class Pagantis extends PaymentModule
{
    /**
     * @var string
     */
    public $url = 'https://pagantis.com';

    /**
     * @var bool
     */
    public $bootstrap = true;

    /** @var string $language */
    public $language;

    /**
     * Default module advanced configuration values
     *
     * @var array
     */
    public $defaultConfigs = array(
        'ENCUOTAS_TITLE' => 'Instant Financing',
        'ENCUOTAS_SIMULATOR_DISPLAY_TYPE' => 'sdk.simulator.types.SELECTABLE_TEXT_CUSTOM',
        'ENCUOTAS_SIMULATOR_DISPLAY_SKIN' => 'sdk.simulator.skins.BLUE',
        'ENCUOTAS_SIMULATOR_DISPLAY_POSITION' => 'hookDisplayProductButtons',
        'ENCUOTAS_SIMULATOR_START_INSTALLMENTS' => '3',
        'ENCUOTAS_SIMULATOR_CSS_POSITION_SELECTOR' => 'default',
        'ENCUOTAS_SIMULATOR_DISPLAY_CSS_POSITION' => 'sdk.simulator.positions.INNER',
        'ENCUOTAS_SIMULATOR_CSS_PRICE_SELECTOR' => 'default',
        'ENCUOTAS_SIMULATOR_CSS_QUANTITY_SELECTOR' => 'default',
        'ENCUOTAS_SIMULATOR_DISPLAY_MAX_AMOUNT' => '300',
        'ENCUOTAS_FORM_DISPLAY_TYPE' => '0',
        'ENCUOTAS_DISPLAY_MIN_AMOUNT' => '1',
        'ENCUOTAS_DISPLAY_MAX_AMOUNT' => '0',
        'ENCUOTAS_URL_OK' => '',
        'ENCUOTAS_URL_KO' => '',
        'ENCUOTAS_ALLOWED_COUNTRIES' => 'a:3:{i:0;s:2:"es";i:1;s:2:"it";i:2;s:2:"fr";}',
        'ENCUOTAS_PROMOTION_EXTRA' => 'Finance this product <span class="pg-no-interest">without interest!</span>',
        'ENCUOTAS_SIMULATOR_THOUSAND_SEPARATOR' => '.',
        'ENCUOTAS_SIMULATOR_DECIMAL_SEPARATOR' => ',',
    );
    /**
     * @var null $shippingAddress
     */
    protected $shippingAddress = null;
    /**
     * @var null $billingAddress
     */
    protected $billingAddress = null;

    /**
     * @var array $allowedCountries
     */
    protected $allowedCountries = array();

    /**
     * Pagantis constructor.
     *
     * Define the module main properties so that prestashop understands what are the module requirements
     * and how to manage the module.
     *
     */
    public function __construct()
    {
        $this->name = 'pagantis';
        $this->tab = 'payments_gateways';
        $this->version = '8.3.5';
        $this->author = 'enCuotas';
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->module_key = '2b9bc901b4d834bb7069e7ea6510438f';
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);
        $this->displayName = $this->l('enCuotas');
        $this->description = $this->l(
            'Instant, easy and effective financial tool for your customers'
        );

        $sql_file = dirname(__FILE__).'/sql/install.sql';
        $this->loadSQLFile($sql_file);

        $this->checkEnvVariables();

        $this->migrate();

        $this->checkHooks();

        $this->checkPromotionCategory();

        parent::__construct();

        $this->getUserLanguage();
    }

    /**
     * Configure the variables for Pagantis payment method.
     *
     * @return bool
     */
    public function install()
    {
        if (!extension_loaded('curl')) {
            $this->_errors[] =
                $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }
        if (!version_compare(phpversion(), '5.3.0', '>=')) {
            $this->_errors[] = $this->l('The PHP version bellow 5.3.0 is not supported');
            return false;
        }

        Configuration::updateValue('pagantis_is_enabled', 1);
        Configuration::updateValue('pagantis_simulator_is_enabled', 1);
        Configuration::updateValue('pagantis_public_key', '');
        Configuration::updateValue('pagantis_private_key', '');

        $return =  (parent::install()
            && $this->registerHook('displayShoppingCart')
            && $this->registerHook('paymentOptions')
            && $this->registerHook('displayRightColumn')
            && $this->registerHook('displayLeftColumn')
            && $this->registerHook('displayRightColumnProduct')
            && $this->registerHook('displayLeftColumnProduct')
            && $this->registerHook('displayProductButtons')
            && $this->registerHook('displayOrderConfirmation')
            && $this->registerHook('header')
        );

        if ($return && _PS_VERSION_ < "1.7") {
            $this->registerHook('payment');
        }

        return $return;
    }

    /**
     * Remove the production private api key and remove the files
     *
     * @return bool
     */
    public function uninstall()
    {
        Configuration::deleteByName('pagantis_public_key');
        Configuration::deleteByName('pagantis_private_key');

        return parent::uninstall();
    }

    /**
     * Migrate the configs of older versions < 7x to new configurations
     */
    public function migrate()
    {
        if (Configuration::get('ENCUOTAS_MIN_AMOUNT')) {
            Db::getInstance()->update(
                'pagantis_config',
                array('value' => Configuration::get('ENCUOTAS_MIN_AMOUNT')),
                'config = \'ENCUOTAS_DISPLAY_MIN_AMOUNT\''
            );
            Configuration::updateValue('ENCUOTAS_MIN_AMOUNT', false);
            Configuration::updateValue('pagantis_is_enabled', 1);
            Configuration::updateValue('pagantis_simulator_is_enabled', 1);

            // migrating pk/tk from previous version
            if (Configuration::get('pagantis_public_key') === false
                && Configuration::get('ENCUOTAS_PUBLIC_KEY_PROD')
            ) {
                Configuration::updateValue('pagantis_public_key', Configuration::get('ENCUOTAS_PUBLIC_KEY_PROD'));
                Configuration::updateValue('ENCUOTAS_PUBLIC_KEY_PROD', false);
            } elseif (Configuration::get('pagantis_public_key') === false
                && Configuration::get('ENCUOTAS_PUBLIC_KEY_TEST')
            ) {
                Configuration::updateValue('pagantis_public_key', Configuration::get('ENCUOTAS_PUBLIC_KEY_TEST'));
                Configuration::updateValue('ENCUOTAS_PUBLIC_KEY_TEST', false);
            }

            if (Configuration::get('pagantis_private_key') === false
                && Configuration::get('ENCUOTAS_PRIVATE_KEY_PROD')
            ) {
                Configuration::updateValue('pagantis_private_key', Configuration::get('ENCUOTAS_PRIVATE_KEY_PROD'));
                Configuration::updateValue('ENCUOTAS_PRIVATE_KEY_PROD', false);
            } elseif (Configuration::get('pagantis_private_key') === false
                && Configuration::get('ENCUOTAS_PRIVATE_KEY_TEST')
            ) {
                Configuration::updateValue('pagantis_private_key', Configuration::get('ENCUOTAS_PRIVATE_KEY_TEST'));
                Configuration::updateValue('ENCUOTAS_PRIVATE_KEY_TEST', false);
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

    /**
     * @throws PrestaShopDatabaseException
     */
    public function checkEnvVariables()
    {
        $sql_content = 'select * from ' . _DB_PREFIX_. 'pagantis_config';
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
            Db::getInstance()->insert('pagantis_config', $data);
        }
    }

    /**
     * @param $sql_file
     * @return bool
     */
    public function loadSQLFile($sql_file)
    {
        try {
            $tableName = _DB_PREFIX_.'pagantis_order';
            $sql = ("SHOW TABLES LIKE '$tableName'");
            $results = Db::getInstance()->ExecuteS($sql);
            if (is_array($results) && count($results) === 1) {
                $query = "select COLUMN_TYPE FROM information_schema.COLUMNS where 
                          TABLE_NAME='$tableName' AND COLUMN_NAME='ps_order_id'";
                $results = $results = Db::getInstance()->ExecuteS($query);
                if (is_array($results) && count($results) === 0) {
                    $sql = "ALTER TABLE $tableName ADD COLUMN ps_order_id VARCHAR(60) AFTER order_id";
                    Db::getInstance()->Execute($sql);
                }
                return false;
            }
        } catch (\Exception $exception) {
            // do nothing
        }

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
        $cart                      = $this->context->cart;
        $currency                  = new Currency($cart->id_currency);
        $availableCurrencies       = array('EUR');
        $pagantisDisplayMinAmount  = Pagantis::getExtraConfig('ENCUOTAS_DISPLAY_MIN_AMOUNT');
        $pagantisDisplayMaxAmount  = Pagantis::getExtraConfig('ENCUOTAS_DISPLAY_MAX_AMOUNT');
        $pagantisPublicKey         = Configuration::get('pagantis_public_key');
        $pagantisPrivateKey        = Configuration::get('pagantis_private_key');
        $this->allowedCountries    = unserialize(Pagantis::getExtraConfig('ENCUOTAS_ALLOWED_COUNTRIES'));
        $this->getUserLanguage();
        return (
            $cart->getOrderTotal() >= $pagantisDisplayMinAmount &&
            ($cart->getOrderTotal() <= $pagantisDisplayMaxAmount || $pagantisDisplayMaxAmount == '0') &&
            in_array($currency->iso_code, $availableCurrencies) &&
            in_array(Tools::strtolower($this->language), $this->allowedCountries) &&
            $pagantisPublicKey &&
            $pagantisPrivateKey
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
            'pagantis_button' => '#pagantis_payment_button',
            'pagantis_currency_iso' => $currency->iso_code,
            'pagantis_cart_total' => $cart->getOrderTotal(),
        );
    }

    /**
     * Header hook
     */
    public function hookHeader()
    {
        $url = 'https://cdn.pagantis.com/js/encuotas-v2/sdk.js?ver=5.3.2';
        if (_PS_VERSION_ >= "1.7") {
            $this->context->controller->registerJavascript(
                sha1(mt_rand(1, 90000)),
                $url,
                array('server' => 'remote')
            );
        } else {
            $this->context->controller->addJS($url);
        }
        $this->context->controller->addJS($this->getPathUri(). 'views/js/simulator.js');
    }

    /**
     * @return array
     * @throws Exception
     */
    public function hookPaymentOptions()
    {
        /** @var Cart $cart */
        $cart = $this->context->cart;
        $this->shippingAddress = new Address($cart->id_address_delivery);
        $this->billingAddress = new Address($cart->id_address_invoice);
        if (!$this->isPaymentMethodAvailable()) {
            return array();
        }

        /** @var Cart $cart */
        $cart                               = $this->context->cart;
        $orderTotal                         = $cart->getOrderTotal();
        $promotedAmount                     = 0;
        $link                               = $this->context->link;
        $pagantisPublicKey                  = Configuration::get('pagantis_public_key');
        $pagantisSimulatorIsEnabled         = Configuration::get('pagantis_simulator_is_enabled');
        $pagantisIsEnabled                  = Configuration::get('pagantis_is_enabled');
        $pagantisSimulatorType              = Pagantis::getExtraConfig('ENCUOTAS_SIMULATOR_DISPLAY_TYPE');
        $pagantisSimulatorCSSSelector       = Pagantis::getExtraConfig('ENCUOTAS_SIMULATOR_CSS_POSITION_SELECTOR');
        $pagantisSimulatorPriceSelector     = Pagantis::getExtraConfig('ENCUOTAS_SIMULATOR_CSS_PRICE_SELECTOR');
        $pagantisSimulatorQuotesStart       = Pagantis::getExtraConfig('ENCUOTAS_SIMULATOR_START_INSTALLMENTS');
        $pagantisSimulatorSkin              = Pagantis::getExtraConfig('ENCUOTAS_SIMULATOR_DISPLAY_SKIN');
        $pagantisSimulatorPosition          = Pagantis::getExtraConfig('ENCUOTAS_SIMULATOR_DISPLAY_CSS_POSITION');
        $pagantisTitle                      = $this->l(Pagantis::getExtraConfig('ENCUOTAS_TITLE'));
        $pagantisSimulatorThousandSeparator = Pagantis::getExtraConfig('ENCUOTAS_SIMULATOR_THOUSAND_SEPARATOR');
        $pagantisSimulatorDecimalSeparator  = Pagantis::getExtraConfig('ENCUOTAS_SIMULATOR_DECIMAL_SEPARATOR');

        $items = $cart->getProducts(true);
        foreach ($items as $item) {
            $itemCategories = ProductCore::getProductCategoriesFull($item['id_product']);
            if (in_array(PROMOTIONS_CATEGORY_NAME, $this->arrayColumn($itemCategories, 'name')) !== false) {
                $productId = $item['id_product'];
                $promotedAmount += Product::getPriceStatic($productId);
            }
        }

        $this->context->smarty->assign($this->getButtonTemplateVars($cart));
        $this->context->smarty->assign(array(
            'amount'                             => $orderTotal,
            'locale'                             => $this->language,
            'country'                            => $this->language,
            'pagantisPublicKey'                  => $pagantisPublicKey,
            'pagantisCSSSelector'                => $pagantisSimulatorCSSSelector,
            'pagantisPriceSelector'              => $pagantisSimulatorPriceSelector,
            'pagantisQuotesStart'                => $pagantisSimulatorQuotesStart,
            'pagantisSimulatorIsEnabled'         => $pagantisSimulatorIsEnabled,
            'pagantisSimulatorType'              => $pagantisSimulatorType,
            'pagantisSimulatorSkin'              => $pagantisSimulatorSkin,
            'pagantisSimulatorPosition'          => $pagantisSimulatorPosition,
            'pagantisIsEnabled'                  => $pagantisIsEnabled,
            'pagantisTitle'                      => $pagantisTitle,
            'paymentUrl'                         => $link->getModuleLink('pagantis', 'payment'),
            'pagantisSimulatorThousandSeparator' => $pagantisSimulatorThousandSeparator,
            'pagantisSimulatorDecimalSeparator'  => $pagantisSimulatorDecimalSeparator,
            'promotedAmount'                     => $promotedAmount,
            'ps_version'                         => str_replace('.', '-', Tools::substr(_PS_VERSION_, 0, 3)),
        ));

        $logo = $this->getPathUri() . '/logo.png';
        $paymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $paymentOption
            ->setCallToActionText($pagantisTitle)
            ->setAction($link->getModuleLink('pagantis', 'payment'))
            ->setLogo($logo)
            ->setModuleName(__CLASS__)
        ;

        $paymentOption->setAdditionalInformation(
            $this->fetch('module:pagantis/views/templates/hook/checkout.tpl')
        );

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
                        'name' => 'pagantis_is_enabled',
                        'type' =>  (version_compare(_PS_VERSION_, '1.6')<0) ?'radio' :'switch',
                        'label' => $this->l('Module is enabled'),
                        'prefix' => '<i class="icon icon-key"></i>',
                        'class' => 't',
                        'required' => true,
                        'values'=> array(
                            array(
                                'id' => 'pagantis_is_enabled_true',
                                'value' => 1,
                                'label' => $this->l('Yes', get_class($this), null, false),
                            ),
                            array(
                                'id' => 'pagantis_is_enabled_false',
                                'value' => 0,
                                'label' => $this->l('No', get_class($this), null, false),
                            ),
                        )
                    ),
                    array(
                        'name' => 'pagantis_public_key',
                        'suffix' => $this->l('ex: pk_fd53cd467ba49022e4gf215e'),
                        'type' => 'text',
                        'size' => 60,
                        'label' => $this->l('Public Key'),
                        'prefix' => '<i class="icon icon-key"></i>',
                        'col' => 6,
                        'required' => true,
                    ),
                    array(
                        'name' => 'pagantis_private_key',
                        'suffix' => $this->l('ex: 21e5723a97459f6a'),
                        'type' => 'text',
                        'size' => 60,
                        'label' => $this->l('Secret Key'),
                        'prefix' => '<i class="icon icon-key"></i>',
                        'col' => 6,
                        'required' => true,
                    ),
                    array(
                        'name' => 'pagantis_simulator_is_enabled',
                        'type' => (version_compare(_PS_VERSION_, '1.6')<0) ?'radio' :'switch',
                        'label' => $this->l('Simulator is enabled'),
                        'prefix' => '<i class="icon icon-key"></i>',
                        'class' => 't',
                        'required' => true,
                        'values'=> array(
                            array(
                                'id' => 'pagantis_simulator_is_enabled_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ),
                            array(
                                'id' => 'pagantis_simulator_is_enabled_off',
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

        $helper->fields_value['pagantis_url_ok'] = Configuration::get('pagantis_url_ok');

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Function to update the variables of enCuotas Module in the backoffice of prestashop
     *
     * @return string
     * @throws SmartyException
     */
    public function getContent()
    {
        $error = '';
        $message = '';
        $settings = array();
        $settings['pagantis_public_key'] = Configuration::get('pagantis_public_key');
        $settings['pagantis_private_key'] = Configuration::get('pagantis_private_key');
        $settingsKeys = array(
            'pagantis_is_enabled',
            'pagantis_public_key',
            'pagantis_private_key',
            'pagantis_simulator_is_enabled',
        );

        //Different Behavior depending on 1.6 or earlier
        if (Tools::isSubmit('submit'.$this->name)) {
            foreach ($settingsKeys as $key) {
                switch ($key) {
                    case 'pagantis_public_key':
                        $value = Tools::getValue($key);
                        if (!$value) {
                            $error = $this->l('Please add a enCuotas API Public Key');
                            break;
                        }
                        Configuration::updateValue($key, $value);
                        $settings[$key] = $value;
                        break;
                    case 'pagantis_private_key':
                        $value = Tools::getValue($key);
                        if (!$value) {
                            $error = $this->l('Please add a enCuotas API Private Key');
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

        $logo = $this->getPathUri() . '/logo.png';
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
        /** @var Cart $cart */
        $cart = $this->context->cart;
        $this->shippingAddress = new Address($cart->id_address_delivery);
        $this->billingAddress = new Address($cart->id_address_invoice);
        if (!$this->isPaymentMethodAvailable()) {
            return false;
        }

        /** @var Cart $cart */

        $cart                               = $params['cart'];
        $orderTotal                         = $cart->getOrderTotal();
        $promotedAmount                     = 0;
        $link                               = $this->context->link;
        $pagantisPublicKey                  = Configuration::get('pagantis_public_key');
        $pagantisSimulatorIsEnabled         = Configuration::get('pagantis_simulator_is_enabled');
        $pagantisIsEnabled                  = Configuration::get('pagantis_is_enabled');
        $pagantisSimulatorType              = Pagantis::getExtraConfig('ENCUOTAS_SIMULATOR_DISPLAY_TYPE');
        $pagantisSimulatorCSSSelector       = Pagantis::getExtraConfig('ENCUOTAS_SIMULATOR_CSS_POSITION_SELECTOR');
        $pagantisSimulatorPriceSelector     = Pagantis::getExtraConfig('ENCUOTAS_SIMULATOR_CSS_PRICE_SELECTOR');
        $pagantisSimulatorQuotesStart       = Pagantis::getExtraConfig('ENCUOTAS_SIMULATOR_START_INSTALLMENTS');
        $pagantisSimulatorSkin              = Pagantis::getExtraConfig('ENCUOTAS_SIMULATOR_DISPLAY_SKIN');
        $pagantisSimulatorPosition          = Pagantis::getExtraConfig('ENCUOTAS_SIMULATOR_DISPLAY_CSS_POSITION');
        $pagantisTitle                      = $this->l(Pagantis::getExtraConfig('ENCUOTAS_TITLE'));
        $pagantisSimulatorThousandSeparator = Pagantis::getExtraConfig('ENCUOTAS_SIMULATOR_THOUSAND_SEPARATOR');
        $pagantisSimulatorDecimalSeparator  = Pagantis::getExtraConfig('ENCUOTAS_SIMULATOR_DECIMAL_SEPARATOR');

        $items = $cart->getProducts(true);
        foreach ($items as $item) {
            $itemCategories = ProductCore::getProductCategoriesFull($item['id_product']);
            if (in_array(PROMOTIONS_CATEGORY_NAME, $this->arrayColumn($itemCategories, 'name')) !== false) {
                $productId = $item['id_product'];
                $promotedAmount += Product::getPriceStatic($productId);
            }
        }

        $this->context->smarty->assign($this->getButtonTemplateVars($cart));
        $this->context->smarty->assign(array(
            'amount'                             => $orderTotal,
            'promotedAmount'                     => $promotedAmount,
            'locale'                             => $this->language,
            'country'                            => $this->language,
            'logo'                               => $this->getPathUri() . '/logo.png',
            'pagantisPublicKey'                  => $pagantisPublicKey,
            'pagantisCSSSelector'                => $pagantisSimulatorCSSSelector,
            'pagantisPriceSelector'              => $pagantisSimulatorPriceSelector,
            'pagantisQuotesStart'                => $pagantisSimulatorQuotesStart,
            'pagantisSimulatorIsEnabled'         => $pagantisSimulatorIsEnabled,
            'pagantisSimulatorType'              => $pagantisSimulatorType,
            'pagantisSimulatorSkin'              => $pagantisSimulatorSkin,
            'pagantisSimulatorPosition'          => $pagantisSimulatorPosition,
            'pagantisIsEnabled'                  => $pagantisIsEnabled,
            'pagantisTitle'                      => $pagantisTitle,
            'pagantisSimulatorThousandSeparator' => $pagantisSimulatorThousandSeparator,
            'pagantisSimulatorDecimalSeparator'  => $pagantisSimulatorDecimalSeparator,
            'paymentUrl'                         => $link->getModuleLink('pagantis', 'payment'),
            'ps_version'                         => str_replace('.', '-', Tools::substr(_PS_VERSION_, 0, 3)),
        ));

        $supercheckout_enabled = Module::isEnabled('supercheckout');
        $onepagecheckoutps_enabled = Module::isEnabled('onepagecheckoutps');
        $onepagecheckout_enabled = Module::isEnabled('onepagecheckout');

        $return = true;
        if ($supercheckout_enabled || $onepagecheckout_enabled || $onepagecheckoutps_enabled) {
            $this->checkLogoExists();
            $return = $this->display(__FILE__, 'views/templates/hook/onepagecheckout.tpl');
        } elseif (_PS_VERSION_ < 1.7) {
            $return = $this->display(__FILE__, 'views/templates/hook/checkout.tpl');
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
        $productConfiguration = Pagantis::getExtraConfig('ENCUOTAS_SIMULATOR_DISPLAY_POSITION');
        $productId = Tools::getValue('id_product');
        if (!$productId) {
            return false;
        }
        //Resolves bug of reference passtrow
        $amount = Product::getPriceStatic($productId);

        $itemCategoriesNames = $this->arrayColumn(Product::getProductCategoriesFull($productId), 'name');
        $isPromotedProduct = in_array(PROMOTIONS_CATEGORY_NAME, $itemCategoriesNames);

        $pagantisPublicKey                  = Configuration::get('pagantis_public_key');
        $pagantisSimulatorIsEnabled         = Configuration::get('pagantis_simulator_is_enabled');
        $pagantisIsEnabled                  = Configuration::get('pagantis_is_enabled');
        $pagantisSimulatorType              = Pagantis::getExtraConfig('ENCUOTAS_SIMULATOR_DISPLAY_TYPE');
        $pagantisSimulatorCSSSelector       = Pagantis::getExtraConfig('ENCUOTAS_SIMULATOR_CSS_POSITION_SELECTOR');
        $pagantisSimulatorPriceSelector     = Pagantis::getExtraConfig('ENCUOTAS_SIMULATOR_CSS_PRICE_SELECTOR');
        $pagantisSimulatorQuantitySelector  = Pagantis::getExtraConfig('ENCUOTAS_SIMULATOR_CSS_QUANTITY_SELECTOR');
        $pagantisSimulatorQuotesStart       = Pagantis::getExtraConfig('ENCUOTAS_SIMULATOR_START_INSTALLMENTS');
        $pagantisSimulatorSkin              = Pagantis::getExtraConfig('ENCUOTAS_SIMULATOR_DISPLAY_SKIN');
        $pagantisSimulatorPosition          = Pagantis::getExtraConfig('ENCUOTAS_SIMULATOR_DISPLAY_CSS_POSITION');
        $pagantisDisplayMinAmount           = Pagantis::getExtraConfig('ENCUOTAS_DISPLAY_MIN_AMOUNT');
        $pagantisPromotionExtra             = Pagantis::getExtraConfig('ENCUOTAS_PROMOTION_EXTRA');
        $pagantisSimulatorThousandSeparator = Pagantis::getExtraConfig('ENCUOTAS_SIMULATOR_THOUSAND_SEPARATOR');
        $pagantisSimulatorDecimalSeparator  = Pagantis::getExtraConfig('ENCUOTAS_SIMULATOR_DECIMAL_SEPARATOR');
        $pagantisSimulatorMaxAmount         = Pagantis::getExtraConfig('ENCUOTAS_SIMULATOR_DISPLAY_MAX_AMOUNT');
        $allowedCountries                   = unserialize(Pagantis::getExtraConfig('ENCUOTAS_ALLOWED_COUNTRIES'));

        if ($functionName != $productConfiguration ||
            $amount <= 0 ||
            $amount <= $pagantisDisplayMinAmount ||
            ($amount >= $pagantisSimulatorMaxAmount && $pagantisSimulatorMaxAmount != '0') ||
            !$pagantisSimulatorType ||
            !in_array(Tools::strtolower($this->language), $allowedCountries)
        ) {
            return null;
        }

        $this->context->smarty->assign(array(
            'amount'                             => $amount,
            'locale'                             => $this->language,
            'country'                            => $this->language,
            'pagantisPublicKey'                  => $pagantisPublicKey,
            'pagantisCSSSelector'                => $pagantisSimulatorCSSSelector,
            'pagantisPriceSelector'              => $pagantisSimulatorPriceSelector,
            'pagantisQuantitySelector'           => $pagantisSimulatorQuantitySelector,
            'pagantisSimulatorIsEnabled'         => $pagantisSimulatorIsEnabled,
            'pagantisIsEnabled'                  => $pagantisIsEnabled,
            'pagantisSimulatorType'              => $pagantisSimulatorType,
            'pagantisSimulatorSkin'              => $pagantisSimulatorSkin,
            'pagantisSimulatorPosition'          => $pagantisSimulatorPosition,
            'pagantisQuotesStart'                => $pagantisSimulatorQuotesStart,
            'isPromotedProduct'                  => $isPromotedProduct,
            'pagantisPromotionExtra'             => Tools::htmlentitiesDecodeUTF8($this->l($pagantisPromotionExtra)),
            'pagantisSimulatorThousandSeparator' => $pagantisSimulatorThousandSeparator,
            'pagantisSimulatorDecimalSeparator'  => $pagantisSimulatorDecimalSeparator,
            'ps_version'                         => str_replace('.', '-', Tools::substr(_PS_VERSION_, 0, 3)),
            'pagantisSimPreposition'             => $this->l('or'),
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
     * checkPromotionCategory
     */
    public function checkPromotionCategory()
    {
        $categories = $this->arrayColumn(Category::getCategories(null, false, false), 'name');
        if (!in_array(PROMOTIONS_CATEGORY_NAME, $categories)) {
            /** @var CategoryCore $category */
            $category = new Category();
            $categoryArray = array((int)Configuration::get('PS_LANG_DEFAULT')=> PROMOTIONS_CATEGORY );
            $category->is_root_category = false;
            $category->link_rewrite = $categoryArray;
            $category->meta_description = $categoryArray;
            $category->meta_keywords = $categoryArray;
            $category->meta_title = $categoryArray;
            $category->name = array((int)Configuration::get('PS_LANG_DEFAULT')=> PROMOTIONS_CATEGORY_NAME);
            $category->id_parent = Configuration::get('PS_HOME_CATEGORY');
            $category->active=0;
            $description = 'Pagantis: Products with this category have free financing assumed by the merchant. ' .
                'Use it to promote your products or brands.';
            $category->description = $this->l($description);
            $category->save();
        }
    }


    public static function getExtraConfig($config = null, $default = '')
    {
        if (is_null($config)) {
            return '';
        }

        $sql = 'SELECT value FROM '._DB_PREFIX_.'pagantis_config where config = \'' . pSQL($config) . '\' limit 1';
        if ($results = Db::getInstance()->ExecuteS($sql)) {
            if (is_array($results) && count($results) === 1 && isset($results[0]['value'])) {
                return $results[0]['value'];
            }
        }

        return $default;
    }

    /**
     * Check logo exists in OPC module
     */
    public function checkLogoExists()
    {
        $logoPg = _PS_MODULE_DIR_ . '/onepagecheckoutps/views/img/payments/pagantis.png';
        if (!file_exists($logoPg) && is_dir(_PS_MODULE_DIR_ . '/onepagecheckoutps/views/img/payments')) {
            copy(
                _PS_PAGANTIS_DIR . '/logo.png',
                $logoPg
            );
        }
    }

    /**
     * Get user language
     */
    private function getUserLanguage()
    {
        $lang = Language::getLanguage($this->context->language->id);
        $langArray = explode("-", $lang['language_code']);
        if (count($langArray) != 2 && isset($lang['locale'])) {
            $langArray = explode("-", $lang['locale']);
        }
        $this->language = Tools::strtoupper($langArray[count($langArray)-1]);
        // Prevent null language detection
        if (in_array(Tools::strtolower($this->language), $this->allowedCountries)) {
            return;
        }
        if ($this->shippingAddress) {
            $this->language = Country::getIsoById($this->shippingAddress->id_country);
            if (in_array(Tools::strtolower($this->language), $this->allowedCountries)) {
                return;
            }
        }
        if ($this->billingAddress) {
            $this->language = Country::getIsoById($this->billingAddress->id_country);
            if (in_array(Tools::strtolower($this->language), $this->allowedCountries)) {
                return;
            }
        }
        return $this->language;
    }

    /**
     * @param array $input
     * @param       $columnKey
     * @param null  $indexKey
     *
     * @return array|bool
     */
    private function arrayColumn(array $input, $columnKey, $indexKey = null)
    {
        $array = array();
        foreach ($input as $value) {
            if (!array_key_exists($columnKey, $value)) {
                trigger_error("Key \"$columnKey\" does not exist in array");
                return false;
            }
            if (is_null($indexKey)) {
                $array[] = $value[$columnKey];
            } else {
                if (!array_key_exists($indexKey, $value)) {
                    trigger_error("Key \"$indexKey\" does not exist in array");
                    return false;
                }
                if (!is_scalar($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not contain scalar value");
                    return false;
                }
                $array[$value[$indexKey]] = $value[$columnKey];
            }
        }
        return $array;
    }
}
