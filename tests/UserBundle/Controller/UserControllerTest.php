<?php

namespace DefaultBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
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

    /**
     * @throws \Exception
     */
    public function testLoginForm()
    {
        $crawler = $this->client->request('GET', '/login');

        $button = $crawler->selectButton('Inloggen');

        $form = $button->form();

        $data = ['login[username]' => 'test', 'login[password]' => 'test'];

        $this->client->submit($form, $data);

        $this->client->followRedirect();

        $this->assertContains('Uitloggen', $this->client->getResponse()->getContent());
    }

    /**
     * @throws \Exception
     */
    public function testLogin()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'test',
            'PHP_AUTH_PW'   => 'test',
        ));

        $client->request('GET', '/account');

        $this->assertContains('Uitloggen', $client->getResponse()->getContent());
    }
}
