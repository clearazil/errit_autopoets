<?php

namespace ShoppingBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use ProductBundle\Entity\Product;
use ReflectionClass;
use ShoppingBundle\Service\ShoppingCart;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ShoppingCartTest extends TestCase
{
    /**
     * @var int
     */
    private $id = 1;

    /**
     * @return SessionInterface
     */
    private function getCartMock()
    {
        $session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $session;
    }

    /**
     * @throws \Exception
     */
    public function testEmptyCart()
    {
        $shoppingCart = new ShoppingCart($this->getCartMock());

        $this->assertEquals(0, $shoppingCart->getTotalPrice());
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function testAddProductToCart()
    {
        $shoppingCart = new ShoppingCart($this->getCartMock());

        $product1 = $this->getProduct(21.95);

        $shoppingCart->add($product1, 2);

        $this->assertEquals(43.9, $shoppingCart->getTotalPrice());
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function testAddTwoProductsToCart()
    {
        $shoppingCart = new ShoppingCart($this->getCartMock());

        $product1 = $this->getProduct(21.95);
        $product2 = $this->getProduct(6);

        $shoppingCart->add($product1, 2);
        $shoppingCart->add($product2);

        $this->assertEquals(49.9, $shoppingCart->getTotalPrice());
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function testNotFreeShipping()
    {
        $shoppingCart = new ShoppingCart($this->getCartMock());

        $product1 = $this->getProduct(24.99);

        $shoppingCart->add($product1);

        $this->assertEquals(6.95, $shoppingCart->getShippingCost());
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function testFreeShipping()
    {
        $shoppingCart = new ShoppingCart($this->getCartMock());

        $product1 = $this->getProduct(25);

        $shoppingCart->add($product1);

        $this->assertEquals(0, $shoppingCart->getShippingCost());
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function testRemove()
    {
        $shoppingCart = new ShoppingCart($this->getCartMock());

        $product1 = $this->getProduct(21.95);
        $product2 = $this->getProduct(6);

        $shoppingCart->add($product1, 2);
        $shoppingCart->add($product2);

        $this->assertEquals(49.9, $shoppingCart->getTotalPrice());

        $shoppingCart->remove($product2);

        $this->assertEquals(43.9, $shoppingCart->getTotalPrice());
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function testDecreaseOne()
    {
        $shoppingCart = new ShoppingCart($this->getCartMock());

        $product1 = $this->getProduct(21.95);
        $product2 = $this->getProduct(6);

        $shoppingCart->add($product1, 2);
        $shoppingCart->add($product2);

        $this->assertEquals(49.9, $shoppingCart->getTotalPrice());

        $shoppingCart->decrease($product1);

        $this->assertEquals(27.95, $shoppingCart->getTotalPrice());
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function testDecreaseAll()
    {
        $shoppingCart = new ShoppingCart($this->getCartMock());

        $product1 = $this->getProduct(21.95);
        $product2 = $this->getProduct(6);

        $shoppingCart->add($product1, 2);
        $shoppingCart->add($product2);

        $this->assertEquals(49.9, $shoppingCart->getTotalPrice());

        $shoppingCart->decrease($product1, 2);

        $this->assertEquals(12.95, $shoppingCart->getTotalPrice());
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function testTotalVatPrice()
    {
        $shoppingCart = new ShoppingCart($this->getCartMock());

        $product1 = $this->getProduct(21.95);
        $product2 = $this->getProduct(6);

        $shoppingCart->add($product1, 2);
        $shoppingCart->add($product2);

        $this->assertEquals(8.66, round($shoppingCart->getVat(), 2));
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function testPriceExcludingVat()
    {
        $shoppingCart = new ShoppingCart($this->getCartMock());

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
     * @throws \ReflectionException
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
