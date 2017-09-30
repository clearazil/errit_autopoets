<?php

namespace ShoppingBundle\Entity;

use ShoppingBundle\Entity\ShoppingCartProduct;
use Doctrine\Common\Collections\ArrayCollection;

class ShoppingCart implements \Serializable
{
    /**
     * @var array
     */
    private $products;

    /**
     * @var float
     */
    private $total;

    /**
     * @var float
     */
    private $subtotal;

    /**
     * @var integer|float
     */
    private $shippingCost;

    /**
     * @var float
     */
    private $totalExcludingVat;

    /**
     * @var float
     */
    private $totalVat;

    const DEFAULT_VAT = 21;

    public function __construct()
    {
        $this->products = new ArrayCollection;
    }

    /**
     * @return ArrayCollection $products
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param ArrayCollection $products
     * @return $this
     */
    public function setProducts($products)
    {
        $this->products = $products;

        return $this;
    }

    /**
     * @return integer
     */
    public function getVat()
    {
        return ShoppingCart::DEFAULT_VAT;
    }

    /**
     * @param ShoppingCartProduct $product
     */
    public function addProduct(ShoppingCartProduct $product)
    {
        $this->products->set($product->getProductId(), $product);
    }

    /**
     * @param  ShoppingCartProduct $product
     */
    public function removeProduct(ShoppingCartProduct $product)
    {
        $this->products->removeElement($product);
    }

    /**
     * @return float
     */
    public function getTotalExcludingVat()
    {
        return $this->totalExcludingVat;
    }

    /**
     * @param $totalExcludingVat
     * @return $this
     */
    public function setTotalExcludingVat($totalExcludingVat)
    {
        $this->totalExcludingVat = $totalExcludingVat;

        return $this;
    }

    /**
     * @return float
     */
    public function getTotalVat()
    {
        return $this->totalVat;
    }

    /**
     * @param $totalVat
     * @return $this
     */
    public function setTotalVat($totalVat)
    {
        $this->totalVat = $totalVat;

        return $this;
    }

    /**
     * @return float
     */
    public function getSubtotal()
    {
        return $this->subtotal;
    }

    /**
     * @param $subtotal
     * @return $this
     */
    public function setSubtotal($subtotal)
    {
        $this->subtotal = $subtotal;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param $total
     * @return $this
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * @return float|integer
     */
    public function getShippingCost()
    {
        if ($this->shippingCost === null) {
            return 0;
        }

        return $this->shippingCost;
    }

    /**
     * @param $shippingCost
     * @return $this
     */
    public function setShippingCost($shippingCost)
    {
        $this->shippingCost = $shippingCost;

        return $this;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            $this->products,
            $this->totalVat,
            $this->subtotal,
            $this->totalExcludingVat,
            $this->total,
            $this->shippingCost,
        ]);
    }

    /**
     * @param  string $serialized
     * @return mixed
     */
    public function unserialize($serialized)
    {
        list(
            $this->products,
            $this->totalVat,
            $this->subtotal,
            $this->totalExcludingVat,
            $this->total,
            $this->shippingCost,
            ) = unserialize($serialized);
    }
}
