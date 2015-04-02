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
        $crawler = $this->login();

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
