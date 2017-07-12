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
define('_PS_PAYLATER_STATIC', '/modules/paylater');
define('PAYLATER_PROD_STATUS', array(0 => 'TEST', 1 => 'PROD'));
define('PAYLATER_SHOPPER_URL', 'https://shopper.pagamastarde.com');
define('PAYLATER_SHOPPER_DEMO_URL', 'http://shopper.localhost/prestashop/');

require _PS_PAYLATER_DIR.'/vendor/autoload.php';

/**
 * Class Paylater
 */
class Paylater extends PaymentModule
{
    /**
     * @var string
     */
    protected $url = 'https://pagamastarde.com';

    /**
     * @var bool
     */
    protected $bootstrap = true;

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
        $this->version = '6.0.0';
        $this->author = 'Paga+Tarde';
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->module_key = '2b9bc901b4d834bb7069e7ea6510438f';
        $this->ps_versions_compliancy = array('min' => '1.3', 'max' => _PS_VERSION_);
        $this->displayName = $this->l('Paga+Tarde');
        $this->description = $this->l(
            'Instant, easy and effective financial tool for your customers'
        );

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

        return (parent::install()
                && $this->registerHook('displayShoppingCart')
                && $this->registerHook('payment')
                && $this->registerHook('paymentOptions')
                && $this->registerHook('paymentReturn')
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
     * @param $params
     *
     * @return array
     */
    public function hookPaymentOptions($params)
    {
        if (!$this->isPaymentMethodAvailable()) {
            return array();
        }

        /** @var Cart $cart */
        $cart                   = $params['cart'];
        $orderTotal             = $cart->getOrderTotal();
        $link                   = $this->context->link;
        $paylaterProd           = Configuration::get('PAYLATER_PROD');
        $paylaterMode           = PAYLATER_PROD_STATUS[(int)$paylaterProd];
        $paylaterPublicKey     = Configuration::get('PAYLATER_PUBLIC_KEY_'.$paylaterMode);
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
            ->setLogo(_PS_PAYLATER_STATIC. '/logo.gif')
        ;

        if (_PS_VERSION_ >= 1.7) {
            $paymentOption    ->setAdditionalInformation(
                $this->fetch('module:paylater/views/templates/hook/checkout-17.tpl')
            );
        } else {
            $paymentOption    ->setAdditionalInformation(
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
                        'prefix' => '<i class="icon icon-gears"></i>',
                        'label' => $this->l('Working Mode'),
                        'name' => 'PAYLATER_PROD',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'production',
                                'value' => true,
                                'label' => $this->l('  Production'),
                            ),
                            array(
                                'id' => 'test',
                                'value' => false,
                                'label' => $this->l('  Test'),
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
                        'col' => 4,
                    ),
                    array(
                        'name' => 'PAYLATER_PRIVATE_KEY_TEST',
                        'suffix' => $this->l('ej: 21e5723a97459f6a'),
                        'type' => 'text',
                        'size' => 35,
                        'label' => $this->l('Private TEST API Key'),
                        'prefix' => '<i class="icon icon-key"></i>',
                        'col' => 4,
                    ),
                    array(
                        'name' => 'PAYLATER_PUBLIC_KEY_PROD',
                        'suffix' => $this->l('ej: pk_fd53cd4644a49022e4f8215e'),
                        'type' => 'text',
                        'size' => 35,
                        'label' => $this->l('Public PROD API Key'),
                        'prefix' => '<i class="icon icon-key"></i>',
                        'col' => 4,
                    ),
                    array(
                        'name' => 'PAYLATER_PRIVATE_KEY_PROD',
                        'suffix' => $this->l('ej: 21e57bcb97459f6a'),
                        'type' => 'text',
                        'size' => 35,
                        'label' => $this->l('Private PROD API Key'),
                        'prefix' => '<i class="icon icon-key"></i>',
                        'col' => 4,
                    ),
                    array(
                        'type' => 'radio',
                        'prefix' => '<i class="icon icon-money"></i>',
                        'label' => $this->l('The financial interests will be paid by'),
                        'name' => 'PAYLATER_DISCOUNT',
                        'values' => array(
                            array(
                                'id' => 'true',
                                'value' => 1,
                                'label' => $this->l('  The online commerce will cover the cost'),
                            ),
                            array(
                                'id' => 'false',
                                'value' => 0,
                                'label' => $this->l('  The end client who buys will cover the cost'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'radio',
                        'label' => $this->l('Payment behavior'),
                        'name' => 'PAYLATER_IFRAME',
                        'prefix' => '<i class="icon icon-desktop"></i>',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'iframe',
                                'value' => true,
                                'label' => $this->l('  open on iFrame inside the page'),
                            ),
                            array(
                                'id' => 'redirection',
                                'value' => false,
                                'label' => $this->l('  redirect the user to the payment page'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'radio',
                        'label' => $this->l('Include simulator in checkout'),
                        'name' => 'PAYLATER_ADD_SIMULATOR',
                        'prefix' => '<i class="icon icon-puzzle-piece"></i>',
                        'desc' => '
                                <a 
                                    href="http://docs.pagamastarde.com/marketing/simulador/" 
                                    target="_blank">' . $this->l("+ Info") . '
                                </a>
                            ',
                        'is_bool' => false,
                        'values' => array(
                            array(
                                'id' => 'simulator',
                                'value' => 0,
                                'label' => $this->l('  Don\'t display')
                            ),
                            array(
                                'id' => 'simulator',
                                'value' => 1,
                                'label' => $this->l('Mini simulator Paylater')
                            ),
                            array(
                                'id' => 'simulator',
                                'value' => 2,
                                'label' => $this->l('Complete simulator Paylater')
                            ),
                            array(
                                'id' => 'simulator',
                                'value' => 3,
                                'label' => $this->l('Selectable simulator Paylater')
                            ),
                            array(
                                'id' => 'simulator',
                                'value' => 4,
                                'label' => $this->l('Descriptive text Paylater')
                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'size' => 3,
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
        $confirmation = "";
        $settings = array();
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
        );

        //Different Behavior depending on 1.6 or earlier
        if (Tools::isSubmit('submit'.$this->name)) {
            foreach ($settingsKeys as $key) {
                $value = Tools::getValue($key);
                Configuration::updateValue($key, $value);
                $settings[$key] = $value;
            }
            $confirmation = $this->displayConfirmation($this->l('Se han guardado los cambios'));
        } else {
            foreach ($settingsKeys as $key) {
                switch ($key) {
                    case 'PAYLATER_MIN_AMOUNT':
                        $settings[$key] = Configuration::get((int)$key);
                        break;

                    default:
                        $settings[$key] = Configuration::get($key);
                        break;
                }
            }
        }

        $logo = _PS_PAYLATER_STATIC. '/views/img/logo-229x130.png';
        $css = _PS_PAYLATER_STATIC. '/views/css/paylater.css';
        $tpl = $this->local_path.'views/templates/admin/config-info.tpl';
        $this->context->smarty->assign(array(
            'logo' => $logo,
            'form' => $this->renderForm($settings),
            'confirmation' => $confirmation,
            'css' => $css
        ));

        return $this->context->smarty->fetch($tpl);
    }

    /**
     * Hook to show payment method, this only applies on prestashop <= 1.6
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
        $paylaterMode           = PAYLATER_PROD_STATUS[(int)$paylaterProd];
        $paylaterPublicKey     = Configuration::get('PAYLATER_PUBLIC_KEY_'.$paylaterMode);
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

        if (_PS_VERSION_ > 1.7) {
            return $this->display(__FILE__, 'views/templates/hook/checkout-17.tpl');
        } else {
            return $this->display(__FILE__, 'views/templates/hook/checkout-15.tpl');
        }
    }
}
