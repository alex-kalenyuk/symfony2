<?php

namespace TrainingBundle\Tests\Controller;

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

        $buttonCrawlerNode = $crawler->selectButton('Login');
        $form = $buttonCrawlerNode->form([
            "_username" => 'email2@mail.com',
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

    public function testShowPost()
    {
        $this->client->request('GET', '/blog/');
        $crawler = $this->client->followRedirect();
        $buttonCrawlerNode = $crawler->selectButton('Login');
        $form = $buttonCrawlerNode->form([
            "_username" => 'email2@mail.com',
            "_password" => 'qwezxc',
        ]);
        $this->client->submit($form);
        $crawler = $this->client->followRedirect();

        $titleEl = $crawler->filter('h3 a')->eq(1);
        $titleText = $titleEl->text();
        $titleLink = $titleEl->link();
        
        $this->client->click($titleLink);

        $this->assertEquals(
            1,
            $crawler->filter('h3:contains("'.$titleText.'")')->count()
        );
    }
}
