<?php

namespace TrainingBundle\Tests\Controller;

class UserControllerTest extends BaseWebTestCase
{
    private $newUserData = [
        "name" => 'test user',
        "email" => 'testemail@mail.com',
        "password" => 'qwezxc',
    ];

    public function testCreate()
    {
        $crawler = $this->login('/blog/user/create');

        $buttonCrawlerNode = $crawler->selectButton('Create User');
        $form = $buttonCrawlerNode->form([
            "trainingbundle_user[name]" => $this->newUserData['name'],
            "trainingbundle_user[email]" => $this->newUserData['email'],
            "trainingbundle_user[password]" => $this->newUserData['password'],
        ]);
        $this->client->submit($form);

        $this->assertTrue(
            $this->client->getResponse()->isRedirect()
        );
        $crawler = $this->client->followRedirect();

        $this->assertEquals(
            1,
            $crawler->filter('table:contains("'.$this->newUserData['name'].'")')->count()
        );

        $td = $crawler->filter('td:contains("'.$this->newUserData['name'].'")')->siblings();
        $link = $td->selectLink('Del')->link();

        $this->client->click($link);
    }
}
