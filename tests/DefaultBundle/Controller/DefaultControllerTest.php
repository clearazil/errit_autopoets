<?php

namespace DefaultBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    /** @var Client client */
    private $client = null;

    /** @var EntityManager $em */
    private $em = null;

    public function setUp()
    {
        $this->client = static::createClient();

        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    /**
     * @runInSeparateProcess
     */
    public function testIndex()
    {
        $this->client->request('GET', '/');

        $this->assertContains('Uitgelicht', $this->client->getResponse()->getContent());
    }

    /**
     * @runInSeparateProcess
     */
    public function testLoginForm()
    {
        $crawler = $this->client->request('GET', '/login');

        $button = $crawler->selectButton('Inloggen');

        $form = $button->form();

        $data = ['login[username]' => 'admin', 'login[password]' => 'test'];

        $this->client->submit($form, $data);

        $this->client->followRedirect();

        $this->assertContains('Uitloggen', $this->client->getResponse()->getContent());
    }

    /**
     * @runInSeparateProcess
     */
    public function testLogin()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'test',
        ));

        $client->request('GET', '/account');

        $this->assertContains('Uitloggen', $client->getResponse()->getContent());
    }
}
