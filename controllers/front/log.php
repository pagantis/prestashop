<?php
/**
 * This file is part of the official Clearpay module for PrestaShop.
 *
 * @author    Clearpay <integrations@clearpay.com>
 * @copyright 2019 Clearpay
 * @license   proprietary
 */

/**
 * Class ClearpayLogModuleFrontController
 */
class ClearpayLogModuleFrontController extends ModuleFrontController
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
        $limit = 200;
        $where = '';
        if (Tools::getValue('limit', false) && is_numeric(Tools::getValue('limit'))) {
            $limit = Tools::getValue('limit');
        }
        if (Tools::getValue('from', false)) {
            $where = 'WHERE createdAt >= \'' . Tools::getValue('from') . '\'';
        }
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'clearpay_log ' . $where . ' ORDER BY id desc LIMIT ' . $limit;
        if ($results = Db::getInstance()->ExecuteS($sql)) {
            foreach ($results as $row) {
                $data = (is_null(json_decode($row['log']))) ? $row['log'] : json_decode($row['log']);
                if (is_array($data)) {
                    $data['timestamp'] = $row['createdAt'];
                } else {
                    $data = array("message" => $data, 'timestamp' => $row['createdAt']);
                }
                $this->message[] = $data;
            }
        }
        $this->jsonResponse();
    }

    /**
     * Send a jsonResponse
     */
    public function jsonResponse()
    {
        $result = json_encode($this->message);
        if ($result === 'null') {
            $result = array();
        }

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
        $productCode = Tools::getValue('product', false);
        $products = explode(',', Clearpay::getExtraConfig('PRODUCTS', null));
        $privateKey = Configuration::get(Tools::strtolower($productCode) . '_private_key');
        $privateKeyGet = Tools::getValue('secret', false);
        if (!empty($privateKeyGet) && $privateKeyGet === $privateKey && in_array(Tools::strtoupper($productCode), $products)) {
            return true;
        }

        header('HTTP/1.1 403 Forbidden', true, 403);
        header('Content-Type: application/json', true);

        exit();
    }
}
