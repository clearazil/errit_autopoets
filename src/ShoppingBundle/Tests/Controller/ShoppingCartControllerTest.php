<?php

namespace ShoppingBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ShoppingCartControllerTest extends WebTestCase
{
    /**
     * @throws \Exception
     */
    public function testAddProductsToCart(): void
    {
        $this->placeProductsInCart(1);
        $this->placeProductsInCart(2);
    }

    /**
     * Place an amount of products in the cart
     *
     * The maximum amount is 9 (9 products per page),
     * for more than that functionality would need to
     * be extended (go to the next page when all products
     * on the page have been added)
     * @param $amount
     * @return Client
     * @throws \Exception
     */
    private function placeProductsInCart($amount): Client
    {
        $client = static::createClient();
        $client->followRedirects();

        $crawler = $client->getCrawler();

        for ($i = 0; $i < $amount; $i++) {
            $crawler = $client->request('GET', '/products/');

            $link = $crawler
                ->filter('.product-action-btn > ul > li > a')
                ->eq($i)
                ->link();

            $crawler = $client->click($link);
        }

        $this->assertEquals($amount, $crawler->filter('.cart-product')->count());

        return $client;
    }

    // TODO test remove product from cart

    // TODO test change product quantity

    // TODO test checkout logged in

    // TODO test checkout logged out
}
