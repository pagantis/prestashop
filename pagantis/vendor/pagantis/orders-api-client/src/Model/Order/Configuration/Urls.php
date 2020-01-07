<?php

namespace Pagantis\OrdersApiClient\Model\Order\Configuration;

use Pagantis\OrdersApiClient\Model\AbstractModel;

/**
 * Class Urls
 * @package Pagantis\OrdersApiClient\Model\Order\Configuration
 */
class Urls extends AbstractModel
{
    /**
     * @var string cancel URL
     */
    protected $cancel = null;

    /**
     * @var string ko URL
     */
    protected $ko = null;

    /**
     * @var string $authorizedNotificationCallback URL
     */
    protected $authorizedNotificationCallback = null;

    /**
     * @var string $rejectedNotificationCallback URL
     */
    protected $rejectedNotificationCallback = null;

    /**
     * @var string $invalidatedNotificationCallback URL
     */
    protected $invalidatedNotificationCallback = null;

    /**
     * @var string ok URL
     */
    protected $ok = null;

    /**
     * @param $url
     *
     * @return bool
     */
    public static function urlValidate($url)
    {
        return false !== filter_var($url, FILTER_VALIDATE_URL);
    }

    /**
     * @return string
     */
    public function getCancel()
    {
        return $this->cancel;
    }

    /**
     * @param string $cancel
     *
     * @return Urls
     */
    public function setCancel($cancel)
    {
        $this->cancel = $cancel;

        return $this;
    }

    /**
     * @return string
     */
    public function getKo()
    {
        return $this->ko;
    }

    /**
     * @param string $ko
     *
     * @return Urls
     */
    public function setKo($ko)
    {
        $this->ko = $ko;

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthorizedNotificationCallback()
    {
        return $this->authorizedNotificationCallback;
    }

    /**
     * @param string $authorizedNotificationCallback
     *
     * @return Urls
     */
    public function setAuthorizedNotificationCallback($authorizedNotificationCallback)
    {
        $this->authorizedNotificationCallback = $authorizedNotificationCallback;

        return $this;
    }

    /**
     * @return string
     */
    public function getRejectedNotificationCallback()
    {
        return $this->rejectedNotificationCallback;
    }

    /**
     * @param string $rejectedNotificationCallback
     *
     * @return Urls
     */
    public function setRejectedNotificationCallback($rejectedNotificationCallback)
    {
        $this->rejectedNotificationCallback = $rejectedNotificationCallback;

        return $this;
    }

    /**
     * @return string
     */
    public function getInvalidatedNotificationCallback()
    {
        return $this->invalidatedNotificationCallback;
    }

    /**
     * @param string $invalidatedNotificationCallback
     *
     * @return Urls
     */
    public function setInvalidatedNotificationCallback($invalidatedNotificationCallback)
    {
        $this->invalidatedNotificationCallback = $invalidatedNotificationCallback;

        return $this;
    }

    /**
     * @return string
     */
    public function getOk()
    {
        return $this->ok;
    }

    /**
     * @param string $ok
     *
     * @return Urls
     */
    public function setOk($ok)
    {
        $this->ok = $ok;

        return $this;
    }
}
