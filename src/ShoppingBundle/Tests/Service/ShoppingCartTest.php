<?php

namespace ShoppingBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use ProductBundle\Entity\Product;
use ReflectionClass;
use ShoppingBundle\Service\ShoppingCart;

class ShoppingCartTest extends TestCase
{
    /**
     * @var int
     */
    private $id = 1;

    /**
     * @runInSeparateProcess
     */
    public function testEmptyCart()
    {
        $shoppingCart = new ShoppingCart();

        $this->assertEquals(0, $shoppingCart->getTotalPrice());
    }

    /**
     * @runInSeparateProcess
     */
    public function testAddProductToCart()
    {
        $shoppingCart = new ShoppingCart();

        $product1 = $this->getProduct(21.95);

        $shoppingCart->add($product1, 2);

        $this->assertEquals(43.9, $shoppingCart->getTotalPrice());
    }

    /**
     * @runInSeparateProcess
     */
    public function testAddTwoProductsToCart()
    {
        $shoppingCart = new ShoppingCart();

        $product1 = $this->getProduct(21.95);
        $product2 = $this->getProduct(6);

        $shoppingCart->add($product1, 2);
        $shoppingCart->add($product2);

        $this->assertEquals(49.9, $shoppingCart->getTotalPrice());
    }

    /**
     * @runInSeparateProcess
     */
    public function testNotFreeShipping()
    {
        $shoppingCart = new ShoppingCart();

        $product1 = $this->getProduct(24.99);

        $shoppingCart->add($product1);

        $this->assertEquals(6.95, $shoppingCart->getShippingCost());
    }

    /**
     * @runInSeparateProcess
     */
    public function testFreeShipping()
    {
        $shoppingCart = new ShoppingCart();

        $product1 = $this->getProduct(25);

        $shoppingCart->add($product1);

        $this->assertEquals(0, $shoppingCart->getShippingCost());
    }

    /**
     * @runInSeparateProcess
     */
    public function testRemove()
    {
        $shoppingCart = new ShoppingCart();

        $product1 = $this->getProduct(21.95);
        $product2 = $this->getProduct(6);

        $shoppingCart->add($product1, 2);
        $shoppingCart->add($product2);

        $this->assertEquals(49.9, $shoppingCart->getTotalPrice());

        $shoppingCart->remove($product2);

        $this->assertEquals(43.9, $shoppingCart->getTotalPrice());
    }

    /**
     * @runInSeparateProcess
     */
    public function testDecreaseOne()
    {
        $shoppingCart = new ShoppingCart();

        $product1 = $this->getProduct(21.95);
        $product2 = $this->getProduct(6);

        $shoppingCart->add($product1, 2);
        $shoppingCart->add($product2);

        $this->assertEquals(49.9, $shoppingCart->getTotalPrice());

        $shoppingCart->decrease($product1);

        $this->assertEquals(27.95, $shoppingCart->getTotalPrice());
    }

    /**
     * @runInSeparateProcess
     */
    public function testDecreaseAll()
    {
        $shoppingCart = new ShoppingCart();

        $product1 = $this->getProduct(21.95);
        $product2 = $this->getProduct(6);

        $shoppingCart->add($product1, 2);
        $shoppingCart->add($product2);

        $this->assertEquals(49.9, $shoppingCart->getTotalPrice());

        $shoppingCart->decrease($product1, 2);

        $this->assertEquals(12.95, $shoppingCart->getTotalPrice());
    }

    /**
     * @runInSeparateProcess
     */
    public function testTotalVatPrice()
    {
        $shoppingCart = new ShoppingCart();

        $product1 = $this->getProduct(21.95);
        $product2 = $this->getProduct(6);

        $shoppingCart->add($product1, 2);
        $shoppingCart->add($product2);

        $this->assertEquals(8.66, round($shoppingCart->getVat(), 2));
    }

    /**
     * @runInSeparateProcess
     */
    public function testPriceExcludingVat()
    {
        $shoppingCart = new ShoppingCart();

        $product1 = $this->getProduct(21.95);
        $product2 = $this->getProduct(6);

        $shoppingCart->add($product1, 2);
        $shoppingCart->add($product2);

        $this->assertEquals(41.24, round($shoppingCart->getTotalExcludingVat(), 2));
    }

    /**
     * @return int
     */
    private function getIncrementingId()
    {
        return $this->id++;
    }

    /**
     * Create a new Product with a price and give it an id
     * @param $price
     * @return Product
     */
    private function getProduct($price)
    {
        $product = new Product();

        $reflection = new ReflectionClass($product);

        $reflectionProperty = $reflection->getProperty('id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($product, $this->getIncrementingId());

        $product->setPrice($price);

        return $product;
    }
}
