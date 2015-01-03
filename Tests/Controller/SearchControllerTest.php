<?php

/*
 * Chill is a software for social workers
 * Copyright (C) 2015 Julien Fastré <julien.fastre@champs-libres.coop>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Chill\MainBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Chill\MainBundle\Search\SearchInterface;



/**
 * Test the search controller
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class SearchControllerTest extends WebTestCase
{
    /*
    public function setUp()
    {
        static::bootKernel();
        
        //add a default service
        $this->addSearchService(
              $this->createDefaultSearchService('<p>I am default</p>', 10), 'default'
              );
        //add a domain service
        $this->addSearchService(
              $this->createDefaultSearchService('<p>I am domain bar</p>', 20), 'bar'
              );
    }
    
    /**
     * Test the behaviour when no domain is provided in the search pattern : 
     * the default search should be enabled
     */
    public function testSearchPath()
    {
        $client = $this->getAuthenticatedClient();
        
        $crawler = $client->request('GET', '/fr/search', array('q' => 'default search'));

        $this->assertTrue($client->getResponse()->isSuccessful(), 
              "The page is loaded without errors");
              
    }
    
    public function testSearchPathEmpty()
    {
        $client = $this->getAuthenticatedClient();
        
        $crawler = $client->request('GET', '/fr/search?q=');

        $this->assertGreaterThan(0, $crawler->filter('*:contains("Merci de fournir des termes de recherche.")')->count());
    }
    
    public function testDomainUnknow()
    {
        $client = $this->getAuthenticatedClient();
        
        $crawler = $client->request('GET', '/fr/search', array('q' => '@unknow domain'));

        $this->assertTrue($client->getResponse()->isSuccessful(), 
              "The page is loaded without errors");
        $this->assertGreaterThan(0, $crawler->filter('*:contains("Le domaine de recherche "unknow" est inconnu.")')->count(), 
              "Message domain unknow is shown");
        
    }
    
    public function testParsingIncorrect()
    {
        $client = $this->getAuthenticatedClient();
        
        $crawler = $client->request('GET', '/fr/search', 
              array('q' => '@domaine @domain double domaine'));
        
        $this->assertGreaterThan(0, $crawler->filter('*:contains("Recherche invalide")')
              ->count());
    }
    
    public function testUnknowName()
    {
        $client = $this->getAuthenticatedClient();
        
        $client->request('GET', '/fr/search', 
              array('q' => 'default search', 'name' => 'unknow'));
        
        $this->assertTrue($client->getResponse()->isNotFound());
    }
    
    
    public function testSearchWithinSpecificSearchName()
    {
        /*
        //add a search service which will be supported
        $this->addSearchService(
              $this->createNonDefaultDomainSearchService("<p>I am domain foo</p>", 100, TRUE), 'foo'
              );
        
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/fr/search', 
              array('q' => '@foo default search', 'name' => 'foo'));

        //$this->markTestSkipped();
        $this->assertEquals(0, $crawler->filter('p:contains("I am default")')->count(), 
              "The mocked default results are not shown");
        $this->assertEquals(0, $crawler->filter('p:contains("I am domain bar")')->count(),
              "The mocked non-default results are not shown");
        $this->assertEquals(1, $crawler->filter('p:contains("I am domain foo")')->count(), 
              "The mocked nnon default results for foo are shown");
        */
    }
    
    private function getAuthenticatedClient()
    {
        return static::createClient(array(), array(
           'PHP_AUTH_USER' => 'center b_social',
           'PHP_AUTH_PW'   => 'password',
        ));
    }

}
