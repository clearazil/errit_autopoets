<?php

namespace UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccountControllerTest extends WebTestCase
{
    /** @var Client client */
    private $client;

    public function setUp()
    {
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'test',
            'PHP_AUTH_PW'   => 'test',
        ));
    }

    /**
     * @throws \Exception
     */
    public function testViewAccountPage()
    {
        $this->client->request('GET', '/account');

        $this->assertContains('Gegevens', $this->client->getResponse()->getContent());

        //$this->client->followRedirect();

        $link = $this->client->getCrawler()->filter(' a:contains("Bestellingen")')
            ->eq(0)
            ->link();

        $this->client->click($link);

        $this->assertContains('Bestelling bekijken', $this->client->getResponse()->getContent());
    }
}
