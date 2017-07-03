<?php

namespace ShoppingBundle\Service;

use ShoppingBundle\Entity\ShoppingCart as CartEntity;
use ShoppingBundle\Entity\ShoppingCartProduct;
use ProductBundle\Entity\Product;
use Symfony\Component\HttpFoundation\Session\Session;

class ShoppingCart
{
    private $shoppingCart = null;

    public function __construct()
    {
        if ($this->shoppingCart === null) {
            $session = new Session();

            $this->shoppingCart = $session->get('shoppingCart');

            if ($this->shoppingCart === null) {
                $this->shoppingCart = new CartEntity;

                $session->set('shoppingCart', $this->shoppingCart);
            }
        }

        $this->updateCart();
    }

    /**
     * Update the cart with totals and shipping cost
     */
    public function updateCart()
    {
        $totalVat = 0;
        $totalExcludingVat = 0;
        $total = 0;
        $subtotal = 0;

        foreach ($this->shoppingCart->getProducts() as $product) {
            $totalExcludingVat = $product->getTotal() / (100 + $this->shoppingCart->getVat()) * 100;
            $totalVat = $product->getTotal() - $totalExcludingVat;

            $totalVat += $totalVat;
            $subtotal += $product->getTotal();
        }

        $this->shoppingCart->setShippingCost(6.95);

        if ($subtotal >= 25) {
            $this->shoppingCart->setShippingCost(0);
        }

        $totalPrice = $subtotal + $this->shoppingCart->getShippingCost();

        $priceExcludingVat = $totalPrice / (100 + $this->shoppingCart->getVat()) * 100;
        $totalVatAmount = $totalPrice - $priceExcludingVat;

        $this->shoppingCart->setTotalVat($totalVatAmount);
        $this->shoppingCart->setTotalExcludingVat($priceExcludingVat);
        $this->shoppingCart->setSubtotal($subtotal);
        $this->shoppingCart->setTotal($totalPrice);
    }

    /**
     * Add a product to the cart or increase its amount
     * @param ProductBundle\Entity\Product $product
     * @param integer $amount
     */
    public function add(Product $product, $amount = 1)
    {
        $shoppingCartProduct = $this->getProductFromCart($product);

        if ($shoppingCartProduct !== null) {
            $shoppingCartProduct->addAmount($amount);
        } else {
            $shoppingCartProduct = new ShoppingCartProduct;
            $image = 'img/cart1.png';

            if ($product->getImages()->first()) {
                $image = 'img/product/thumbnail/' . $product->getImages()->first()->getImage();
            }
            
            $shoppingCartProduct->setName($product->getName())
             ->setProductId($product->getId())
             ->setImage($image)
             ->setPrice($product->getPrice())
             ->setAmount($amount);

            $this->shoppingCart->addProduct($shoppingCartProduct);
        }
    }

    /**
     * Decrease the amount of the product in the cart
     * Remove the product if the amount gets to zero or less
     * @param  ProductBundle\Entity\Product $product
     * @param  integer $amount
     */
    public function decrease(Product $product, $amount = 1)
    {
        $shoppingCartProduct = $this->getProductFromCart($product);

        if ($shoppingCartProduct !== null) {
            $shoppingCartProduct->decreaseAmount($amount);

            if ($shoppingCartProduct->getAmount() < 1) {
                $this->shoppingCart->removeProduct($shoppingCartProduct);
            }
        }
    }

    /**
     * Remove a product from the cart
     * @param  ProductBundle\Entity\Product $product
     */
    public function remove(Product $product)
    {
        $shoppingCartProduct = $this->getProductFromCart($product);

        if ($shoppingCartProduct !== null) {
            $this->shoppingCart->removeProduct($shoppingCartProduct);
        }
    }

    /**
     * Check if a Product is in the cart
     * @param  ProductBundle\Entity\Product $product
     * @return boolean
     */
    public function isProductInCart(Product $product)
    {
        $productsInCart = $this->shoppingCart->getProducts();

        return $productsInCart->containsKey($product->getId());
    }

    /**
     * Get a Product entity from the cart
     * @param  ProductBundle\Entity\Product $product
     * @return ShoppingBundle\Entity\ShoppingCartProduct
     */
    public function getProductFromCart(Product $product)
    {
        $productsInCart = $this->shoppingCart->getProducts();

        return $productsInCart->get($product->getId());
    }

    /**
     * Get all products in the cart
     * @return array
     */
    public function getProducts()
    {
        $productsInCart = $this->shoppingCart->getProducts();

        return $productsInCart;
    }

    /**
     * Get total of different products in the cart
     * @return integer
     */
    public function getProductAmount()
    {
        return $this->getProducts()->count();
    }

    /**
     * @return float
     */
    public function getSubtotal()
    {
        return $this->shoppingCart->getSubtotal();
    }

    /**
     * Get the total vat amount
     * @return float
     */
    public function getVat()
    {
        return $this->shoppingCart->getTotalVat();
    }

    /**
     * @return float
     */
    public function getTotalExcludingVat()
    {
        return $this->shoppingCart->getTotalExcludingVat();
    }

    /**
     * @return float
     */
    public function getTotalPrice()
    {
        return $this->shoppingCart->getTotal();
    }

    /**
     * @return float
     */
    public function getShippingCost()
    {
        return $this->shoppingCart->getShippingCost();
    }
}
