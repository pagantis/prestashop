<?php

namespace Pagantis\OrdersApiClient\Model;

use Pagantis\OrdersApiClient\Model\Order\ActionUrls;
use Pagantis\OrdersApiClient\Model\Order\Configuration;
use Pagantis\OrdersApiClient\Model\Order\Metadata;
use Pagantis\OrdersApiClient\Model\Order\Refund;
use Pagantis\OrdersApiClient\Model\Order\ShoppingCart;
use Pagantis\OrdersApiClient\Model\Order\User;

/**
 * Class Order
 *
 * @package Pagantis\OrdersApiClient\Model
 */
class Order extends AbstractModel
{
    /**
     * Initial status of a order.
     */
    const STATUS_CREATED = 'CREATED';

    /**
     * Order has been authorized and initial payment has been approved. For finalizing the order
     * it's mandatory to confirm it.
     */
    const STATUS_AUTHORIZED = 'AUTHORIZED';

    /**
     * Order confirmed has been paid by customer and merchant has confirmed it. Payment is completed
     * and settlement will be created.
     */
    const STATUS_CONFIRMED = 'CONFIRMED';

    /**
     * Rejected by the risk engine, the transaction has been rejected and payment is no longer
     * expected nor possible.
     */
    const STATUS_REJECTED = 'REJECTED';

    /**
     * The order has been invalidated due to the expiration limit. If no action happens during the
     * defined time, the order could turn to invalidated.
     */
    const STATUS_INVALIDATED = 'INVALIDATED';

    /**
     * Undefined ERROR has occurred, please double check with the account manager or Pagantis support channels.
     */
    const STATUS_ERROR = 'ERROR';

    /**
     * If a order is not confirmed given the default confirmation time, defined previously, it will turn to
     * unconfirmed and this will refund any possible payment taken from the customer. The loan shall not be created.
     */
    const STATUS_UNCONFIRMED = 'UNCONFIRMED';

    /**
     * @var ActionUrls $actionUrls
     */
    protected $actionUrls;

    /**
     * @var string $apiVersion
     */
    protected $apiVersion;

    /**
     * @var Configuration $configuration
     */
    protected $configuration;

    /**
     * @var \DateTime $confirmedAt
     */
    protected $confirmedAt;

    /**
     * @var \DateTime $createdAt
     */
    protected $createdAt;

    /**
     * @var \DateTime $expiresAt
     */
    protected $expiresAt;

    /**
     * @var \DateTime $unconfirmedAt
     */
    protected $unconfirmedAt;

    /**
     * @var string $gracePeriod
     */
    protected $gracePeriod;

    /**
     * @var string $gracePeriodMonth
     */
    protected $gracePeriodMonth;

    /**
     * @var string $id
     */
    protected $id;

    /**
     * @var Metadata $metadata
     */
    protected $metadata;

    /**
     * @var Refund[] $refunds
     */
    protected $refunds;

    /**
     * @var ShoppingCart $shoppingCart
     */
    protected $shoppingCart;

    /**
     * @var string $status
     */
    protected $status;

    /**
     * @var User $user
     */
    protected $user;

    /**
     * Order constructor.
     */
    public function __construct()
    {
        $this->configuration = new Configuration();
        $this->metadata = new Metadata();
        $this->shoppingCart = new ShoppingCart();
        $this->user = new User();
    }

    /**
     * @return ActionUrls
     */
    public function getActionUrls()
    {
        return $this->actionUrls;
    }

    /**
     * @param ActionUrls $actionUrls
     *
     * @return Order
     */
    public function setActionUrls($actionUrls)
    {
        $this->actionUrls = $actionUrls;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     * @param string $apiVersion
     *
     * @return Order
     */
    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;

        return $this;
    }

    /**
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param Configuration $configuration
     *
     * @return Order
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getConfirmedAt()
    {
        return $this->confirmedAt;
    }

    /**
     * @param \DateTime $confirmedAt
     *
     * @return Order
     */
    public function setConfirmedAt($confirmedAt)
    {
        $this->confirmedAt = $confirmedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return Order
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * @param \DateTime $expiresAt
     *
     * @return Order
     */
    public function setExpiresAt($expiresAt)
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUnconfirmedAt()
    {
        return $this->unconfirmedAt;
    }

    /**
     * @param \DateTime $unconfirmedAt
     *
     * @return Order
     */
    public function setUnconfirmedAt($unconfirmedAt)
    {
        $this->unconfirmedAt = $unconfirmedAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getGracePeriod()
    {
        return $this->gracePeriod;
    }

    /**
     * @param string $gracePeriod
     *
     * @return Order
     */
    public function setGracePeriod($gracePeriod)
    {
        $this->gracePeriod = $gracePeriod;

        return $this;
    }

    /**
     * @return string
     */
    public function getGracePeriodMonth()
    {
        return $this->gracePeriodMonth;
    }

    /**
     * @param string $gracePeriodMonth
     *
     * @return Order
     */
    public function setGracePeriodMonth($gracePeriodMonth)
    {
        $this->gracePeriodMonth = $gracePeriodMonth;

        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return Order
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Metadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param Metadata $metadata
     *
     * @return Order
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * @return Refund[]
     */
    public function getRefunds()
    {
        return $this->refunds;
    }

    /**
     * @param Refund $refund
     *
     * @return $this
     */
    public function addRefund(Refund $refund)
    {
        $this->refunds[] = $refund;

        return $this;
    }

    /**
     * @return ShoppingCart
     */
    public function getShoppingCart()
    {
        return $this->shoppingCart;
    }

    /**
     * @param ShoppingCart $shoppingCart
     *
     * @return Order
     */
    public function setShoppingCart($shoppingCart)
    {
        $this->shoppingCart = $shoppingCart;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return Order
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return Order
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param \stdClass $object
     *
     * @throws \Exception
     */
    public function import($object)
    {
        $this->actionUrls = new ActionUrls();
        $this->configuration = new Configuration();
        $this->metadata = new Metadata();
        $this->refunds = array();
        $this->shoppingCart = new ShoppingCart();
        $this->user = new User();

        parent::import($object);
        $properties = get_object_vars($object);
        foreach ($properties as $key => $value) {
            if (is_array($value)) {
                if (is_array($this->{$key}) && $key == 'refunds') {
                    $this->refunds = array();
                    foreach ($value as $refund) {
                        $refundObject = new Refund();
                        $refundObject->import($refund);
                        $this->addRefund($refundObject);
                    }
                }
            }
        }
    }
}
