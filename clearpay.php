<?php
/**
 * This file is part of the official Clearpay module for PrestaShop.
 *
 * @author    Clearpay <integrations@clearpay.com>
 * @copyright 2019 Clearpay
 * @license   proprietary
 */


if (!defined('_PS_VERSION_')) {
    exit;
}

define('_PS_CLEARPAY_DIR', _PS_MODULE_DIR_. '/clearpay');
define('PROMOTIONS_CATEGORY', 'clearpay-promotion-product');
define('PROMOTIONS_CATEGORY_NAME', 'Clearpay Promoted Product');

require _PS_CLEARPAY_DIR.'/vendor/autoload.php';

use Afterpay\SDK\Merchant as AfterpayMerchant;
use Afterpay\SDK\HTTP\Request\GetConfiguration as AfterpayGetConfigurationRequest;

/**
 * Class Clearpay
 */
class Clearpay extends PaymentModule
{
    /**
     * @var string
     */
    public $url = 'https://clearpay.com';

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
        'PRODUCTS' => 'CLEARPAY',
        'CLEARPAY' => '{
            "CODE": "clearpay",
            "SIMULATOR_DISPLAY_TYPE": "clearpay",
            "SIMULATOR_DISPLAY_PRODUCT_PAGE": true
        }'
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
     * Clearpay constructor.
     *
     * Define the module main properties so that prestashop understands what are the module requirements
     * and how to manage the module.
     *
     */
    public function __construct()
    {
        $this->name = 'clearpay';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'Clearpay';
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->module_key = '2b9bc901b4d834bb7069e7ea6510438f';
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);
        $this->displayName = $this->l('Clearpay');
        $this->description = $this->l(
            'Instant, easy and effective financial tool for your customers'
        );

        $current_context = Context::getContext();
        if (!is_null($current_context->controller) && $current_context->controller->controller_type != 'front') {
            $sql_file = dirname(__FILE__).'/sql/install.sql';
            $this->loadSQLFile($sql_file);
            $this->checkDatabaseTables();
            $this->checkEnvVariables();
            $this->migrate();
            $this->checkHooks();
            $this->checkPromotionCategory();
        }

        parent::__construct();

        $this->getUserLanguage();
    }

    /**
     * Configure the variables for Clearpay payment method.
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

        $products = explode(',', Clearpay::getExtraConfig('PRODUCTS', null));
        foreach ($products as $product) {
            $code = Clearpay::getExtraConfig('CODE', $product);
            Configuration::updateValue($code . '_is_enabled', 0);
            Configuration::updateValue($code . '_public_key', '');
            Configuration::updateValue($code . '_secret_key', '');
            Configuration::updateValue($code . '_environment', 1);
            Configuration::updateValue($code . '_min_amount', null);
            Configuration::updateValue($code . '_max_amount', null);
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
        $products = explode(',', Clearpay::getExtraConfig('PRODUCTS', null));
        foreach ($products as $product) {
            $code = Clearpay::getExtraConfig('CODE', $product);
            Configuration::deleteByName($code . '_public_key');
            Configuration::deleteByName($code . '_secret_key');
            Configuration::deleteByName($code . '_environment');
            Configuration::deleteByName($code . '_min_amount');
            Configuration::deleteByName($code . '_max_amount');
        }
        $sql_file = dirname(__FILE__).'/sql/uninstall.sql';
        $this->loadSQLFile($sql_file);

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
        $sql_content = 'select * from ' . _DB_PREFIX_. 'clearpay_config';
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
            Db::getInstance()->insert('clearpay_config', $data);
        }
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
     * checkDatabaseTables INTEGRITY
     */
    public function checkDatabaseTables()
    {
        try {
            $tableName = _DB_PREFIX_.'clearpay_order';
            $sql = "show tables like '"   . $tableName . "'";
            $data = Db::getInstance()->ExecuteS($sql);
            if (count($data) > 0) {
                $sql = "desc " . $tableName;
                $data = Db::getInstance()->ExecuteS($sql);
                if (count($data) == 2) {
                    $sql = "ALTER TABLE $tableName ADD COLUMN ps_order_id VARCHAR(60) AFTER order_id";
                    Db::getInstance()->Execute($sql);
                }
                if (count($data) == 3) {
                    $sql = "DROP TABLE $tableName";
                    Db::getInstance()->Execute($sql);
                    $sql_file = dirname(__FILE__).'/sql/install.sql';
                    $this->loadSQLFile($sql_file);
                }
            } else {
                $sql_file = dirname(__FILE__).'/sql/install.sql';
                $this->loadSQLFile($sql_file);
            }
            $tableName = _DB_PREFIX_.'clearpay_config';
            $sql = "show tables like '"   . $tableName . "'";
            $data = Db::getInstance()->ExecuteS($sql);
            if (count($data) > 0) {
                $sql = "desc "   . $tableName;
                $data = Db::getInstance()->ExecuteS($sql);
                if (count($data) === 3 && $data[2]['Type'] !== 'varchar(5000)') {
                    $sql = "ALTER TABLE $tableName MODIFY `value` VARCHAR(5000)";
                    Db::getInstance()->Execute($sql);
                }
                // return because clearpay tables exisit so load the sqlfile is not needed
            }
        } catch (\Exception $exception) {
            // do nothing
        }
    }

    /**
     * Check amount of order > minAmount
     * Check valid currency
     * Check API variables are set
     *
     * @param string $product
     * @return bool
     */
    public function isPaymentMethodAvailable($product = 'clearpay')
    {
        $configs = json_decode(Clearpay::getExtraConfig($product, null), true);
        $cart                      = $this->context->cart;
        $currency                  = new Currency($cart->id_currency);
        $availableCurrencies       = array('EUR');
        $clearpayDisplayMinAmount  = $configs['DISPLAY_MIN_AMOUNT'];
        $clearpayDisplayMaxAmount  = $configs['DISPLAY_MAX_AMOUNT'];
        $clearpayPublicKey         = Configuration::get($configs['CODE'] . '_public_key');
        $clearpayPrivateKey        = Configuration::get($configs['CODE'] . '_secret_key');
        $this->allowedCountries    = unserialize(Clearpay::getExtraConfig('ALLOWED_COUNTRIES', null));
        $this->getUserLanguage();
        return (
            $cart->getOrderTotal() >= $clearpayDisplayMinAmount &&
            ($cart->getOrderTotal() <= $clearpayDisplayMaxAmount || $clearpayDisplayMaxAmount == '0') &&
            in_array($currency->iso_code, $availableCurrencies) &&
            in_array(Tools::strtolower($this->language), $this->allowedCountries) &&
            $clearpayPublicKey &&
            $clearpayPrivateKey
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

        $url = 'https://cdn.clearpay.com/js/pg-v2/sdk.js';
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
        $products = explode(',', Clearpay::getExtraConfig('PRODUCTS', null));
        $templateConfigs = array();
        foreach ($products as $product) {
            if ($this->isPaymentMethodAvailable($product)) {
                $productConfigs = Clearpay::getExtraConfig($product, null);
                $productConfigs = json_decode($productConfigs, true);
                $publicKey = Configuration::get($productConfigs['CODE'] . '_public_key');
                $simulatorIsEnabled = $productConfigs['SIMULATOR_DISPLAY_PRODUCT_PAGE'];
                $isEnabled = Configuration::get($productConfigs['CODE'] . '_is_enabled');

                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_TITLE'] = $this->l($productConfigs['TITLE']);
                unset($productConfigs['TITLE']);
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_AMOUNT'] = $orderTotal;
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_PROMOTED_AMOUNT'] = $promotedAmount;
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_LOCALE'] = $this->language;
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_COUNTRY'] = $this->language;
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_PUBLIC_KEY'] = $publicKey;
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_SIMULATOR_IS_ENABLED'] = $simulatorIsEnabled;
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_IS_ENABLED'] = $isEnabled;
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_LOGO'] = _MODULE_DIR_ . 'clearpay/views/images/logo.png';
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_PAYMENT_URL'] = $link->getModuleLink('clearpay', 'payment') . '&product=' . $productConfigs['CODE'];
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_PS_VERSION'] = str_replace('.', '-', Tools::substr(_PS_VERSION_, 0, 3));

                foreach ($productConfigs as $productConfigKey => $productConfigValue) {
                    $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . "_" . $productConfigKey] = $productConfigValue;
                }
                $this->context->smarty->assign($templateConfigs);

                $paymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
                $uri = $link->getModuleLink('clearpay', 'payment');
                if (strpos($uri, '?') !== false) {
                    $uri .= '&product=' . $productConfigs['CODE'];
                } else {
                    $uri .= '?product=' . $productConfigs['CODE'];
                }
                $paymentOption
                    ->setCallToActionText($templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_TITLE'])
                    ->setAction($uri)
                    ->setLogo($templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_LOGO'])
                    ->setModuleName(__CLASS__)
                    ->setAdditionalInformation(
                        $this->fetch('module:clearpay/views/templates/hook/checkout-' . $productConfigs['CODE'] . '.tpl')
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
        $products = explode(',', Clearpay::getExtraConfig('PRODUCTS', null));
        $inputs = array();
        foreach ($products as $product) {
            $code = Clearpay::getExtraConfig('CODE', $product);
            $inputs[] = array(
                'name' => $code .'_is_enabled',
                'type' =>  (version_compare(_PS_VERSION_, '1.6')<0) ?'radio' :'switch',
                'label' => $this->l('Module is enabled'),
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
                'name' => $code . '_sandbox_public_key',
                'suffix' => $this->l('ex: 400101010'),
                'type' => 'text',
                'label' => $this->l('Merchant id for Sandbox environment'),
                'prefix' => '<i class="icon icon-key"></i>',
                'col' => 6,
                'required' => true,
            );
            $inputs[] = array(
                'name' => $code . '_sandbox_secret_key',
                'suffix' => $this->l('128 alphanumeric code'),
                'type' => 'text',
                'size' => 128,
                'label' => $this->l('Secret Key for Sandbox environment'),
                'prefix' => '<i class="icon icon-key"></i>',
                'col' => 6,
                'required' => true,
            );
            $inputs[] = array(
                'name' => $code . '_production_public_key',
                'suffix' => $this->l('ex: 400101010'),
                'type' => 'text',
                'label' => $this->l('Merchant id for Production environment'),
                'prefix' => '<i class="icon icon-key"></i>',
                'col' => 6,
                'required' => true,
            );
            $inputs[] = array(
                'name' => $code . '_production_secret_key',
                'suffix' => $this->l('128 alphanumeric code'),
                'type' => 'text',
                'size' => 128,
                'label' => $this->l('Secret Key for Production environment'),
                'prefix' => '<i class="icon icon-key"></i>',
                'col' => 6,
                'required' => true,
            );
            $inputs[] = array(
                'name' => $code . '_environment',
                'type' => 'select',
                'label' => $this->l('API Environment'),
                'prefix' => '<i class="icon icon-key"></i>',
                'class' => 't',
                'required' => true,
                'options' => array(
                    'query' => array(
                        array(
                            $code . '_environment_id' => 'sandbox',
                            $code . '_environment_name' => $this->l('Sandbox')
                        ),
                        array(
                            $code . '_environment_id' => 'production',
                            $code . '_environment_name' => $this->l('Production')
                        )
                    ),
                    'id' => $code . '_environment_id',
                    'name' => $code . '_environment_name'
                )
            );
        }
        $inputs[] = array(
            'name' => $code . '_min_amount',
            'suffix' => $this->l('ex: 0.5'),
            'type' => 'text',
            'label' => $this->l('Min Payment Limit'),
            'col' => 6,
            'disabled' => true,
            'required' => false,
        );
        $inputs[] = array(
            'name' => $code . '_max_amount',
            'suffix' => $this->l('ex: 800'),
            'type' => 'text',
            'label' => $this->l('Max Payment Limit'),
            'col' => 6,
            'disabled' => true,
            'required' => false,
        );
        $inputs[] = array(
            'name' => $code . '_restricted_categories',
            'type' => 'categories',
            'label' => $this->l('Restricted Categories'),
            'tree' => array(
                'id' => $code . '_restricted_categories',
                'selected_categories' => json_decode(Configuration::get($code . '_restricted_categories')),
                'root_category' => Category::getRootCategory()->id,
                'use_search' => true,
                'use_checkbox' => true,
            ),
        );

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
     * Function to update the variables of Clearpay Module in the backoffice of prestashop
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
        $products = explode(',', Clearpay::getExtraConfig('PRODUCTS', null));
        foreach ($products as $product) {
            $code = Clearpay::getExtraConfig('CODE', $product);
            $sandboxPublicKey = Configuration::get($code . '_public_key');
            $sandboxSecretKey = Configuration::get($code . '_secret_key');
            $productionPublicKey = Configuration::get($code . '_public_key');
            $productionSecretKey = Configuration::get($code . '_secret_key');
            $environment = Configuration::get($code . '_environment');
            $settings[$code . '_sandbox_public_key'] = $sandboxPublicKey;
            $settings[$code . '_sandbox_secret_key'] = $sandboxSecretKey;
            $settings[$code . '_production_public_key'] = $productionPublicKey;
            $settings[$code . '_production_secret_key'] = $productionSecretKey;
            $settings[$code . '_is_enabled'] = Configuration::get($code . '_is_enabled');
            $settings[$code . '_environment'] = Configuration::get($code . '_environment');
            $settings[$code . '_min_amount'] = Configuration::get($code . '_min_amount');
            $settings[$code . '_max_amount'] = Configuration::get($code . '_max_amount');
            $settings[$code . '_restricted_categories'] = Configuration::get($code . '_restricted_categories');

            $settingsKeys[] = $code . '_is_enabled';
            $settingsKeys[] = $code . '_sandbox_public_key';
            $settingsKeys[] = $code . '_sandbox_secret_key';
            $settingsKeys[] = $code . '_production_public_key';
            $settingsKeys[] = $code . '_production_secret_key';
            $settingsKeys[] = $code . '_sandbox_secret_key';
            $settingsKeys[] = $code . '_production_secret_key';
            $settingsKeys[] = $code . '_environment';
            $settingsKeys[] = $code . '_restricted_categories';

            // auto update configs
            if (!empty($sandboxPublicKey) && !empty($sandboxSecretKey)  && $environment === 'sandbox' ||
                !empty($productionPublicKey) && !empty($productionSecretKey)  && $environment === 'production') {
                // auto update configs
                $merchant = new AfterpayMerchant();
                $merchant
                    ->setMerchantId(($environment === 'sandbox') ? $sandboxPublicKey : $productionPublicKey)
                    ->setSecretKey(($environment === 'sandbox') ? $sandboxSecretKey : $productionSecretKey)
                ;

                $getConfigurationRequest = new AfterpayGetConfigurationRequest();
                $getConfigurationRequest->setMerchant($merchant);
                $getConfigurationRequest->send();
                $configuration = $getConfigurationRequest->getResponse()->getParsedBody();

                Configuration::updateValue($code . '_min_amount', $configuration[0]->minimumAmount->amount);
                Configuration::updateValue($code . '_max_amount', $configuration[0]->maximumAmount->amount);
            }
        }

        //Different Behavior depending on 1.6 or earlier
        if (Tools::isSubmit('submit'.$this->name)) {
            foreach ($settingsKeys as $key) {
                $value = Tools::getValue($key);
                if (is_array($value)) {
                    $value = json_encode($value);
                }
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


        $logo = _MODULE_DIR_ . 'clearpay/views/images/logo.png';
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
        $products = explode(',', Clearpay::getExtraConfig('PRODUCTS', null));
        $templateConfigs = array();
        foreach ($products as $product) {
            if ($this->isPaymentMethodAvailable($product)) {
                $productConfigs = Clearpay::getExtraConfig($product, null);
                $productConfigs = json_decode($productConfigs, true);
                $sandboxPublicKey = Configuration::get($code . '_public_key');
                $productionPublicKey = Configuration::get($code . '_public_key');
                $environment = Configuration::get($code . '_environment');
                $publicKey = ($environment === 'sandbox') ? $sandboxPublicKey : $productionPublicKey;
                $simulatorIsEnabled = Configuration::get($productConfigs['CODE'] . '_environment');
                $isEnabled = Configuration::get($productConfigs['CODE'] . '_is_enabled');

                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_TITLE'] = $this->l($productConfigs['TITLE']);
                unset($productConfigs['TITLE']);
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_AMOUNT'] = $orderTotal;
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_PROMOTED_AMOUNT'] = $promotedAmount;
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_LOCALE'] = $this->language;
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_COUNTRY'] = $this->language;
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_PUBLIC_KEY'] = $publicKey;
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_SIMULATOR_IS_ENABLED'] = $simulatorIsEnabled;
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_IS_ENABLED'] = $isEnabled;
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_LOGO'] = _MODULE_DIR_ . 'clearpay/views/images/logo.png';
                $uri = $link->getModuleLink('clearpay', 'payment');
                if (strpos($uri, '?') !== false) {
                    $uri .= '&product=' . $productConfigs['CODE'];
                } else {
                    $uri .= '?product=' . $productConfigs['CODE'];
                }
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_PAYMENT_URL'] = $uri;
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_PS_VERSION'] = str_replace('.', '-', Tools::substr(_PS_VERSION_, 0, 3));

                foreach ($productConfigs as $productConfigKey => $productConfigValue) {
                    $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . "_" . $productConfigKey] = $productConfigValue;
                }
                $this->context->smarty->assign($templateConfigs);
                if ($supercheckout_enabled || $onepagecheckout_enabled || $onepagecheckoutps_enabled) {
                    $this->checkLogoExists();
                    $return .= $this->display(
                        __FILE__,
                        'views/templates/hook/onepagecheckout-' . $productConfigs['CODE'] . '.tpl'
                    );
                } elseif (_PS_VERSION_ < 1.7) {
                    $return .= $this->display(
                        __FILE__,
                        'views/templates/hook/checkout-' . $productConfigs['CODE'] . '.tpl'
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
        $allowedCountries = unserialize(Clearpay::getExtraConfig('ALLOWED_COUNTRIES', null));

        $itemCategoriesNames = $this->arrayColumn(Product::getProductCategoriesFull($productId), 'name');
        $isPromotedProduct = in_array(PROMOTIONS_CATEGORY_NAME, $itemCategoriesNames);

        $return = '';
        $products = explode(',', Clearpay::getExtraConfig('PRODUCTS', null));
        $templateConfigs = array();
        foreach ($products as $product) {
            $productConfigs = Clearpay::getExtraConfig($product, null);
            $productConfigs = json_decode($productConfigs, true);
            $sandboxPublicKey = Configuration::get($code . '_public_key');
            $productionPublicKey = Configuration::get($code . '_public_key');
            $environment = Configuration::get($code . '_environment');
            $publicKey = ($environment === 'sandbox') ? $sandboxPublicKey : $productionPublicKey;
            $simulatorIsEnabled = $productConfigs['SIMULATOR_DISPLAY_PRODUCT_PAGE'];
            $isEnabled = Configuration::get($productConfigs['CODE'] . '_is_enabled');
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
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_TITLE'] = $this->l($productConfigs['TITLE']);
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_SIMULATOR_TITLE'] = $this->l($productConfigs['SIMULATOR_TITLE']);
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_SIMULATOR_SUBTITLE'] = $this->l($productConfigs['SIMULATOR_SUBTITLE']);
                unset($productConfigs['TITLE']);
                unset($productConfigs['SIMULATOR_TITLE']);
                unset($productConfigs['SIMULATOR_SUBTITLE']);
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_AMOUNT'] = $amount;
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_AMOUNT4X'] = number_format(($amount / 4), 2, '.', '');
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_IS_PROMOTED_PRODUCT'] = $isPromotedProduct;
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_LOCALE'] = $this->language;
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_COUNTRY'] = $this->language;
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_PUBLIC_KEY'] = $publicKey;
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_SIMULATOR_IS_ENABLED'] = $simulatorIsEnabled;
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_IS_ENABLED'] = $isEnabled;
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_LOGO'] = _MODULE_DIR_ . 'clearpay/views/images/logo.png';
                $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . '_PS_VERSION'] = str_replace('.', '-', Tools::substr(_PS_VERSION_, 0, 3));
                foreach ($productConfigs as $productConfigKey => $productConfigValue) {
                    $templateConfigs[Tools::strtoupper($productConfigs['CODE']) . "_" . $productConfigKey] = $productConfigValue;
                }

                $this->context->smarty->assign($templateConfigs);
                $return .= $this->display(
                    __FILE__,
                    'views/templates/hook/product-simulator-' . $productConfigs['CODE'] . '.tpl'
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
            $description = 'Clearpay: Products with this category have free financing assumed by the merchant. ' .
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
    public static function getExtraConfig($config = null, $product = "CLEARPAY", $default = '')
    {
        if (is_null($config)) {
            return '';
        }

        if (is_null($product)) {
            $sql = 'SELECT value FROM '._DB_PREFIX_.'clearpay_config where config = \'' . pSQL($config) . '\' limit 1';
            if ($results = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql)) {
                if (is_array($results) && count($results) === 1 && isset($results[0]['value'])) {
                    return $results[0]['value'];
                }
            }
        }

        $sql = 'SELECT value FROM '._DB_PREFIX_.'clearpay_config where config = \'' . pSQL($product) . '\' limit 1';
        if ($results = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql)) {
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
     * @param null   $config
     * @param string $product
     * @param string $default
     * @return mixed|string
     */
    public function setExtraConfig($config = null, $product = "CLEARPAY", $default = '')
    {
        if (is_null($config)) {
            return '';
        }

        if (is_null($product)) {
            $sql = 'SELECT value FROM '._DB_PREFIX_.'clearpay_config where config = \'' . pSQL($config) . '\' limit 1';
            if ($results = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql)) {
                if (is_array($results) && count($results) === 1 && isset($results[0]['value'])) {
                    return $results[0]['value'];
                }
            }
        }

        $sql = 'SELECT value FROM '._DB_PREFIX_.'clearpay_config where config = \'' . pSQL($product) . '\' limit 1';
        if ($results = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql)) {
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
        $logoPg = _PS_MODULE_DIR_ . 'clearpay//onepagecheckoutps/views/img/payments/clearpay.png';
        if (!file_exists($logoPg) && is_dir(_PS_MODULE_DIR_ . 'clearpay//onepagecheckoutps/views/img/payments')) {
            copy(
                _PS_CLEARPAY_DIR . '/logo.png',
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
