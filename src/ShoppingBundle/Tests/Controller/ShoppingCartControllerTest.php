<?php

namespace ShoppingBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ShoppingCartControllerTest extends WebTestCase
{
    public function testCart()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/cart');
    }

}
