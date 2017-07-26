<?php
/**
 * This file is part of the official Paylater module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2015-2016 Paga+Tarde
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

        try {
            $orders = new PrestaShopCollection('Order');

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
        } catch (\Exception $exception) {
            $this->message = $exception->getMessage();
            $this->error = true;
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

        if ($this->error) {
            header('HTTP/1.1 400 Bad Request', true, 400);
        } else {
            header('HTTP/1.1 200 Ok', true, 200);
        }

        header('Content-Type: application/json', true);
        header('Content-Length: ' . Tools::strlen($result));

        echo $result;
    }

    /**
     *
     */
    public function authorize()
    {
        $paylaterProd = Configuration::get('PAYLATER_PROD');
        $paylaterMode = $paylaterProd == 1 ? 'PROD' : 'TEST';
        $privateKey = Configuration::get('PAYLATER_PRIVATE_KEY_'. $paylaterMode);

        if ($privateKey == getallheaders()['Secret']) {
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
    }
}
