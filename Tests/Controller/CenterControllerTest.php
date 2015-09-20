<?php

namespace Chill\MainBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CenterControllerTest extends WebTestCase
{
    public function testCompleteScenario()
    {
        // Create a new client to browse the application
        $client = static::createClient(array(), array(
           'PHP_AUTH_USER' => 'admin',
           'PHP_AUTH_PW'   => 'password',
        ));

        // Create a new entry in the database
        $crawler = $client->request('GET', '/fr/admin/center/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), 
              "Unexpected HTTP status code for GET /fr/admin/center/");
        $crawler = $client->click($crawler->selectLink('Créer un nouveau centre')->link());

        // Fill in the form and submit it
        $form = $crawler->selectButton('Créer')->form(array(
            'chill_mainbundle_center[name]'  => 'Test center',
        ));

        $client->submit($form);
        $crawler = $client->followRedirect();

        // Check data in the show view
        $this->assertGreaterThan(0, 
              $crawler->filter('td:contains("Test center")')->count(), 
              'Missing element td:contains("Test center")');

        // Edit the entity
        $crawler = $client->click($crawler->selectLink('Edit')->link());

        $form = $crawler->selectButton('Update')->form(array(
            'chill_mainbundle_center[name]'  => 'Foo',
        ));

        $client->submit($form);
        $crawler = $client->followRedirect();

        // Check the element contains an attribute with value equals "Foo"
        $this->assertGreaterThan(0, $crawler->filter('[value="Foo"]')->count(), 
              'Missing element [value="Foo"]');

        $crawler = $client->request('GET', '/fr/admin/center/');

        // Check the entity has been delete on the list
        $this->assertRegExp('/Foo/', $client->getResponse()->getContent());
    }
}
