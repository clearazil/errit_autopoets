<?php

namespace ProductBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;

class ProductControllerTest extends WebTestCase
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->client = static::createClient();
    }

    /**
     * @throws \Exception
     */
    public function testProductIndex()
    {
        $crawler = $this->client->request('GET', '/products/');

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("product_with_category_1")')->count()
        );
    }

    /**
     * @throws \Exception
     */
    public function testCategory()
    {
        $this->tickCategories(['product-category-2']);

        $this->assertContains('product_with_category_2', $this->client->getResponse()->getContent());
    }

    /**
     * @throws \Exception
     */
    public function testProductsWithoutCategory()
    {
        $crawler = $this->client->request('GET', '/products/');

        $form = $crawler->filter('form[name=select_categories]')->form();

        $checkbox = $form['select_categories[other]'];

        $checkbox->tick();

        $this->client->submit($form);

        $this->assertContains('product_without_category', $this->client->getResponse()->getContent());
    }

    /**
     * @throws \Exception
     */
    public function testMultipleCategory()
    {
        $this->tickCategories(['product-category-2', 'product-category-3']);

        $this->assertContains('product_with_category_3', $this->client->getResponse()->getContent());
    }

    /**
     * @param array $categoryNames
     */
    private function tickCategories($categoryNames)
    {
        $crawler = $this->client->request('GET', '/products/');

        $form = $crawler->filter('form[name=select_categories]')->form();

        /** @var ChoiceFormField[] $categories */
        $categories = $form['select_categories[categories]'];

        foreach ($categories as $checkbox) {
            $available = $checkbox->availableOptionValues();

            if (in_array(reset($available), $categoryNames, true)) {
                $checkbox->tick();
            }
        }

        $this->client->submit($form);
    }
}
