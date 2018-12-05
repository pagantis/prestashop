<?php

/**
 * Class PaylaterLogModuleFrontController
 */
class PaylaterLogModuleFrontController extends ModuleFrontController
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


        $sql = 'SELECT * FROM '._DB_PREFIX_.'pmt_logs ORDER BY id desc LIMIT 200';
        if ($results = Db::getInstance()->ExecuteS($sql)) {
            foreach ($results as $row) {
                $this->message[] = json_decode($row['log']);
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
