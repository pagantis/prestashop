<?php
/**
 * This file is part of the official Paylater module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2019 Paga+Tarde
 * @license   proprietary
 */

/**
 * Class PaylaterApiModuleFrontController
 */
class PaylaterApiModuleFrontController extends ModuleFrontController
{
    /**
     * @var string $message
     */
    protected $message;

    /**
     * @var bool $error
     */
    protected $error = false;

    /**
     * Controller index method:
     */
    public function postProcess()
    {
        if (!$this->authorize()) {
            return;
        };

        $userId = Tools::getValue('user_id', false);
        $from = Tools::getValue('from', false);
        $payment = Tools::getValue('payment', false);
        if ($payment == 'Paga Tarde') {
            $payment = 'Paga+Tarde';
        }

        if (_PS_VERSION_ > '1.6') {
            $orders = new PrestaShopCollection('Order');
        } else {
            $orders = new Collection('Order');
        }

        if ($userId) {
            $orders = $orders->where('id_customer', '=', $userId);
        }
        if ($payment) {
            $orders->where('payment', '=', $payment);
        }
        if ($from) {
            $orders->where('date_add', '>', $from);
        }

        foreach ($orders as $order) {
            $this->message[] = $order;
        }

        $this->jsonResponse();
    }

    /**
     * Send a jsonResponse
     */
    public function jsonResponse()
    {
        $result = json_encode(array(
            'timestamp' => time(),
            'result' => $this->message,
        ));

        header('HTTP/1.1 200 Ok', true, 200);
        header('Content-Type: application/json', true);
        header('Content-Length: ' . Tools::strlen($result));

        echo $result;
        exit();
    }

    /**
     * @return bool|null
     */
    public function authorize()
    {
        $privateKey = Configuration::get('pmt_private_key');

        if (Tools::getValue('secret', false) == $privateKey) {
            return true;
        }

        $result = json_encode(array(
            'timestamp' => time(),
            'result' => 'Access Forbidden',
        ));

        header('HTTP/1.1 403 Forbidden', true, 403);
        header('Content-Type: application/json', true);
        header('Content-Length: ' . Tools::strlen($result));

        echo $result;
        exit();
    }
}
