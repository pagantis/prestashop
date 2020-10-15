<?php
/**
 * This file is part of the official Clearpay module for PrestaShop.
 *
 * @author    Clearpay <integrations@clearpay.com>
 * @copyright 2020 Clearpay
 * @license   proprietary
 */

define('_PS_CLEARPAY_DIR', _PS_MODULE_DIR_. '/clearpay');
define('PROMOTIONS_CATEGORY', 'clearpay-promotion-product');
define('PROMOTIONS_CATEGORY_NAME', 'Clearpay Promoted Product');

require _PS_CLEARPAY_DIR.'/vendor/autoload.php';

use Afterpay\SDK\Merchant as AfterpayMerchant;
use Afterpay\SDK\HTTP\Request\GetConfiguration as AfterpayGetConfigurationRequest;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Clearpay
 */
class Clearpay extends PaymentModule
{
    /**
     * Available currency
     */
    const CLEARPAY_AVAILABLE_CURRENCIES = 'EUR,GBP,USD';

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
        'CODE' =>'clearpay',
        'ALLOWED_COUNTRIES' => 'a:4:{i:1;s:2:"gb";i:2;s:2:"es";i:3;s:2:"fr";i:4;s:2:"it";}',
        'SIMULATOR_DISPLAY_TYPE' => 'clearpay',
        'SIMULATOR_IS_ENABLED' => true,
        'URL_OK' => '',
        'URL_KO' => ''
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
        $this->displayName = $this->l('Clearpay Payment Gateway');
        $this->description = $this->l('Buy now, pay later - Enjoy interest-free payments');
        $this->checkoutText = $this->l('or 4 payments of %€ with %');
        $this->currency = 'EUR';
        $context = Context::getContext();
        if (isset($context->currency)) {
            $this->currency =$context->currency->iso_code;
        }

        parent::__construct();

        $this->presetUserLanguage();
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
        if (!version_compare(phpversion(), '5.6.0', '>=')) {
            $this->_errors[] = $this->l('The PHP version bellow 5.3.0 is not supported');
            return false;
        }

        $sql_file = dirname(__FILE__) . '/sql/install.sql';
        $this->loadSQLFile($sql_file);

        $this->populateEnvVariables();

        Configuration::updateValue('CLEARPAY_IS_ENABLED', 0);
        Configuration::updateValue('CLEARPAY_SANDBOX_PUBLIC_KEY', '');
        Configuration::updateValue('CLEARPAY_SANDBOX_SECRET_KEY', '');
        Configuration::updateValue('CLEARPAY_PRODUCTION_ENVIRONMENT', '');
        Configuration::updateValue('CLEARPAY_PRODUCTION_SECRET_KEY', '');
        Configuration::updateValue('CLEARPAY_ENVIRONMENT', 1);
        Configuration::updateValue('CLEARPAY_MIN_AMOUNT', null);
        Configuration::updateValue('CLEARPAY_MAX_AMOUNT', null);
        Configuration::updateValue('CLEARPAY_RESTRICTED_CATEGORIES', '');


