<?php
/**
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integration@pagantis.com>
 * @copyright 2019 Pagantis
 * @license   proprietary
 */

use Pagantis\ModuleUtils\Model\Log\LogEntry;

/**
 * Class AbstractController
 */
abstract class AbstractController extends ModuleFrontController
{
    /**
     * PAGANTIS_CODE
     */
    const PAGANTIS_CODE = 'pagantis';

    /**
     * @var array $headers
     */
    protected $headers;

    /**
     * Configure redirection
     *
     * @param string $url
     * @param array  $parameters
     */
    public function redirect($url = '', $parameters = array())
    {
        $parsedUrl = parse_url($url);
        $separator = ($parsedUrl['query'] == null) ? '?' : '&';
        $redirectUrl = $url. $separator . http_build_query($parameters);
        Tools::redirect($redirectUrl);
    }

    /**
     * Save log in SQL database
     *
     * @param array $data
     * @param null  $exception
     */
    public function saveLog($data = array(), $exception = null)
    {
        try {
            $logEntry = new LogEntry();
            if ($exception !== null) {
                $logEntry->error($exception);
            }
            if (isset($data['message'])) {
                $logEntry->setMessage($data['message']);
            }
            if (isset($data['line'])) {
                $logEntry->setLine($data['line']);
            }
            if (isset($data['file'])) {
                $logEntry->setFile($data['file']);
            }
            if (isset($data['code'])) {
                $logEntry->setCode($data['code']);
            }
            if (isset($data['trace'])) {
                $logEntry->setTrace($data['trace']);
            }

            Db::getInstance()->insert('pagantis_log', array(
                'log' => $logEntry->toJson()
            ));
        } catch (\Exception $exception) {
            // Do nothing
        }
    }
}
