<?php

namespace Chill\MainBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ScopeControllerTest extends WebTestCase
{
    public function testCompleteScenario()
    {
        // Create a new client to browse the application
        $client = static::createClient(array(), array(
           'PHP_AUTH_USER' => 'admin',
           'PHP_AUTH_PW'   => 'password',
        ));

        // Create a new entry in the database
        $crawler = $client->request('GET', '/fr/admin/scope/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), 
                "Unexpected HTTP status code for GET /fr/admin/scope/");
        $crawler = $client->click($crawler->selectLink('Créer un nouveau cercle')->link());
        // Fill in the form and submit it
        $form = $crawler->selectButton('Créer')->form(array(
            'chill_mainbundle_scope[name][fr]'  => 'Test en fr',
            'chill_mainbundle_scope[name][en]'  => 'Test en en'
        ));

        $client->submit($form/*, array(
            'chill_mainbundle_scope' => array(
                'name' => array(
                    'fr' => 'test en fr',
                    'en' => 'test in english',
                    'nl' => 'test in nl'
                )
            )
        )*/);
        $crawler = $client->followRedirect();
        
        // Check data in the show view
        $this->assertGreaterThan(0, $crawler->filter('td:contains("Test en fr")')->count(),
                'Missing element td:contains("Test en fr")');

        // Edit the entity
        $crawler = $client->click($crawler->selectLink('Edit')->link());
        
        $form = $crawler->selectButton('Update')->form(array(
            'chill_mainbundle_scope[name][fr]'  => 'Foo',
            'chill_mainbundle_scope[name][en]'  => 'Foo en',
        ));

        $client->submit($form);
        $crawler = $client->followRedirect();

        // Check the element contains an attribute with value equals "Foo"
        $this->assertGreaterThan(0, $crawler->filter('[value="Foo"]')->count(), 'Missing element [value="Foo"]');

    }

}
