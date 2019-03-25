<?php
/**
 * This file is part of the official Paylater module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2019 Paga+Tarde
 * @license   proprietary
 */

use PagaMasTarde\ModuleUtils\Model\Log\LogEntry;

/**
 * Class AbstractController
 */
abstract class AbstractController extends ModuleFrontController
{
    /**
     * PMT_CODE
     */
    const PMT_CODE = 'paylater';

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
     * @param \Exception  $exception
     */
    public function saveLog($data = array(), $exception = null)
    {
        try {
            $logEntry = new LogEntry();
            if (count($data) > 0) {
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
            }  elseif (!is_null($exception)) {
                $logEntry->error($exception);
            }

            $response = $logEntry->toJson();
            if (is_null($response)) {
                if (count($data) > 0) {
                    $response = json_encode($data);
                } elseif (!is_null($exception)) {
                    $response = $exception->getMessage();
                } else {
                    $response = 'Unable to serialize log.'.
                        'data: '. json_encode($data).
                        'exception: '. json_encode($exception);
                }
            }

            Db::getInstance()->insert('pmt_log', array(
                'log' => $response
            ));
        } catch (\Exception $exception) {
            // Do nothing
        }
    }
}
