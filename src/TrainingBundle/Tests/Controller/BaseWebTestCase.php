<?php

namespace TrainingBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class BaseWebTestCase extends WebTestCase
{
    /**
     * @var Client
     */
    public $client;

    protected $existingUser = [
        "_username" => 'email3@mail.com',
        "_password" => 'qwezxc',
    ];

    public function setUp()
    {
        $this->client = static::createClient();
    }

    protected function login($url = '/blog/')
    {
        $this->client->request('GET', $url);
        $crawler = $this->client->followRedirect();

        $this->assertEquals(
            1,
            $crawler->filter('a:contains("Login")')->count()
        );

        $buttonCrawlerNode = $crawler->selectButton('Login');
        $form = $buttonCrawlerNode->form($this->existingUser);
        $this->client->submit($form);

        $this->assertTrue(
            $this->client->getResponse()->isRedirect()
        );


        return $this->client->followRedirect();
    }
}