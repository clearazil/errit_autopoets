<?php

namespace ShoppingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UserBundle\Entity\Address;
use UserBundle\Entity\User;

/**
 * PurchaseOrder
 *
 * @ORM\Table(name="purchase_order")
 * @ORM\Entity(repositoryClass="ShoppingBundle\Repository\PurchaseOrderRepository")
 */
class PurchaseOrder
{
    const PAYMENT_METHOD_IDEAL = 0;
    const PAYMENT_METHOD_BANK_TRANSFER = 1;

    const STATUS_PAYMENT_NOT_RECEIVED = 0;
    const STATUS_PAYMENT_RECEIVED = 1;
    const STATUS_SHIPPED = 2;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="smallint")
     */
    private $status;

    /**
     * @var int
     *
     * @ORM\Column(name="payment_method", type="smallint")
     */
    private $paymentMethod;

    /**
     * @var float
     *
     * @ORM\Column(name="shipping_costs", type="decimal", precision=19, scale=4)
     */
    private $shippingCosts;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User", inversedBy="purchaseOrders")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity="UserBundle\Entity\Address")
     * @ORM\JoinTable(name="addresses_purchase_orders",
     *     joinColumns={@ORM\JoinColumn(name="purchase_order_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="address_id", referencedColumnName="id", unique=true)}
     *     )
     */
    private $addresses;

    /**
     * @ORM\OneToMany(targetEntity="PurchaseOrderLine", mappedBy="purchaseOrder")
     */
    private $purchaseOrderLines;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return PurchaseOrder
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->addresses = new ArrayCollection();
        $this->purchaseOrderLines = new ArrayCollection();
    }

    /**
     * Add address
     *
     * @param Address $address
     *
     * @return PurchaseOrder
     */
    public function addAddress(Address $address)
    {
        $this->addresses[] = $address;

        return $this;
    }

    /**
     * Remove address
     *
     * @param Address $address
     */
    public function removeAddress(Address $address)
    {
        $this->addresses->removeElement($address);
    }

    /**
     * Get addresses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * Add purchaseOrderLine
     *
     * @param PurchaseOrderLine $purchaseOrderLine
     *
     * @return PurchaseOrder
     */
    public function addPurchaseOrderLine(PurchaseOrderLine $purchaseOrderLine)
    {
        $this->purchaseOrderLines[] = $purchaseOrderLine;

        return $this;
    }

    /**
     * Remove purchaseOrderLine
     *
     * @param PurchaseOrderLine $purchaseOrderLine
     */
    public function removePurchaseOrderLine(PurchaseOrderLine $purchaseOrderLine)
    {
        $this->purchaseOrderLines->removeElement($purchaseOrderLine);
    }

    /**
     * Get purchaseOrderLines
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPurchaseOrderLines()
    {
        return $this->purchaseOrderLines;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return PurchaseOrder
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Address|null
     */
    public function getAddress()
    {
        if ($this->getAddresses() === null) {
            return null;
        }

        $address = $this->getAddresses()
            ->filter(function (Address $address) {
                return $address->getIsBilling() === false;
            });

        if (empty($address[0])) {
            return null;
        }

        return $address[0];
    }

    /**
     * Set paymentMethod
     *
     * @param integer $paymentMethod
     *
     * @return PurchaseOrder
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * Get paymentMethod
     *
     * @return integer
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @return string
     */
    public function getPaymentMethodString()
    {
        switch ($this->getPaymentMethod()) {
            case self::PAYMENT_METHOD_IDEAL:
                return 'PURCHASE_ORDER_PAYMENT_IDEAL';
            case self::PAYMENT_METHOD_BANK_TRANSFER:
                return 'PURCHASE_ORDER_PAYMENT_BANK_TRANSFER';
        }
    }

    /**
     * @return array
     */
    public static function getPaymentMethodOptions()
    {
        return [
            'PURCHASE_ORDER_PAYMENT_IDEAL' => self::PAYMENT_METHOD_IDEAL,
            'PURCHASE_ORDER_PAYMENT_BANK_TRANSFER' => self::PAYMENT_METHOD_BANK_TRANSFER,
        ];
    }

    /**
     * @return int
     */
    public function getTotalProducts()
    {
        $totalProducts = 0;

        foreach ($this->getPurchaseOrderLines() as $orderLine) {
            $totalProducts += $orderLine->getAmount();
        }

        return $totalProducts;
    }

    /**
     * @return float
     */
    public function getTotalPrice()
    {
        $totalPrice = 0;

        foreach ($this->getPurchaseOrderLines() as $orderLine) {
            $totalPrice += $orderLine->getPrice();
        }

        $totalPrice += $this->getShippingCosts();

        return $totalPrice;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return PurchaseOrder
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getStatusString()
    {
        switch ($this->getStatus()) {
            case self::STATUS_PAYMENT_NOT_RECEIVED:
                return 'PURCHASE_ORDER_PAYMENT_NOT_RECEIVED';
            case self::STATUS_PAYMENT_RECEIVED:
                return 'PURCHASE_ORDER_PAYMENT_RECEIVED';
            case self::STATUS_SHIPPED:
                return 'PURCHASE_ORDER_SHIPPED';
        }
    }

    /**
     * @return array
     */
    public static function getStatusOptions()
    {
        return [
            'PURCHASE_ORDER_PAYMENT_NOT_RECEIVED' => self::STATUS_PAYMENT_NOT_RECEIVED,
            'PURCHASE_ORDER_PAYMENT_RECEIVED' => self::STATUS_PAYMENT_RECEIVED,
            'PURCHASE_ORDER_SHIPPED' => self::STATUS_SHIPPED,
        ];
    }

    /**
     * Set shippingCosts
     *
     * @param float $shippingCosts
     *
     * @return PurchaseOrder
     */
    public function setShippingCosts($shippingCosts)
    {
        $this->shippingCosts = $shippingCosts;

        return $this;
    }

    /**
     * Get shippingCosts
     *
     * @return float
     */
    public function getShippingCosts()
    {
        return $this->shippingCosts;
    }
}
