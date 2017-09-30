<?php

namespace ShoppingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use ProductBundle\Entity\Product;

/**
 * PurchaseOrderLine
 *
 * @ORM\Table(name="purchase_order_line")
 * @ORM\Entity(repositoryClass="ShoppingBundle\Repository\PurchaseOrderLineRepository")
 */
class PurchaseOrderLine
{
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="subtotal", type="decimal", precision=19, scale=4)
     */
    private $subtotal;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=19, scale=4)
     */
    private $price;

    /**
     * @var int
     *
     * @ORM\Column(name="vat", type="float")
     */
    private $vat;

    /**
     * @var int
     *
     * @ORM\Column(name="amount", type="integer")
     */
    private $amount;

    /**
     * @ORM\ManyToOne(targetEntity="PurchaseOrder", inversedBy="purchaseOrderLines")
     * @ORM\JoinColumn(name="purchase_order_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $purchaseOrder;

    /**
     * @ORM\ManyToOne(targetEntity="ProductBundle\Entity\Product", inversedBy="purchaseOrderLines")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $product;


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
     * Set name
     *
     * @param string $name
     *
     * @return PurchaseOrderLine
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set subtotal
     *
     * @param string $subtotal
     *
     * @return PurchaseOrderLine
     */
    public function setSubtotal($subtotal)
    {
        $this->subtotal = $subtotal;

        return $this;
    }

    /**
     * Get subtotal
     *
     * @return string
     */
    public function getSubtotal()
    {
        return $this->subtotal;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return PurchaseOrderLine
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set vat
     *
     * @param integer $vat
     *
     * @return PurchaseOrderLine
     */
    public function setVat($vat)
    {
        $this->vat = $vat;

        return $this;
    }

    /**
     * Get vat
     *
     * @return int
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * Set amount
     *
     * @param integer $amount
     *
     * @return PurchaseOrderLine
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set purchaseOrder
     *
     * @param PurchaseOrder $purchaseOrder
     *
     * @return PurchaseOrderLine
     */
    public function setPurchaseOrder(PurchaseOrder $purchaseOrder = null)
    {
        $this->purchaseOrder = $purchaseOrder;

        return $this;
    }

    /**
     * Get purchaseOrder
     *
     * @return PurchaseOrder
     */
    public function getPurchaseOrder()
    {
        return $this->purchaseOrder;
    }

    /**
     * Set product
     *
     * @param Product $product
     *
     * @return PurchaseOrderLine
     */
    public function setProduct(Product $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }
}
