<?php

namespace DefaultBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    /** @var Client client */
    private $client;

    /** @var EntityManager $em */
    private $em;

    public function setUp()
    {
        $this->client = static::createClient();

        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    /**
     * @throws \Exception
     */
    public function testIndex()
    {
        $this->client->request('GET', '/');

        $this->assertContains('Uitgelicht', $this->client->getResponse()->getContent());
    }
}
