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
     * @var installErrors
     */
    public $installErrors = array();

    /**
     * Paylater constructor.
     *
     * Define the module main properties so that prestashop understands what are the module requirements
     * and how to manage the module.
     *
     */
    public function __construct()
    {
        $this->dotEnvError = null;
        $this->name = 'paylater';
        $this->tab = 'payments_gateways';
        $this->version = '7.1.0';
        $this->author = 'Paga+Tarde';
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->module_key = '2b9bc901b4d834bb7069e7ea6510438f';
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);
        $this->displayName = $this->l('Paga+Tarde');
        $this->description = $this->l(
            'Instant, easy and effective financial tool for your customers'
        );


        $continue = true;
        if (Module::isInstalled($this->name)) {
            if (!$this->upgrade()) {
                $this->context->controller->errors[] = $this->l('Unable to write file') .
                    ' ' . _PS_PAYLATER_DIR . '/.env ' .
                    $this->l('Ensure that the file exists and have the correct permissions');
                $this->dotEnvError = $this->l('Unable to write file') .
                    ' ' . _PS_PAYLATER_DIR . '/.env ' .
                    $this->l('Ensure that the file exists and have the correct permissions');
                $continue = false;
            }
        } else {
            copy(
                _PS_PAYLATER_DIR . '/.env.dist',
                _PS_PAYLATER_DIR . '/.env'
            );
        }

        if ($continue) {
            $sql_file = dirname(_PS_PAYLATER_DIR).'/sql/install.sql';
            $this->loadSQLFile($sql_file);

            try {
                $envFile = new Dotenv\Dotenv(_PS_PAYLATER_DIR);
                $envFile->load();
            } catch (\Exception $exception) {
                $this->context->controller->errors[] = $this->l('Unable to read file') .
                    ' ' . _PS_PAYLATER_DIR . '/.env ' .
                    $this->l('Ensure that the file exists and have the correct permissions');
                $this->dotEnvError = $this->l('Unable to read file') .
                    ' ' . _PS_PAYLATER_DIR . '/.env ' .
                    $this->l('Ensure that the file exists and have the correct permissions');
            }
        }
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

        $sql_file = dirname(__FILE__).'/sql/install.sql';
        $this->loadSQLFile($sql_file);

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
     * Upgrade module and generate/update .env file if needed
     */
    public function upgrade()
    {

        if (file_exists(_PS_PAYLATER_DIR . '/.env') && !is_writable(_PS_PAYLATER_DIR . '/.env')) {
            return false;
        }
        $envFileVariables = $this->readEnvFileAsArray(_PS_PAYLATER_DIR . '/.env');
        $distFileVariables = $this->readEnvFileAsArray(_PS_PAYLATER_DIR . '/.env.dist');
        $distFile = Tools::file_get_contents(_PS_PAYLATER_DIR . '/.env.dist');

        $newEnvFileArr = array_merge($distFileVariables, $envFileVariables);
        $newEnvFile = $this->replaceEnvFileValues($distFile, $newEnvFileArr);
        file_put_contents(_PS_PAYLATER_DIR . '/.env', $newEnvFile);

        // migrating pk/tk from previous version
        if (Configuration::get('pmt_public_key') === false && Configuration::get('PAYLATER_PUBLIC_KEY_PROD')) {
            Configuration::updateValue('pmt_public_key', Configuration::get('PAYLATER_PUBLIC_KEY_PROD'));
        } elseif (Configuration::get('pmt_public_key') === false && Configuration::get('PAYLATER_PUBLIC_KEY_TEST')) {
            Configuration::updateValue('pmt_public_key', Configuration::get('PAYLATER_PUBLIC_KEY_TEST'));
        }

        if (Configuration::get('pmt_private_key') === false && Configuration::get('PAYLATER_PRIVATE_KEY_PROD')) {
            Configuration::updateValue('pmt_private_key', Configuration::get('PAYLATER_PRIVATE_KEY_PROD'));
        } elseif (Configuration::get('pmt_private_key') === false && Configuration::get('PAYLATER_PRIVATE_KEY_TEST')) {
            Configuration::updateValue('pmt_private_key', Configuration::get('PAYLATER_PRIVATE_KEY_TEST'));
        }
        Configuration::updateValue('pmt_is_enabled', 1);

        return true;
    }

    /**
     * readEnvFileAsArray and return it as a key=>value array
     *
     * @param $filePath
     * @return array
     */
    protected function readEnvFileAsArray($filePath)
    {
        $envFileVariables = array();

        if (file_exists($filePath)) {
            // Read file into an array of lines with auto-detected line endings
            $autodetect = ini_get('auto_detect_line_endings');
            ini_set('auto_detect_line_endings', '1');
            $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            ini_set('auto_detect_line_endings', $autodetect);

            foreach ($lines as $line) {
                // Is a variable line ?
                if (!(isset($line[0]) && $line[0] === '#') && strpos($line, '=') !== false) {
                    list($name, $value) = array_map('trim', explode('=', $line, 2));
                    $envFileVariables[$name] = $value;
                }
            }
        }
        return $envFileVariables;
    }

    /**
     * @param $envFile
     * @param $replacements
     * @return mixed
     */
    protected function replaceEnvFileValues($envFile, $replacements)
    {
        foreach ($replacements as $key => $value) {
            $from = strpos($envFile, $key);
            if ($from !== false) {
                $to = strpos($envFile, '#', $from);
                $fromReplace = Tools::substr($envFile, $from, (($to - $from)-1));
                $toReplace = $key . '=' . $value;
                $envFile = str_replace($fromReplace, $toReplace, $envFile);
            }
        }
        return $envFile;
    }

    /**
     * @param $sql_file
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
        $pmtSimulatorIsEnabled      = Configuration::get('pmt_simulator_is_enabled');
        $pmtIsEnabled               = Configuration::get('pmt_is_enabled');
        $pmtSimulatorQuotesStart    = getenv('PMT_SIMULATOR_START_INSTALLMENTS');
        $pmtSimulatorQuotesMax      = getenv('PMT_SIMULATOR_MAX_INSTALLMENTS');
        $pmtTitle                   = $this->l(getenv('PMT_TITLE'));

        $this->context->smarty->assign($this->getButtonTemplateVars($cart));
        $this->context->smarty->assign(array(
            'amount'                => $orderTotal,
            'pmtPublicKey'          => $pmtPublicKey,
            'pmtQuotesStart'        => $pmtSimulatorQuotesStart,
            'pmtQuotesMax'          => $pmtSimulatorQuotesMax,
            'pmtSimulatorIsEnabled' => $pmtSimulatorIsEnabled,
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
        if ($this->dotEnvError) {
            $message = $this->displayError($this->dotEnvError);
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
        $pmtSimulatorIsEnabled      = Configuration::get('pmt_simulator_is_enabled');
        $pmtIsEnabled               = Configuration::get('pmt_is_enabled');
        $pmtSimulatorQuotesStart    = getenv('PMT_SIMULATOR_START_INSTALLMENTS');
        $pmtSimulatorQuotesMax      = getenv('PMT_SIMULATOR_MAX_INSTALLMENTS');
        $pmtTitle                   = $this->l(getenv('PMT_TITLE'));
        $this->context->smarty->assign($this->getButtonTemplateVars($cart));
        $this->context->smarty->assign(array(
            'amount'                => $orderTotal,
            'pmtPublicKey'          => $pmtPublicKey,
            'pmtQuotesStart'        => $pmtSimulatorQuotesStart,
            'pmtQuotesMax'          => $pmtSimulatorQuotesMax,
            'pmtSimulatorIsEnabled' => $pmtSimulatorIsEnabled,
            'pmtIsEnabled'          => $pmtIsEnabled,
            'pmtTitle'              => $pmtTitle,
            'paymentUrl'            => $link->getModuleLink('paylater', 'payment'),
            'ps_version'            => str_replace('.', '-', Tools::substr(_PS_VERSION_, 0, 3)),
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
        $productConfiguration = getenv('PMT_SIMULATOR_DISPLAY_POSITION');
        /** @var ProductCore $product */
        $product = new Product(Tools::getValue('id_product'));
        $amount = $product->getPublicPrice();
        $pmtPublicKey             = Configuration::get('pmt_public_key');
        $pmtSimulatorIsEnabled    = Configuration::get('pmt_simulator_is_enabled');
        $pmtIsEnabled             = Configuration::get('pmt_is_enabled');
        $pmtSimulatorProduct      = getenv('PMT_SIMULATOR_DISPLAY_TYPE');
        $pmtSimulatorQuotesStart  = getenv('PMT_SIMULATOR_START_INSTALLMENTS');
        $pmtSimulatorQuotesMax    = getenv('PMT_SIMULATOR_MAX_INSTALLMENTS');
        $pmtDisplayMinAmount      = getenv('PMT_DISPLAY_MIN_AMOUNT');

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
            'pmtSimulatorIsEnabled' => $pmtSimulatorIsEnabled,
            'pmtIsEnabled'          => $pmtIsEnabled,
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
