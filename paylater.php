<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require _PS_ROOT_DIR_ . '/modules/paylater/vendor/autoload.php';

/**
 * Class Paylater
 */
class Paylater extends PaymentModule
{
    /** Directory PATH of the module */
    const _PS_PAYLATER_DIR = _PS_MODULE_DIR_.'paylater/';

    /**
     * Array of possible status of variable PAYLATER_PROD
     */
    const PAYLATER_PROD_STATUS = [
        0 => 'TEST',
        1 => 'PROD'
    ];

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
        $this->ps_versions_compliancy = ['min' => '1.3', 'max' => _PS_VERSION_];
        $this->displayName = $this->l('Paga+Tarde');
        $this->description = $this->l(
            'Increase your sales with Paga+Tarde, a payment method that offers instant credit.'
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
        $availableCurrencies        = ['EUR']; //@todo fetch valid currencies from API
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
            return [];
        }

        /** @var Cart $cart */
        $cart                   = $params['cart'];
        $orderTotal             = $cart->getOrderTotal();
        $link                   = $this->context->link;
        $paylaterProd           = Configuration::get('PAYLATER_PROD');
        $paylaterMode           = self::PAYLATER_PROD_STATUS[(int)$paylaterProd];
        $paylatarePublicKey     = Configuration::get('PAYLATER_PUBLIC_KEY_'.$paylaterMode);
        $paylaterDiscount       = Configuration::get('PAYLATER_DISCOUNT');
        $paylaterAddSimulator   = Configuration::get('PAYLATER_ADD_SIMULATOR');

        $this->context->smarty->assign($this->getButtonTemplateVars($cart));
        $this->context->smarty->assign([
            'PAYLATER_DISCOUNT'         => $paylaterDiscount,
            'AMOUNT'                    => $orderTotal,
            'PAYLATER_PUBLIC_KEY'       => $paylatarePublicKey,
            'PAYLATER_ADD_SIMULATOR'    => $paylaterAddSimulator,
        ]);

        $paymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $paymentOption
            ->setCallToActionText($this->l('Pay with Paga+Tarde'))
            ->setAction($link->getModuleLink('paylater', 'payment'))
            ->setLogo(Media::getMediaPath(self::_PS_PAYLATER_DIR.'logo.gif'))
            ->setAdditionalInformation($this->fetch('module:paylater/views/templates/hook/checkout.tpl'))
        ;

