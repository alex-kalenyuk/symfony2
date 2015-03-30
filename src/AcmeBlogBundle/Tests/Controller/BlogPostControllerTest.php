<?php

namespace AcmeBlogBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class BlogPostControllerTest extends WebTestCase
{
    /**
     * @var Client
     */
    public $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testIndex()
    {
        $this->client->request('GET', '/blog/');
        $crawler = $this->client->followRedirect();

        $this->assertEquals(
            1,
            $crawler->filter('a:contains("Login")')->count()
        );

        $buttonCrawlerNode = $crawler->selectButton('login');
        $form = $buttonCrawlerNode->form([
            "_username" => 'email1@mail.com',
            "_password" => 'qwezxc',
        ]);
        $this->client->submit($form);

        $this->assertTrue(
            $this->client->getResponse()->isRedirect()
        );
        $crawler = $this->client->followRedirect();

        $this->assertEquals(
            1,
            $crawler->filter('a:contains("Logout")')->count()
        );
    }
}
