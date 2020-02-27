<?php
/**
 * This file is part of the official enCuotas module for PrestaShop.
 *
 * @author    Pagantis <integrations@pagantis.com>
 * @copyright 2019 Pagantis
 * @license   proprietary
 */

/**
 * Class PagantisLogModuleFrontController
 */
class PagantisConfigModuleFrontController extends ModuleFrontController
{
    /**
     * Initial method
     */
    public function initContent()
    {
        $this->authorize();
        $method = Tools::strtolower($_SERVER['REQUEST_METHOD']) . "Method";
        if (method_exists($this, $method)) {
            header('HTTP/1.1 200 Ok', true, 200);
            header('Content-Type: application/json', true);
            $result = json_encode($this->{$method}());
            header('Content-Length: ' . Tools::strlen($result));
            echo $result;
            exit();
        }
        header('HTTP/1.1 405 Method not allowed', true, 405);
        header('Content-Type: application/json', true);

        exit();
    }

    /**
     * @return array
     */
    public function getExtraConfigs()
    {
        $sql_content = 'select * from ' . _DB_PREFIX_. 'pagantis_config';
        $dbConfigs = Db::getInstance()->executeS($sql_content);

        $simpleDbConfigs = array();
        foreach ($dbConfigs as $config) {
            $simpleDbConfigs[$config['config']] = $config['value'];
        }

        return $simpleDbConfigs;
    }

    /**
     * Update POST params in DB
     */
    public function postMethod()
    {
        $errors = array();
        $post = (_PS_VERSION_ < 1.6) ? $_POST + $_GET : Tools::getAllValues();
        unset($post['fc']);
        unset($post['module']);
        unset($post['controller']);
        unset($post['secret']);
        if (count($post)) {
            foreach ($post as $config => $value) {
                $defaultConfigs = $this->getExtraConfigs();
                if (isset($defaultConfigs[$config])) {
                    Db::getInstance()->update(
                        'pagantis_config',
                        array('value' => pSQL($value)),
                        'config = \''. pSQL($config) .'\''
                    );
                } else {
                    $errors[$config] = $value;
                }
            }
        } else {
            $errors['NO_POST_DATA'] = 'No post data provided';
        }

        $dbConfigs = $this->getMethod();
        if (count($errors) > 0) {
            $dbConfigs['__ERRORS__'] = $errors;
        }
        return $dbConfigs;
    }

    /**
     * Read PTM configs
     *
     * @throws PrestaShopDatabaseException
     */
    public function getMethod()
    {
        $sql_content = 'select * from ' . _DB_PREFIX_. 'pagantis_config';
        $dbConfigs = Db::getInstance()->executeS($sql_content);

        $simpleDbConfigs = array();
        foreach ($dbConfigs as $config) {
            $simpleDbConfigs[$config['config']] = $config['value'];
        }
        return $simpleDbConfigs;
    }

    /**
     * @return bool|null
     */
    public function authorize()
    {
        $privateKey = Configuration::get('pagantis_private_key');

        if (Tools::getValue('secret', false) == $privateKey) {
            return true;
        }

        header('HTTP/1.1 403 Forbidden', true, 403);
        header('Content-Type: application/json', true);

        exit();
    }
}