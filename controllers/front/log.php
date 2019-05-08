<?php
/**
 * This file is part of the official Paylater module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2019 Paga+Tarde
 * @license   proprietary
 */

/**
 * Class PaylaterLogModuleFrontController
 */
class PaylaterLogModuleFrontController extends ModuleFrontController
{
    /**
     * @var string $log
     */
    protected $log;

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

        $sql = 'SELECT * FROM '._DB_PREFIX_.'pmt_log ORDER BY id desc LIMIT 200';
        if ($results = Db::getInstance()->ExecuteS($sql)) {
            foreach ($results as $row) {
                if (is_null(json_decode($row['log']))) {
                    $message = $row['log'];
                } else {
                    $message = json_decode($row['log'], true);
                    //var_dump($message);
                    if (isset($message['message'])) {
                        $message = $message['message'];
                    }
                }
                $this->log[] = array(
                    'message' => $message,
                    'created_at' => $row['createdAt']
                );
            }
        }
        $this->jsonResponse();
    }

    /**
     * Send a jsonResponse
     */
    public function jsonResponse()
    {
        $result = json_encode($this->log);

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

        header('HTTP/1.1 403 Forbidden', true, 403);
        header('Content-Type: application/json', true);

        exit();
    }
}
