<?php

namespace Pagantis\OrdersApiClient\Model\Order;

use Pagantis\OrdersApiClient\Model\AbstractModel;

/**
 * Class ActionUrls
 * @package Pagantis\OrdersApiClient\Model\Order
 */
class ActionUrls extends AbstractModel
{
    /**
     * @var string $confirm
     */
    protected $confirm;

    /**
     * @var string $form
     */
    protected $form;

    /**
     * @var string $instoreEmail
     */
    protected $instoreEmail;

    /**
     * @var string $instoreSms
     */
    protected $instoreSms;

    /**
     * @var string $refund
     */
    protected $refund;

    /**
     * @var string $upsell
     */
    protected $upsell;

    /**
     * @return string
     */
    public function getConfirm()
    {
        return $this->confirm;
    }

    /**
     * @param string $confirm
     *
     * @return $this
     */
    public function setConfirm($confirm)
    {
        $this->confirm = $confirm;

        return $this;
    }

    /**
     * @return string
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param string $form
     *
     * @return $this
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @return string
     */
    public function getInstoreEmail()
    {
        return $this->instoreEmail;
    }

    /**
     * @param string $instoreEmail
     *
     * @return $this
     */
    public function setInstoreEmail($instoreEmail)
    {
        $this->instoreEmail = $instoreEmail;

        return $this;
    }

    /**
     * @return string
     */
    public function getInstoreSms()
    {
        return $this->instoreSms;
    }

    /**
     * @param string $instoreSms
     *
     * @return $this
     */
    public function setInstoreSms($instoreSms)
    {
        $this->instoreSms = $instoreSms;

        return $this;
    }

    /**
     * @return string
     */
    public function getRefund()
    {
        return $this->refund;
    }

    /**
     * @param string $refund
     *
     * @return $this
     */
    public function setRefund($refund)
    {
        $this->refund = $refund;

        return $this;
    }

    /**
     * @return string
     */
    public function getUpsell()
    {
        return $this->upsell;
    }

    /**
     * @param string $upsell
     *
     * @return $this
     */
    public function setUpsell($upsell)
    {
        $this->upsell = $upsell;

        return $this;
    }
}
