<?php
/**
 * This file is part of the official Paylater module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2019 Paga+Tarde
 * @license   proprietary
 */

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
     * @param bool   $error
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
     * Return the HttpStatusCode description
     *
     * @param int $statusCode
     * @return string
     */
    public function getHttpStatusCode($statusCode = 200)
    {
        $httpStatusCodes = array(
            200 => "OK",
            201 => "Created",
            202 => "Accepted",
            400 => "Bad Request",
            401 => "Unauthorized",
            402 => "Payment Required",
            403 => "Forbidden",
            404 => "Not Found",
            405 => "Method Not Allowed",
            406 => "Not Acceptable",
            407 => "Proxy Authentication Required",
            408 => "Request Timeout",
            409 => "Conflict",
            429 => "Too Many Requests",
            500 => "Internal Server Error",
        );
        return isset($httpStatusCodes)? $httpStatusCodes[$statusCode] : $httpStatusCodes[200];
    }

    /**
     * Save log in SQL database
     *
     * @param array $data
     */
    public function saveLog($data = array())
    {
        try {
            $data = array_merge($data, array(
                'timestamp' => time(),
                'date' => date("Y-m-d H:i:s"),
            ));

            Db::getInstance()->insert('pmt_logs', array(
                'log' => json_encode(str_replace('\'', '`', $data)),
            ));
        } catch (\Exception $exception) {
            // Do nothing
        }
    }
}
