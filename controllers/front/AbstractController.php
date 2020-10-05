<?php
/**
 * This file is part of the official Clearpay module for PrestaShop.
 *
 * @author    Clearpay <integrations@clearpay.com>
 * @copyright 2019 Clearpay
 * @license   proprietary
 */

use Clearpay\ModuleUtils\Model\Log\LogEntry;

/**
 * Class AbstractController
 */
abstract class AbstractController extends ModuleFrontController
{
    /**
     * CODE
     */
    const CODE = 'clearpay';

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
        $response = "";
        try {
            if (count($data) > 0) {
                $response = json_encode($data);
            } elseif (!is_null($exception)) {
                $logEntry = new LogEntry();
                $logEntry->error($exception);
                $response = $logEntry->toJson();
            }
            if (is_null($response)) {
                if (!is_null($exception)) {
                    $response = $exception->getMessage();
                } else {
                    $response = 'Unable to serialize log.'.
                        'data: '. json_encode($data).
                        'exception: '. json_encode($exception);
                }
            }

            Db::getInstance()->insert('clearpay_log', array(
                'log' => str_replace("'", "\'", $response)
            ));
        } catch (\Exception $error) {
            // Do nothing
        }
    }
}