        return [$paymentOption];
    }

    /**
     * Get the form for editing the backoffice options of the module
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
                        'type' => (_PS_VERSION_ >= 1.6) ? 'switch' : 'radio',
                        'label' => $this->l('Production Mode'),
                        'name' => 'PAYLATER_PROD',
                        'is_bool' => true,
                        'desc' => $this->l('Set the module to prod or test'),
                        'values' => array(
                            array(
                                'id' => 'prod',
                                'value' => true,
                                'label' => $this->l('Prodution'),
                            ),
                            array(
                                'id' => 'test',
                                'value' => false,
                                'label' => $this->l('Testing'),
                            ),
                        ),
                    ),
                    array(
                        'name' => 'PAYLATER_PUBLIC_KEY_TEST',
                        'type' => 'text',
                        'label' => $this->l('Public TEST API Key'),
                        'desc' => $this->l('Public test API Key'),
                        'prefix' => '<i class="icon icon-key"></i>',
                        'col' => 4,
                    ),
                    array(
                        'name' => 'PAYLATER_PRIVATE_KEY_TEST',
                        'type' => 'text',
                        'label' => $this->l('Private TEST API Key'),
                        'desc' => $this->l('Private test API Key'),
                        'prefix' => '<i class="icon icon-key"></i>',
                        'col' => 4,
                    ),
                    array(
                        'name' => 'PAYLATER_PUBLIC_KEY_PROD',
                        'type' => 'text',
                        'label' => $this->l('Public PROD API Key'),
                        'desc' => $this->l('Public PROD API Key'),
                        'prefix' => '<i class="icon icon-key"></i>',
                        'col' => 4,
                    ),
                    array(
                        'name' => 'PAYLATER_PRIVATE_KEY_PROD',
                        'type' => 'text',
                        'label' => $this->l('Private PROD API Key'),
                        'desc' => $this->l('Private PROD API Key'),
                        'prefix' => '<i class="icon icon-key"></i>',
                        'col' => 4,
                    ),
                    array(
                        'type' => 'select',
                        'name' => 'PAYLATER_DISCOUNT',
                        'desc' => $this->l('The shop will pay the interests of the loans'),
                        'is_bool' => true,
                        'label' => $this->l('Customer Free loans'),
                        'options' => array(
                            'query' => array(
                                array(
                                    'id_discount' => 'false',
                                    'name' => $this->l('Disabled')
                                ),
                                array(
                                    'id_discount' => 'true',
                                    'name' => $this->l('Enabled')
                                )
                            ),
                            'id' => 'id_discount',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => (_PS_VERSION_ >= 1.6) ? 'switch' : 'radio',
                        'label' => $this->l('Display mode'),
                        'name' => 'PAYLATER_IFRAME',
                        'is_bool' => true,
                        'desc' => $this->l('Select the way to display the payment page'),
                        'values' => array(
                            array(
                                'id' => 'iframe',
                                'value' => true,
                                'label' => $this->l('show the page in a modal'),
                            ),
                            array(
                                'id' => 'redirection',
                                'value' => false,
                                'label' => $this->l('redirect the user to the payment'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'name' => 'PAYLATER_ADD_SIMULATOR',
                        'desc' => $this->l('Show the loan simulator'),
                        'is_bool' => true,
                        'label' => $this->l('Include simulator'),
                        'options' => array(
                            'query' => array(
                                array(
                                    'id_type' => 'true',
                                    'name' => $this->l('Enabled')
                                ),
                                array(
                                    'id_type' => 'false',
                                    'name' => $this->l('Disabled')
                                )
                            ),
                            'id' => 'id_type',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Minimum Finance Amount'),
                        'name' => 'PAYLATER_MIN_AMOUNT',
                        'desc' => $this->l('Cart minimum amount to enable the finance method'),
                        'required' => false,
                        'col' => 2,
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
        $output = '';
        $settings = [];
        $settingsKeys = [
            'PAYLATER_PROD',
            'PAYLATER_PUBLIC_KEY_TEST',
            'PAYLATER_PRIVATE_KEY_TEST',
            'PAYLATER_PUBLIC_KEY_PROD',
            'PAYLATER_PRIVATE_KEY_PROD',
            'PAYLATER_DISCOUNT',
            'PAYLATER_ADD_SIMULATOR',
            'PAYLATER_IFRAME',
            'PAYLATER_MIN_AMOUNT',
        ];

        //Different Behavior depending on 1.6 or earlier
        if (Tools::isSubmit('submit'.$this->name)) {
            foreach ($settingsKeys as $key) {
                $value = Tools::getValue($key);
                Configuration::updateValue($key, $value);
                $settings[$key] = $value;
            }
            $output .= $this->displayConfirmation($this->l('Se han guardado los cambios'));
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

        return $output.$this->renderForm($settings);
    }

    /**
     * Hook to show payment method, this only applies on prestashop >= 1.6
     *
     * @param $params
     *
     * @return string
     */
    public function hookPayment($params)
    {
        if (!$this->isPaymentMethodAvailable()) {
            return false;
        }

        /** @var Cart $cart */
        $cart = $params['cart'];
        /** @var Customer $customer */
        $customer = $this->context->customer;
        $discount = Configuration::get('PAYLATER_DISCOUNT');
        $link = $this->context->link;
        $query = [
            'id_cart' => $this->context->cart->id,
            'key' => $this->context->cart->secure_key,
        ];
        $currency = new Currency($cart->id_currency);
        $callbackUrl = $link->getModuleLink('paylater', 'notify', $query);
        $cancelUrl = $link->getPageLink('order');
        $paylaterProd = Configuration::get('PAYLATER_PROD');
        $paylaterMode = self::PAYLATER_PROD_STATUS[(int)$paylaterProd];
        $paylaterPublicKey = Configuration::get('PAYLATER_PUBLIC_KEY_'.$paylaterMode);
        $paylaterPrivateKey = Configuration::get('PAYLATER_PRIVATE_KEY_'.$paylaterMode);
        $iframe = Configuration::get('PAYLATER_IFRAME');
        $includeSimulator = Configuration::get('PAYLATER_ADD_SIMULATOR');
        $this->context->smarty->assign($this->getButtonTemplateVars($cart));
        $this->context->smarty->assign('iframe', $iframe);

        $prestashopObjectModule = new \ShopperLibrary\ObjectModule\PrestashopObjectModule();
        $prestashopObjectModule
            ->setIncludeSimulator($includeSimulator)
            ->setCustomer($customer)
            ->setCart($cart)
        ;

        $prestashopObjectModule->requiredConfig
            ->setPublicKey($paylaterPublicKey)
            ->setPrivateKey($paylaterPrivateKey)
            ->setCurrency($currency->iso_code)
            ->setOrderId($cart->id)
            ->setAmount((int) ($cart->getOrderTotal() * 100))
            ->setCancelUrl($cancelUrl)
            ->setDiscount($discount)
            ->setIFrame($iframe)
            ->setCallbackUrl($callbackUrl)
        ;

        $prestashopObjectModule->customerConfig
            ->setName($customer->firstname)
            ->setEmail($customer->email)
            ->setBirthDate(new \DateTime(date('y-m-d', $customer->birthday)))
            ->setIsCustomer($customer->isGuest())
            ->setGender($customer->id_gender)
            ->setMemberSince(new \DateTime(date('y-m-d', $customer->date_add)))
        ;

        $shopperClient = new \ShopperLibrary\ShopperClient();
        $shopperClient->setObjectModule($prestashopObjectModule);
        $paymentForm = $shopperClient->getPaymentForm();

        $this->context->smarty->assign('form', $paymentForm);

        return $this->display(__FILE__, 'payment.tpl');
    }
}
