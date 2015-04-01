<?php

namespace TrainingBundle\Tests\Controller;

class BlogPostControllerTest extends BaseWebTestCase
{
    public function testIndex()
    {
        $crawler = $this->login();

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