        $return =  (parent::install()
            && $this->registerHook('displayShoppingCart')
            && $this->registerHook('paymentOptions')
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
        Configuration::deleteByName('CLEARPAY_IS_ENABLED');
        Configuration::deleteByName('CLEARPAY_SANDBOX_PUBLIC_KEY');
        Configuration::deleteByName('CLEARPAY_SANDBOX_SECRET_KEY');
        Configuration::deleteByName('CLEARPAY_PRODUCTION_ENVIRONMENT');
        Configuration::deleteByName('CLEARPAY_PRODUCTION_SECRET_KEY');
        Configuration::deleteByName('CLEARPAY_ENVIRONMENT');
        Configuration::deleteByName('CLEARPAY_MIN_AMOUNT');
        Configuration::deleteByName('CLEARPAY_MAX_AMOUNT');
        Configuration::deleteByName('CLEARPAY_RESTRICTED_CATEGORIES');
        $sql_file = dirname(__FILE__).'/sql/uninstall.sql';
        $this->loadSQLFile($sql_file);

        return parent::uninstall();
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
     * Populate DB variables on installation
     */
    public function populateEnvVariables()
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
     * Check amount of order > minAmount
     * Check valid currency
     * Check API variables are set
     *
     * @param string $product
     * @return bool
     */
    public function isPaymentMethodAvailable()
    {
        $cart = $this->context->cart;
        $totalAmount = $cart->getOrderTotal(true, Cart::BOTH);
        $currency = new Currency($cart->id_currency);
        $availableCurrencies = explode(",", self::CLEARPAY_AVAILABLE_CURRENCIES);
        $displayMinAmount = Configuration::get('CLEARPAY_MIN_AMOUNT');
        $displayMaxAmount = Configuration::get('CLEARPAY_MAX_AMOUNT');
        $publicKey = Configuration::get('CLEARPAY_SANDBOX_PUBLIC_KEY');
        $secretKey = Configuration::get('CLEARPAY_SANDBOX_SECRET_KEY');
        $environment = Configuration::get('CLEARPAY_ENVIRONMENT');

        if ($environment === 'production') {
            $publicKey = Configuration::get('CLEARPAY_PRODUCTION_PUBLIC_KEY');
            $secretKey = Configuration::get('CLEARPAY_PRODUCTION_SECRET_KEY');
        }
        $this->allowedCountries = unserialize(Clearpay::getExtraConfig('ALLOWED_COUNTRIES', null));
        $this->presetUserLanguage();

        return (
            $totalAmount >= $displayMinAmount &&
            $totalAmount <= $displayMaxAmount &&
            in_array($currency->iso_code, $availableCurrencies) &&
            in_array(Tools::strtolower($this->language), $this->allowedCountries) &&
            $publicKey &&
            $secretKey
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
        $currency = new Currency($cart->id_currency);

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

        $url = 'https://js.afterpay.com/afterpay-1.x.js';
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
        $totalAmount = $cart->getOrderTotal(true, Cart::BOTH);

        $link = $this->context->link;

        $return = array();
        $this->context->smarty->assign($this->getButtonTemplateVars($cart));
        $templateConfigs = array();
        if ($this->isPaymentMethodAvailable()) {
            $publicKey = Configuration::get('CLEARPAY_SANDBOX_PUBLIC_KEY');
            $environment = Configuration::get('CLEARPAY_ENVIRONMENT');

            if ($environment === 'production') {
                $publicKey = Configuration::get('CLEARPAY_PRODUCTION_PUBLIC_KEY');
            }

            $isEnabled = Configuration::get('CLEARPAY_IS_ENABLED');

            $templateConfigs['TITLE'] = $this->checkoutText;
            $templateConfigs['MERCHANT_ID'] = $publicKey;
            $templateConfigs['TOTAL_AMOUNT'] = $totalAmount;
            $templateConfigs['IS_ENABLED'] = $isEnabled;
            $templateConfigs['ICON'] = Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/app_icon.png');
            $templateConfigs['LOGO'] = Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/logo.png');
            $templateConfigs['PAYMENT_URL'] = $link->getModuleLink('clearpay', 'payment');
            $templateConfigs['PS_VERSION'] = str_replace('.', '-', Tools::substr(_PS_VERSION_, 0, 3));

            $this->context->smarty->assign($templateConfigs);

            $paymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
            $uri = $link->getModuleLink('clearpay', 'payment');
            $paymentOption
                ->setCallToActionText($this->l('Pay with '))
                ->setAction($uri)
                ->setLogo($templateConfigs['LOGO'])
                ->setModuleName(__CLASS__)
                ->setAdditionalInformation(
                    $this->fetch('module:clearpay/views/templates/hook/checkout.tpl')
                )
            ;
            $return[] = $paymentOption;
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
        $inputs = array();
        $inputs[] = array(
            'name' => 'CLEARPAY_IS_ENABLED',
            'type' =>  (version_compare(_PS_VERSION_, '1.6')<0) ?'radio' :'switch',
            'label' => $this->l('Module is enabled'),
            'prefix' => '<i class="icon icon-key"></i>',
            'class' => 't',
            'required' => true,
            'values'=> array(
                array(
                    'id' => 'CLEARPAY_IS_ENABLED_TRUE',
                    'value' => 1,
                    'label' => $this->l('Yes', get_class($this), null, false),
                ),
                array(
                    'id' => 'CLEARPAY_IS_ENABLED_FALSE',
                    'value' => 0,
                    'label' => $this->l('No', get_class($this), null, false),
                ),
            )
        );
        $inputs[] = array(
            'name' => 'CLEARPAY_SANDBOX_PUBLIC_KEY',
            'suffix' => $this->l('ex: 400101010'),
            'type' => 'text',
            'label' => $this->l('Merchant id for Sandbox environment'),
            'prefix' => '<i class="icon icon-key"></i>',
            'col' => 6,
            'required' => true,
        );
        $inputs[] = array(
            'name' => 'CLEARPAY_SANDBOX_SECRET_KEY',
            'suffix' => $this->l('128 alphanumeric code'),
            'type' => 'text',
            'size' => 128,
            'label' => $this->l('Secret Key for Sandbox environment'),
            'prefix' => '<i class="icon icon-key"></i>',
            'col' => 6,
            'required' => true,
        );
        $inputs[] = array(
            'name' => 'CLEARPAY_PRODUCTION_PUBLIC_KEY',
            'suffix' => $this->l('ex: 400101010'),
            'type' => 'text',
            'label' => $this->l('Merchant id for Production environment'),
            'prefix' => '<i class="icon icon-key"></i>',
            'col' => 6,
            'required' => true,
        );
        $inputs[] = array(
            'name' => 'CLEARPAY_PRODUCTION_SECRET_KEY',
            'suffix' => $this->l('128 alphanumeric code'),
            'type' => 'text',
            'size' => 128,
            'label' => $this->l('Secret Key for Production environment'),
            'prefix' => '<i class="icon icon-key"></i>',
            'col' => 6,
            'required' => true,
        );
        $inputs[] = array(
            'name' => 'CLEARPAY_ENVIRONMENT',
            'type' => 'select',
            'label' => $this->l('API Environment'),
            'prefix' => '<i class="icon icon-key"></i>',
            'class' => 't',
            'required' => true,
            'options' => array(
                'query' => array(
                    array(
                        'CLEARPAY_ENVIRONMENT_id' => 'sandbox',
                        'CLEARPAY_ENVIRONMENT_name' => $this->l('Sandbox')
                    ),
                    array(
                        'CLEARPAY_ENVIRONMENT_id' => 'production',
                        'CLEARPAY_ENVIRONMENT_name' => $this->l('Production')
                    )
                ),
                'id' => 'CLEARPAY_ENVIRONMENT_id',
                'name' => 'CLEARPAY_ENVIRONMENT_name'
            )
        );

        $inputs[] = array(
            'name' => 'CLEARPAY_MIN_AMOUNT',
            'suffix' => $this->l('ex: 0.5'),
            'type' => 'text',
            'label' => $this->l('Min Payment Limit'),
            'col' => 6,
            'disabled' => true,
            'required' => false,
        );
        $inputs[] = array(
            'name' => 'CLEARPAY_MAX_AMOUNT',
            'suffix' => $this->l('ex: 800'),
            'type' => 'text',
            'label' => $this->l('Max Payment Limit'),
            'col' => 6,
            'disabled' => true,
            'required' => false,
        );
        $inputs[] = array(
            'type' => 'categories',
            'label' => $this->l('Restricted Categories'),
            'name' => 'CLEARPAY_RESTRICTED_CATEGORIES',
            'tree' => array(
                'id' => 'CLEARPAY_RESTRICTED_CATEGORIES',
                'selected_categories' => json_decode(Configuration::get('CLEARPAY_RESTRICTED_CATEGORIES')),
                'root_category' => Category::getRootCategory()->id,
                'use_search' => false,
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
    private function renderForm()
    {
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->show_toolbar = true;
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = array(
            'save' =>
                array(
                    'desc' => $this->l('Save'),
                    'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                        '&token='.Tools::getAdminTokenLite('AdminModules'),
                ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );


        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->fields_value['CLEARPAY_SANDBOX_PUBLIC_KEY'] = Configuration::get('CLEARPAY_SANDBOX_PUBLIC_KEY');
        $helper->fields_value['CLEARPAY_SANDBOX_SECRET_KEY'] = Configuration::get('CLEARPAY_SANDBOX_SECRET_KEY');
        $helper->fields_value['CLEARPAY_PRODUCTION_PUBLIC_KEY'] = Configuration::get('CLEARPAY_PRODUCTION_PUBLIC_KEY');
        $helper->fields_value['CLEARPAY_PRODUCTION_SECRET_KEY'] = Configuration::get('CLEARPAY_PRODUCTION_SECRET_KEY');
        $helper->fields_value['CLEARPAY_IS_ENABLED'] = Configuration::get('CLEARPAY_IS_ENABLED');
        $helper->fields_value['CLEARPAY_ENVIRONMENT'] = Configuration::get('CLEARPAY_ENVIRONMENT');
        $helper->fields_value['CLEARPAY_MIN_AMOUNT'] = Configuration::get('CLEARPAY_MIN_AMOUNT');
        $helper->fields_value['CLEARPAY_MAX_AMOUNT'] = Configuration::get('CLEARPAY_MAX_AMOUNT');

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
        $message = '';
        $settingsKeys = array();
        $settingsKeys[] = 'CLEARPAY_IS_ENABLED';
        $settingsKeys[] = 'CLEARPAY_SANDBOX_PUBLIC_KEY';
        $settingsKeys[] = 'CLEARPAY_SANDBOX_SECRET_KEY';
        $settingsKeys[] = 'CLEARPAY_PRODUCTION_PUBLIC_KEY';
        $settingsKeys[] = 'CLEARPAY_PRODUCTION_SECRET_KEY';
        $settingsKeys[] = 'CLEARPAY_ENVIRONMENT';
        $settingsKeys[] = 'CLEARPAY_RESTRICTED_CATEGORIES';

        //Different Behavior depending on 1.6 or earlier
        if (Tools::isSubmit('submit'.$this->name)) {
            foreach ($settingsKeys as $key) {
                $value = Tools::getValue($key);
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                Configuration::updateValue($key, $value);
            }
            $message = $this->displayConfirmation($this->l('All changes have been saved'));
        }

        // auto update configs on background
        $publicKey = Configuration::get('CLEARPAY_SANDBOX_PUBLIC_KEY');
        $secretKey = Configuration::get('CLEARPAY_SANDBOX_SECRET_KEY');
        $environment = Configuration::get('CLEARPAY_ENVIRONMENT');
        $isEnabled = Configuration::get('CLEARPAY_IS_ENABLED');

        if ($environment === 'production') {
            $publicKey = Configuration::get('CLEARPAY_PRODUCTION_PUBLIC_KEY');
            $secretKey = Configuration::get('CLEARPAY_PRODUCTION_SECRET_KEY');
        }

        if (!empty($publicKey) && !empty($secretKey)  && $isEnabled) {
            // auto update configs
            $merchant = new AfterpayMerchant();
            $merchant
                ->setMerchantId($publicKey)
                ->setSecretKey($secretKey)
            ;

            $getConfigurationRequest = new AfterpayGetConfigurationRequest();
            $getConfigurationRequest->setMerchant($merchant);
            $getConfigurationRequest->send();
            $configuration = $getConfigurationRequest->getResponse()->getParsedBody();

            if (isset($configuration->errorCode)) {
                $message = $this->displayError($configuration->message);
            } else {
                Configuration::updateValue('CLEARPAY_MIN_AMOUNT', $configuration[0]->minimumAmount->amount);
                Configuration::updateValue('CLEARPAY_MAX_AMOUNT', $configuration[0]->maximumAmount->amount);
            }
        }


        $logo = Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/logo.png');
        $tpl = $this->local_path.'views/templates/admin/config-info.tpl';
        $this->context->smarty->assign(array(
            'logo' => $logo,
            'form' => $this->renderForm(),
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

        $totalAmount = $cart->getOrderTotal(true, Cart::BOTH);
        $link = $this->context->link;

        $supercheckout_enabled = Module::isEnabled('supercheckout');
        $onepagecheckoutps_enabled = Module::isEnabled('onepagecheckoutps');
        $onepagecheckout_enabled = Module::isEnabled('onepagecheckout');

        $return = '';
        $this->context->smarty->assign($this->getButtonTemplateVars($cart));
        $templateConfigs = array();
        if ($this->isPaymentMethodAvailable($product)) {
            $publicKey = Configuration::get('CLEARPAY_SANDBOX_PUBLIC_KEY');
            $environment = Configuration::get('CLEARPAY_ENVIRONMENT');
            if ($environment === 'production') {
                $publicKey = Configuration::get('CLEARPAY_PRODUCTION_PUBLIC_KEY');
            }
            $isEnabled = Configuration::get('CLEARPAY_IS_ENABLED');


            $templateConfigs['PUBLIC_KEY'] = $publicKey;
            $templateConfigs['TOTAL_AMOUNT'] = $totalAmount;
            $templateConfigs['IS_ENABLED'] = $isEnabled;
            $templateConfigs['LOGO'] = Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/checkout_logo.png');
            $templateConfigs['PAYMENT_URL'] = $link->getModuleLink('clearpay', 'payment');
            $templateConfigs['PS_VERSION'] = str_replace('.', '-', Tools::substr(_PS_VERSION_, 0, 3));

            $this->context->smarty->assign($templateConfigs);
            if ($supercheckout_enabled || $onepagecheckout_enabled || $onepagecheckoutps_enabled) {
                $this->checkLogoExists();
                $return .= $this->display(
                    __FILE__,
                    'views/templates/hook/onepagecheckout.tpl'
                );
            } elseif (_PS_VERSION_ < 1.7) {
                $return .= $this->display(
                    __FILE__,
                    'views/templates/hook/checkout.tpl'
                );
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
        $amount = Product::getPriceStatic($productId);
        $allowedCountries = unserialize(Clearpay::getExtraConfig('ALLOWED_COUNTRIES', null));


        $simulatorIsEnabled = Clearpay::getExtraConfig('SIMULATOR_IS_ENABLED');
        $isEnabled = Configuration::get('CLEARPAY_IS_ENABLED');

        $return = '';
        $templateConfigs = array();

        if ($isEnabled &&
            $simulatorIsEnabled &&
            $amount > 0 &&
            $amount >= Configuration::get('CLEARPAY_MIN_AMOUNT') &&
            $amount <= Configuration::get('CLEARPAY_MAX_AMOUNT') &&
            in_array(Tools::strtolower($this->language), $allowedCountries)
        ) {
            $templateConfigs['PS_VERSION'] = str_replace('.', '-', Tools::substr(_PS_VERSION_, 0, 3));
            $templateConfigs['SDK_URL'] = 'https://js.afterpay.com/afterpay-1.x.js';
            $templateConfigs['CLEARPAY_MIN_AMOUNT'] = Configuration::get('CLEARPAY_MIN_AMOUNT');
            $templateConfigs['CLEARPAY_MAX_AMOUNT'] = Configuration::get('CLEARPAY_MAX_AMOUNT');
            $templateConfigs['CURRENCY'] = $this->currency;
            $language = Language::getLanguage($this->context->language->id);
            $templateConfigs['ISO_COUNTRY_CODE'] = $language['locale'];
            $templateConfigs['AMOUNT'] = $amount;


            $this->context->smarty->assign($templateConfigs);
            $return .= $this->display(
                __FILE__,
                'views/templates/hook/product-simulator.tpl'
            );
        }

        return $return;
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
        if (isset($params['type']) && $params['type'] === 'after_price' &&
            isset($params['smarty']) && isset($params['smarty']->template_resource) &&
            (strpos($params['smarty']->template_resource, 'product.tpl') !== false  ||
                strpos($params['smarty']->template_resource, 'product-prices.tpl') !== false)
        ) {
            return $this->productPageSimulatorDisplay();
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
     * @param null   $config
     * @param string $default
     * @return string
     */
    public static function getExtraConfig($config = null, $default = '')
    {
        if (is_null($config)) {
            return '';
        }

        $sql = 'SELECT value FROM '._DB_PREFIX_.'clearpay_config where config = \'' . pSQL($config) . '\' limit 1';
        if ($results = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql)) {
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
    private function presetUserLanguage()
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
}
