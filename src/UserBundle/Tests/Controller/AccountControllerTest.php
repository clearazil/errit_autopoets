<?php

namespace UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccountControllerTest extends WebTestCase
{
    /** @var Client client */
    private $client;

    /** @var Client $clientLoggedIn */
    private $clientLoggedIn;

    public function setUp()
    {
        $this->clientLoggedIn = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'test',
            'PHP_AUTH_PW'   => 'test',
        ));

        $this->client = static::createClient();
    }

    /**
     * @throws \Exception
     */
    public function testRecoverPassword(): void
    {
        // user without address
        $this->recoverPassword('noaddress@hotmail.com');
        // user with address
        $this->recoverPassword('hasaddress@hotmail.com');
    }

    /**
     * @param $email
     * @throws \Exception
     */
    private function recoverPassword($email): void
    {
        $crawler = $this->client->request('GET', '/recover-password');
        $form = $crawler->filter('form[name=recover_password]')->form();

        $this->client->submit($form, [
            'recover_password[email]' => $email,
        ]);

        $this->assertContains(
            'Er is een email verstuurd met een link om je wachtwoord opnieuw in te stellen' ,
            $this->client->getResponse()->getContent()
        );
    }

    /**
     * @throws \Exception
     */
    public function testViewAccountPage(): void
    {
        $this->clientLoggedIn->request('GET', '/account');

        $this->assertContains('Gegevens', $this->clientLoggedIn->getResponse()->getContent());

        $link = $this->clientLoggedIn->getCrawler()->filter(' a:contains("Bestellingen")')
            ->eq(0)
            ->link();

        $this->clientLoggedIn->click($link);

        $this->assertContains('Bestelling bekijken', $this->clientLoggedIn->getResponse()->getContent());
    }

    /**
     * @throws \Exception
     */
    public function testEditAccount(): void
    {
        // account without an address (create one)
        $client = $this->updateAccount('noaddress@hotmail.com', [
            'account[addresses][0][firstName]' => 'FirstName',
            'account[addresses][0][lastName]' => 'LastName',
            'account[addresses][0][companyName]' => 'test',
            'account[addresses][0][city]' => 'test',
            'account[addresses][0][address]' => 'test',
            'account[addresses][0][houseNumber]' => 'test',
            'account[addresses][0][zipCode]' => 'test',
            'account[addresses][0][phoneNumber]' => 'test',
        ]);

        $this->assertContains('FirstName LastName', $client->getResponse()->getContent());

        // update an account with an existing address
        $client = $this->updateAccount('hasaddress@hotmail.com');

        $this->assertContains('Dot Com', $client->getResponse()->getContent());

    }

    /**
     * @param string $email
     * @param array $formData
     * @return Client
     */
    private function updateAccount($email, array $formData = []): Client
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => $email,
            'PHP_AUTH_PW'   => 'test',
        ));

        $crawler = $client->request('GET', '/account/edit');

        $form = $crawler->filter('form[name=account]')->form();

        $client->submit($form, $formData);

        $client->followRedirect();

        return $client;
    }
}
