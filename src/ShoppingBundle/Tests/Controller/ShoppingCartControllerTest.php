<?php

namespace ShoppingBundle\Tests\Controller;

use ShoppingBundle\Entity\PurchaseOrder;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use UserBundle\Entity\User;

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
     * @throws \Exception
     */
    public function testRemoveProductFromCart(): void
    {
        $client = $this->placeProductsInCart(2);
        $crawler = $client->getCrawler();

        $link = $crawler
            ->filter('.cart-remove > a')
            ->eq(0)
            ->link();

        $crawler = $client->click($link);

        $this->assertEquals(1, $crawler->filter('.cart-product')->count());
    }

    /**
     * @throws \Exception
     */
    public function testChangeProductQuantity(): void
    {
        $client = $this->placeProductsInCart(1);
        $crawler = $client->getCrawler();

        $link = $crawler
            ->filter('.cart-increase')
            ->eq(0)
            ->link();

        $crawler = $client->click($link);

        $this->assertEquals(2, $crawler->filter('.cart-plus-minus-box')->attr('value'));

        $link = $crawler
            ->filter('.cart-decrease')
            ->eq(0)
            ->link();

        $crawler = $client->click($link);

        $this->assertEquals(1, $crawler->filter('.cart-plus-minus-box')->attr('value'));
    }

    /**
     * @throws \Exception
     */
    public function testCheckoutLoggedIn(): void
    {
        $client = $this->placeProductsInCart(2, true);
        $crawler = $client->getCrawler();

        $link = $crawler
            ->filter('a:contains("Naar bestellen")')
            ->eq(0)
            ->link();

        $client->click($link);

        $this->checkoutOrder($client);
    }

    /**
     * @throws \Exception
     */
    public function testCheckoutExistingCustomer(): void
    {
        $client = $this->placeProductsInCart(2);
        $crawler = $client->getCrawler();

        $link = $crawler
            ->filter('a:contains("Naar bestellen")')
            ->eq(0)
            ->link();

        $crawler = $client->click($link);

        $form = $crawler->filter('form[name=login]')->form();
        $form['login[username]'] = 'user_with_address';
        $form['login[password]'] = 'test';

        $client->submit($form);

        $this->checkoutOrder($client);
    }

    /**
     * @throws \Exception
     */
    public function testCheckoutWithNewAccount(): void
    {
        $this->checkoutAsNewUser(true, 'newaccount@newuser.nl');
    }

    /**
     * @throws \Exception
     */
    public function testCheckoutWithoutNewAccount(): void
    {
        $this->checkoutAsNewUser(false, 'noaccount@newuser.nl');
    }

    /**
     * @param bool $createAccount
     * @param string $email
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    private function checkoutAsNewUser(bool $createAccount, string $email): void
    {
        $client = $this->placeProductsInCart(2);
        $crawler = $client->getCrawler();

        $link = $crawler
            ->filter('a:contains("Naar bestellen")')
            ->eq(0)
            ->link();

        $crawler = $client->click($link);

        $form = $crawler->filter('form[name=register]')->form();
        $form['register[email]'] = $email;

        if ($createAccount) {
            $form['register[create_account]']->tick();
        }

        $client->submit($form);

        $this->checkoutOrder($client, [
            'address[firstName]' => 'Name',
            'address[lastName]' => 'LastName',
            'address[address]' => 'Address',
            'address[houseNumber]' => '30',
            'address[city]' => 'City',
            'address[phoneNumber]' => '0599-332211',
            'address[zipCode]' => '9592RR',
        ]);

        $em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $repository = $em->getRepository(User::class);
        $user = $repository->findUserByEmail($email);

        if ($createAccount) {
            $this->assertNotNull($user);
        } else {
            $this->assertNull($user);
        }
    }

    /**
     * @param Client $client
     * @param array $address
     * @return Client
     * @throws \Exception
     */
    private function checkoutOrder(Client $client, array $address = []): Client
    {
        $this->assertContains('Jouw bestelling', $client->getResponse()->getContent());

        $crawler = $client->getCrawler();

        $form = $crawler->filter('form[name=address]')->form();

        // set address fields
        foreach ($address as $key => $addressInput) {
            $form[$key] = $addressInput;
        }

        $crawler = $client->submit($form);

        // select a payment method (bank transfer)
        $form = $crawler->filter('form[name=payment]')->form();

        $form['payment[payment]'] = PurchaseOrder::PAYMENT_METHOD_BANK_TRANSFER;

        $client->submit($form);

        // success!
        $this->assertContains('Overmaken op rekening', $client->getResponse()->getContent());

        return $client;
    }

    /**
     * Place an amount of products in the cart
     *
     * The maximum amount is 9 (9 products per page),
     * for more than that functionality would need to
     * be extended (go to the next page when all products
     * on the page have been added)
     * @param int $amount
     * @param bool $loggedIn
     * @return Client
     * @throws \Exception
     */
    private function placeProductsInCart(int $amount, bool $loggedIn = false): Client
    {
        $client = static::createClient();

        if ($loggedIn) {
            $client = static::createClient(array(), array(
                'PHP_AUTH_USER' => 'user_with_address',
                'PHP_AUTH_PW' => 'test',
            ));
        }

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

    // TODO test back button
}
