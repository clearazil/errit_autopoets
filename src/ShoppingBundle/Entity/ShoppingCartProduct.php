<?php

namespace ShoppingBundle\Entity;

class ShoppingCartProduct
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $productId;

    /**
     * @var string
     */
    private $image;

    /**
     * @var float
     */
    private $price;

    /**
     * @var integer
     */
    private $amount;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return integer
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param $productId
     * @return $this
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param $image
     * @return $this
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param $price
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->price * $this->amount;
    }

    /**
     * @return integer
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @param $amount
     * @return $this
     */
    public function addAmount($amount)
    {
        $this->amount += $amount;

        return $this;
    }

    /**
     * @param $amount
     * @return $this
     */
    public function decreaseAmount($amount)
    {
        $this->amount -= $amount;

        return $this;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            $this->name,
            $this->productId,
            $this->image,
            $this->price,
            $this->amount,
        ]);
    }

    /**
     * @param  string $serialized
     * @return mixed
     */
    public function unserialize($serialized)
    {
        list(
            $this->name,
            $this->productId,
            $this->image,
            $this->price,
            $this->amount,
            ) = unserialize($serialized);
    }
}
