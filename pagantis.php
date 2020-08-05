<?php
/**
 * This file is part of the official Pagantis module for PrestaShop.
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
define('PROMOTIONS_CATEGORY_NAME', 'Pagantis Promoted Product');

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
        'URL_OK' => '',
        'URL_KO' => '',
        'ALLOWED_COUNTRIES' => 'a:3:{i:0;s:2:"es";i:1;s:2:"it";i:2;s:2:"fr";}',
        'PRODUCTS' => 'P4X,PAGANTIS',
        'P4X' => '{
            "CODE": "P4X",
            "TITLE": "Pay in 4 installments, without cost",
            "SIMULATOR_TITLE": "up to 4 payments of",
            "SIMULATOR_SUBTITLE": "without cost with",
            "SIMULATOR_DISPLAY_TYPE": "p4x",
            "SIMULATOR_DISPLAY_IMAGE": "https://cdn.digitalorigin.com/assets/master/logos/pg-favicon.png",
            "SIMULATOR_DISPLAY_TYPE_CHECKOUT": "sdk.simulator.types.CHECKOUT_PAGE",
            "SIMULATOR_START_INSTALLMENTS": "4",
            "SIMULATOR_CSS_PRICE_SELECTOR": "default",
            "SIMULATOR_CSS_QUANTITY_SELECTOR": "default",
            "SIMULATOR_CSS_PRODUCT_PAGE_STYLES": "",
            "SIMULATOR_CSS_CHECKOUT_PAGE_STYLES": "",
            "SIMULATOR_DISPLAY_MAX_AMOUNT": "800",
            "FORM_DISPLAY_TYPE" : "0",
            "DISPLAY_MIN_AMOUNT": "0",
            "DISPLAY_MAX_AMOUNT": "800",
            "SIMULATOR_THOUSAND_SEPARATOR": ".",
            "SIMULATOR_DECIMAL_SEPARATOR": ","
            }',
        'PAGANTIS' => '{
            "CODE": "PAGANTIS",
            "TITLE": "Instant Financing",
            "SIMULATOR_TITLE": "Instant Financing",
            "SIMULATOR_SUBTITLE": "",
            "SIMULATOR_DISPLAY_TYPE": "sdk.simulator.types.PRODUCT_PAGE",
            "SIMULATOR_DISPLAY_TYPE_CHECKOUT": "sdk.simulator.types.CHECKOUT_PAGE",
            "SIMULATOR_DISPLAY_SKIN": "sdk.simulator.skins.BLUE",
            "SIMULATOR_START_INSTALLMENTS": "3",
            "SIMULATOR_CSS_POSITION_SELECTOR": "default",
            "SIMULATOR_DISPLAY_CSS_POSITION": "sdk.simulator.positions.INNER",
            "SIMULATOR_CSS_PRICE_SELECTOR": "default",
            "SIMULATOR_CSS_QUANTITY_SELECTOR": "default",
            "SIMULATOR_CSS_PRODUCT_PAGE_STYLES": "",
            "SIMULATOR_CSS_CHECKOUT_PAGE_STYLES": "",   
            "SIMULATOR_DISPLAY_MAX_AMOUNT": "1500",
            "FORM_DISPLAY_TYPE" : "0",
            "DISPLAY_MIN_AMOUNT": "0",
            "DISPLAY_MAX_AMOUNT": "1500",
            "PROMOTION_EXTRA": "Finance this product <span class=pg-no-interest>without interest!</span>",
            "SIMULATOR_THOUSAND_SEPARATOR": ".",
            "SIMULATOR_DECIMAL_SEPARATOR": ","
            }',
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
        $this->version = '8.6.4';
        $this->author = 'Pagantis';
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->module_key = '2b9bc901b4d834bb7069e7ea6510438f';
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);
        $this->displayName = $this->l('Pagantis');
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

        $products = explode(',', Pagantis::getExtraConfig('PRODUCTS', null));
        foreach ($products as $product) {
            $code = Tools::strtolower(Pagantis::getExtraConfig('CODE', $product));
            if ($code === 'p4x') {
                Configuration::updateValue($code . '_simulator_is_enabled', 1);
            }
            Configuration::updateValue($code . '_is_enabled', 0);
            Configuration::updateValue($code . '_public_key', '');
            Configuration::updateValue($code . '_private_key', '');
        }

        $return =  (parent::install()
            && $this->registerHook('displayShoppingCart')
            && $this->registerHook('paymentOptions')
            && $this->registerHook('displayProductButtons')
            && $this->registerHook('displayProductPriceBlock')
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
        Configuration::deleteByName('4x_public_key');
        Configuration::deleteByName('12x_public_key');
        Configuration::deleteByName('4x_public_key');
        Configuration::deleteByName('12x_public_key');

        return parent::uninstall();
    }

    /**
     * Migrate the configs of simple 8x module to multiproduct
     */
    public function migrate()
    {
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

            $sql_content = 'select * from ' . _DB_PREFIX_. 'hook_module where 
            id_module = \'' . Module::getModuleIdByName($this->name) . '\' and 
            id_shop = \'' . Shop::getContextShopID() . '\' and 
            id_hook = \'' . Hook::getIdByName('displayProductPriceBlock') . '\'';
            $hook_exists = Db::getInstance()->ExecuteS($sql_content);
            if (empty($hook_exists)) {
                $sql_insert = 'insert into ' . _DB_PREFIX_.  'hook_module 
            (id_module, id_shop, id_hook, position)
            values
            (\''. Module::getModuleIdByName($this->name) . '\',
            \''. Shop::getContextShopID() . '\',
            \''. Hook::getIdByName('displayProductPriceBlock') . '\',
            160)';
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
            $sql = "show tables like '"   . $tableName . "'";
            $data = Db::getInstance()->ExecuteS($sql);
            if (count($data) > 0) {
                $sql = "desc "   . $tableName;
                $data = Db::getInstance()->ExecuteS($sql);
                if (count($data) == 2) {
                    $sql = "ALTER TABLE $tableName ADD COLUMN ps_order_id VARCHAR(60) AFTER order_id";
                    Db::getInstance()->Execute($sql);
                }
            }
            $tableName = _DB_PREFIX_.'pagantis_config';
            $sql = "show tables like '"   . $tableName . "'";
            $data = Db::getInstance()->ExecuteS($sql);
            if (count($data) > 0) {
                $sql = "desc "   . $tableName;
                $data = Db::getInstance()->ExecuteS($sql);
                if (count($data) === 3 && $data[2]['Type'] !== 'varchar(5000)') {
                    $sql = "ALTER TABLE $tableName MODIFY `value` VARCHAR(5000)";
                    Db::getInstance()->Execute($sql);
                }
                // return because pagantis tables exisit so load the sqlfile is not needed
                return true;
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
     * @param string $product
     * @return bool
     */
    public function isPaymentMethodAvailable($product = 'p4x')
    {
        $configs = json_decode(Pagantis::getExtraConfig($product, null), true);
        $cart                      = $this->context->cart;
        $currency                  = new Currency($cart->id_currency);
        $availableCurrencies       = array('EUR');
        $pagantisDisplayMinAmount  = $configs['DISPLAY_MIN_AMOUNT'];
        $pagantisDisplayMaxAmount  = $configs['DISPLAY_MAX_AMOUNT'];
        $pagantisPublicKey         = Configuration::get(Tools::strtolower($configs['CODE']) . '_public_key');
        $pagantisPrivateKey        = Configuration::get(Tools::strtolower($configs['CODE']) . '_private_key');
        $this->allowedCountries    = unserialize(Pagantis::getExtraConfig('ALLOWED_COUNTRIES', null));
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
            'button' => '#payment_button',
            'currency_iso' => $currency->iso_code,
            'cart_total' => $cart->getOrderTotal(),
        );
    }

    /**
     * Header hook
     */
    public function hookHeader()
    {

        $url = 'https://cdn.pagantis.com/js/pg-v2/sdk.js';
        if (_PS_VERSION_ >= "1.7") {
            $this->context->controller->registerJavascript(
                sha1(mt_rand(1, 90000)),
                $url,
                array('server' => 'remote')
            );
        } else {
            $this->context->controller->addJS($url);
        }
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

        $orderTotal = $cart->getOrderTotal();
        $promotedAmount = 0;
        $link = $this->context->link;
        $items = $cart->getProducts(true);
        foreach ($items as $item) {
            $itemCategories = ProductCore::getProductCategoriesFull($item['id_product']);
            if (in_array(PROMOTIONS_CATEGORY_NAME, $this->arrayColumn($itemCategories, 'name')) !== false) {
                $productId = $item['id_product'];
                $promotedAmount += Product::getPriceStatic($productId);
            }
        }

        $return = array();
        $this->context->smarty->assign($this->getButtonTemplateVars($cart));
        $products = explode(',', Pagantis::getExtraConfig('PRODUCTS', null));
        $templateConfigs = array();
        foreach ($products as $product) {
            if ($this->isPaymentMethodAvailable($product)) {
                $productConfigs = Pagantis::getExtraConfig($product, null);
                $productConfigs = json_decode($productConfigs, true);
                $publicKey = Configuration::get(Tools::strtolower($productConfigs['CODE']) . '_public_key');
                $simulatorIsEnabled = Configuration::get(Tools::strtolower($productConfigs['CODE']) . '_simulator_is_enabled');
                $isEnabled = Configuration::get(Tools::strtolower($productConfigs['CODE']) . '_is_enabled');

                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_TITLE'] = $this->l($productConfigs['TITLE']);
                unset($productConfigs['TITLE']);
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_AMOUNT'] = $orderTotal;
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_PROMOTED_AMOUNT'] = $promotedAmount;
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_LOCALE'] = $this->language;
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_COUNTRY'] = $this->language;
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_PUBLIC_KEY'] = $publicKey;
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_SIMULATOR_IS_ENABLED'] = $simulatorIsEnabled;
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_IS_ENABLED'] = $isEnabled;
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_LOGO'] = 'https://cdn.digitalorigin.com/assets/master/logos/pg-favicon.png';
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_PAYMENT_URL'] = $link->getModuleLink('pagantis', 'payment') . '&product=' . Tools::strtolower($productConfigs['CODE']);
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_PS_VERSION'] = str_replace('.', '-', Tools::substr(_PS_VERSION_, 0, 3));

                foreach ($productConfigs as $productConfigKey => $productConfigValue) {
                    $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . "_" . $productConfigKey] = $productConfigValue;
                }
                $this->context->smarty->assign($templateConfigs);

                $paymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
                $uri = $link->getModuleLink('pagantis', 'payment');
                if (strpos($uri, '?') !== false) {
                    $uri .= '&product=' . Tools::strtolower($productConfigs['CODE']);
                } else {
                    $uri .= '?product=' . Tools::strtolower($productConfigs['CODE']);
                }
                $paymentOption
                    ->setCallToActionText($templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_TITLE'])
                    ->setAction($uri)
                    ->setLogo($templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_LOGO'])
                    ->setModuleName(__CLASS__)
                    ->setAdditionalInformation(
                        $this->fetch('module:pagantis/views/templates/hook/checkout-' . Tools::strtolower($productConfigs['CODE']) . '.tpl')
                    )
                ;
                $return[] = $paymentOption;
            }
        }
        if (count($return) === 0) {
            return false;
        }
        return $return;
    }

    /**
     * Get the form for editing the BackOffice options of the module
     *
     * @return array
            */
    private function getConfigForm()
    {
        $products = explode(',', Pagantis::getExtraConfig('PRODUCTS', null));
        $inputs = array();
        foreach ($products as $product) {
            $code = Tools::strtolower(Pagantis::getExtraConfig('CODE', $product));
            $inputs[] = array(
                'name' => $code .'_is_enabled',
                'type' =>  (version_compare(_PS_VERSION_, '1.6')<0) ?'radio' :'switch',
                'label' => $this->l('Module is enabled ' . $code),
                'prefix' => '<i class="icon icon-key"></i>',
                'class' => 't',
                'required' => true,
                'values'=> array(
                    array(
                        'id' => $code .'_is_enabled_true',
                        'value' => 1,
                        'label' => $this->l('Yes', get_class($this), null, false),
                    ),
                    array(
                        'id' => $code . '_is_enabled_false',
                        'value' => 0,
                        'label' => $this->l('No', get_class($this), null, false),
                    ),
                )
            );
            $inputs[] = array(
                'name' => $code . '_public_key',
                'suffix' => $this->l('ex: pk_fd53cd467ba49022e4gf215e'),
                'type' => 'text',
                'size' => 60,
                'label' => $this->l('Public Key ' . $code),
                'prefix' => '<i class="icon icon-key"></i>',
                'col' => 6,
                'required' => true,
            );
            $inputs[] = array(
                'name' => $code . '_private_key',
                'suffix' => $this->l('ex: 21e5723a97459f6a'),
                'type' => 'text',
                'size' => 60,
                'label' => $this->l('Private Key ' . $code),
                'prefix' => '<i class="icon icon-key"></i>',
                'col' => 6,
                'required' => true,
            );
            if ($code !== "p4x") {
                $inputs[] = array(
                    'name' => $code . '_simulator_is_enabled',
                    'type' => (version_compare(_PS_VERSION_, '1.6')<0) ?'radio' :'switch',
                    'label' => $this->l('Simulator is enabled ' . $code),
                    'prefix' => '<i class="icon icon-key"></i>',
                    'class' => 't',
                    'required' => true,
                    'values'=> array(
                        array(
                            'id' => $code . '_simulator_is_enabled_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ),
                        array(
                            'id' => $code . '_simulator_is_enabled_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ),
                    )
                );
            }
        }
        $return = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Basic Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => $inputs,
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            )
        );
        return $return;
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

        $helper->fields_value['url_ok'] = Configuration::get('url_ok');

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Function to update the variables of Pagantis Module in the backoffice of prestashop
     *
     * @return string
     * @throws SmartyException
     */
    public function getContent()
    {
        $error = '';
        $message = '';
        $settings = array();
        $settingsKeys = array();
        $products = explode(',', Pagantis::getExtraConfig('PRODUCTS', null));
        foreach ($products as $product) {
            $code = Tools::strtolower(Pagantis::getExtraConfig('CODE', $product));
            $settings[$code . '_public_key'] = Configuration::get($code . '_public_key');
            $settings[$code . '_private_key'] = Configuration::get($code . '_private_key');
            $settings[$code . '_is_enabled'] = Configuration::get($code . '_is_enabled');
            if ($code !== 'p4x') {
                $settings[$code . '_simulator_is_enabled'] = Configuration::get($code . '_simulator_is_enabled');
            }
            $settingsKeys[] = $code . '_is_enabled';
            $settingsKeys[] = $code . '_public_key';
            $settingsKeys[] = $code . '_private_key';
            if ($code !== 'p4x') {
                $settingsKeys[] = $code . '_simulator_is_enabled';
            }
        }

        //Different Behavior depending on 1.6 or earlier
        if (Tools::isSubmit('submit'.$this->name)) {
            foreach ($settingsKeys as $key) {
                $value = Tools::getValue($key);
                Configuration::updateValue($key, $value);
                $settings[$key] = $value;
            }
            $message = $this->displayConfirmation($this->l('All changes have been saved'));
        } else {
            foreach ($settingsKeys as $key) {
                    $settings[$key] = Configuration::get($key);
            }
        }
        if ($error) {
            $message = $this->displayError($error);
        }

        $logo = 'https://cdn.digitalorigin.com/assets/master/logos/pg.png';
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

        $orderTotal = $cart->getOrderTotal();
        $promotedAmount = 0;
        $link = $this->context->link;
        $items = $cart->getProducts(true);
        foreach ($items as $item) {
            $itemCategories = ProductCore::getProductCategoriesFull($item['id_product']);
            if (in_array(PROMOTIONS_CATEGORY_NAME, $this->arrayColumn($itemCategories, 'name')) !== false) {
                $productId = $item['id_product'];
                $promotedAmount += Product::getPriceStatic($productId);
            }
        }

        $supercheckout_enabled = Module::isEnabled('supercheckout');
        $onepagecheckoutps_enabled = Module::isEnabled('onepagecheckoutps');
        $onepagecheckout_enabled = Module::isEnabled('onepagecheckout');

        $return = '';
        $this->context->smarty->assign($this->getButtonTemplateVars($cart));
        $products = explode(',', Pagantis::getExtraConfig('PRODUCTS', null));
        $templateConfigs = array();
        foreach ($products as $product) {
            if ($this->isPaymentMethodAvailable($product)) {
                $productConfigs = Pagantis::getExtraConfig($product, null);
                $productConfigs = json_decode($productConfigs, true);
                $publicKey = Configuration::get(Tools::strtolower($productConfigs['CODE']) . '_public_key');
                $simulatorIsEnabled = Configuration::get(Tools::strtolower($productConfigs['CODE']) . '_simulator_is_enabled');
                $isEnabled = Configuration::get(Tools::strtolower($productConfigs['CODE']) . '_is_enabled');

                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_TITLE'] = $this->l($productConfigs['TITLE']);
                unset($productConfigs['TITLE']);
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_AMOUNT'] = $orderTotal;
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_PROMOTED_AMOUNT'] = $promotedAmount;
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_LOCALE'] = $this->language;
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_COUNTRY'] = $this->language;
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_PUBLIC_KEY'] = $publicKey;
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_SIMULATOR_IS_ENABLED'] = $simulatorIsEnabled;
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_IS_ENABLED'] = $isEnabled;
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_LOGO'] = 'https://cdn.digitalorigin.com/assets/master/logos/pg-favicon.png';
                $uri = $link->getModuleLink('pagantis', 'payment');
                if (strpos($uri, '?') !== false) {
                    $uri .= '&product=' . Tools::strtolower($productConfigs['CODE']);
                } else {
                    $uri .= '?product=' . Tools::strtolower($productConfigs['CODE']);
                }
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_PAYMENT_URL'] = $uri;
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_PS_VERSION'] = str_replace('.', '-', Tools::substr(_PS_VERSION_, 0, 3));

                foreach ($productConfigs as $productConfigKey => $productConfigValue) {
                    $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . "_" . $productConfigKey] = $productConfigValue;
                }
                $this->context->smarty->assign($templateConfigs);
                if ($supercheckout_enabled || $onepagecheckout_enabled || $onepagecheckoutps_enabled) {
                    $this->checkLogoExists();
                    $return .= $this->display(
                        __FILE__,
                        'views/templates/hook/onepagecheckout-' . Tools::strtolower($productConfigs['CODE']) . '.tpl'
                    );
                } elseif (_PS_VERSION_ < 1.7) {
                    $return .= $this->display(
                        __FILE__,
                        'views/templates/hook/checkout-' . Tools::strtolower($productConfigs['CODE']) . '.tpl'
                    );
                }
            }
        }

        return $return;
    }

    /**
     * @param string $hookName
     * @return bool|string
     */
    public function productPageSimulatorDisplay($hookName = '')
    {
        $productId = Tools::getValue('id_product');
        if (!$productId) {
            return false;
        }
        //Resolves bug of reference passtrow
        $amount = Product::getPriceStatic($productId);
        $allowedCountries = unserialize(Pagantis::getExtraConfig('ALLOWED_COUNTRIES', null));

        $itemCategoriesNames = $this->arrayColumn(Product::getProductCategoriesFull($productId), 'name');
        $isPromotedProduct = in_array(PROMOTIONS_CATEGORY_NAME, $itemCategoriesNames);

        $return = '';
        $products = explode(',', Pagantis::getExtraConfig('PRODUCTS', null));
        $templateConfigs = array();
        foreach ($products as $product) {
            $productConfigs = Pagantis::getExtraConfig($product, null);
            $productConfigs = json_decode($productConfigs, true);

            $publicKey = Configuration::get(Tools::strtolower($productConfigs['CODE']) . '_public_key');
            $simulatorIsEnabled = Configuration::get(Tools::strtolower($productConfigs['CODE']) . '_simulator_is_enabled');
            if (Tools::strtolower($productConfigs['CODE']) === 'p4x') {
                $simulatorIsEnabled = true;
            }
            $isEnabled = Configuration::get(Tools::strtolower($productConfigs['CODE']) . '_is_enabled');
            $availableSimulators = array(
                'hookDisplayProductButtons' => array(
                    'sdk.simulator.types.SIMPLE',
                    'sdk.simulator.types.SELECTABLE',
                    'sdk.simulator.types.MARKETING',
                    'sdk.simulator.types.TEXT'
                ),
                'hookDisplayProductPriceBlock' => array(
                    'sdk.simulator.types.PRODUCT_PAGE',
                    'sdk.simulator.types.SELECTABLE_TEXT_CUSTOM',
                    'p4x',
                )
            );
            if ($isEnabled &&
                $simulatorIsEnabled &&
                $amount > 0 &&
                $amount > $productConfigs['DISPLAY_MIN_AMOUNT'] &&
                ($amount < $productConfigs['DISPLAY_MAX_AMOUNT'] || $productConfigs['DISPLAY_MAX_AMOUNT'] === '0') &&
                ($amount < $productConfigs['SIMULATOR_DISPLAY_MAX_AMOUNT'] || $productConfigs['SIMULATOR_DISPLAY_MAX_AMOUNT'] === '0') &&
                in_array(Tools::strtolower($this->language), $allowedCountries) &&
                (in_array($productConfigs['SIMULATOR_DISPLAY_TYPE'], $availableSimulators[$hookName]) || _PS_VERSION_ < 1.6)
            ) {
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_TITLE'] = $this->l($productConfigs['TITLE']);
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_SIMULATOR_TITLE'] = $this->l($productConfigs['SIMULATOR_TITLE']);
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_SIMULATOR_SUBTITLE'] = $this->l($productConfigs['SIMULATOR_SUBTITLE']);
                unset($productConfigs['TITLE']);
                unset($productConfigs['SIMULATOR_TITLE']);
                unset($productConfigs['SIMULATOR_SUBTITLE']);
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_AMOUNT'] = $amount;
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_AMOUNT4X'] = number_format(($amount / 4), 2, '.', '');
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_IS_PROMOTED_PRODUCT'] = $isPromotedProduct;
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_LOCALE'] = $this->language;
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_COUNTRY'] = $this->language;
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_PUBLIC_KEY'] = $publicKey;
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_SIMULATOR_IS_ENABLED'] = $simulatorIsEnabled;
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_IS_ENABLED'] = $isEnabled;
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_LOGO'] = 'https://cdn.digitalorigin.com/assets/master/logos/pg-favicon.png';
                $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . '_PS_VERSION'] = str_replace('.', '-', Tools::substr(_PS_VERSION_, 0, 3));
                foreach ($productConfigs as $productConfigKey => $productConfigValue) {
                    $templateConfigs[Tools::strtoupper(Tools::strtolower($productConfigs['CODE'])) . "_" . $productConfigKey] = $productConfigValue;
                }

                $this->context->smarty->assign($templateConfigs);
                $return .= $this->display(
                    __FILE__,
                    'views/templates/hook/product-simulator-' . Tools::strtolower($productConfigs['CODE']) . '.tpl'
                );
            }
        }

        return $return;
    }

    /**
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookDisplayProductButtons()
    {
        return $this->productPageSimulatorDisplay("hookDisplayProductButtons");
    }

    /**
     * @param $params
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookDisplayProductPriceBlock($params)
    {
        // $params['type'] = weight | price | after_price
        if (isset($params['type']) && $params['type'] === 'price' &&
            isset($params['smarty']) && isset($params['smarty']->template_resource) &&
            (strpos($params['smarty']->template_resource, 'product.tpl') !== false  ||
            strpos($params['smarty']->template_resource, 'product-prices.tpl') !== false)
        ) {
            return $this->productPageSimulatorDisplay("hookDisplayProductPriceBlock");
        }
        return '';
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

    /**
     * @param null   $config
     * @param        $product
     * @param string $default
     * @return string
     */
    public static function getExtraConfig($config = null, $product = "P4X", $default = '')
    {
        if (is_null($config)) {
            return '';
        }

        if (is_null($product)) {
            $sql = 'SELECT value FROM '._DB_PREFIX_.'pagantis_config where config = \'' . pSQL($config) . '\' limit 1';
            if ($results = Db::getInstance()->ExecuteS($sql)) {
                if (is_array($results) && count($results) === 1 && isset($results[0]['value'])) {
                    return $results[0]['value'];
                }
            }
        }

        $sql = 'SELECT value FROM '._DB_PREFIX_.'pagantis_config where config = \'' . pSQL($product) . '\' limit 1';
        if ($results = Db::getInstance()->ExecuteS($sql)) {
            if (is_array($results) && count($results) === 1 && isset($results[0]['value'])) {
                $configs = json_decode($results[0]['value'], true);
                $value = '';
                if (isset($configs[$config])) {
                    $value = $configs[$config];
                }
                return $value;
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
