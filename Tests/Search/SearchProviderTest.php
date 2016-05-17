<?php
/*
 *
 * Copyright (C) 2014, Champs Libres Cooperative SCRLFS, <http://www.champs-libres.coop>
 *
 * This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Chill\MainBundle\Test\Search;

use Chill\MainBundle\Search\SearchProvider;
use Chill\MainBundle\Search\SearchInterface;


class SearchProviderTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     *
     * @var SearchProvider 
     */
    private $search;
    
    public function setUp()
    {
        $this->search = new SearchProvider();
        
        //add a default service
        $this->addSearchService(
              $this->createDefaultSearchService('I am default', 10), 'default'
              );
        //add a domain service
        $this->addSearchService(
              $this->createNonDefaultDomainSearchService('I am domain bar', 20, FALSE), 'bar'
              );
    }
    
    /**
     * @expectedException \Chill\MainBundle\Search\UnknowSearchNameException
     */
    public function testInvalidSearchName()
    {
        $this->search->getByName("invalid name");
    }
    
    public function testSimplePattern()
    {
        $terms = $this->p("@person birthdate:2014-01-02 name:(my name) is not my name");
        
        $this->assertEquals(array(
           '_domain' => 'person',
           'birthdate' => '2014-01-02',
           '_default' => 'is not my name',
           'name' => 'my name'
        ), $terms);
    }
    
    public function testWithoutDomain()
    {
        $terms = $this->p('foo:bar residual');
        
        $this->assertEquals(array(
           '_domain' => null,
           'foo' => 'bar',
           '_default' => 'residual'
        ), $terms);
    }
    
    public function testWithoutDefault()
    {
        $terms = $this->p('@person foo:bar');
        
        $this->assertEquals(array(
           '_domain' => 'person',
           'foo' => 'bar',
           '_default' => ''
        ), $terms);
    }
    
    public function testCapitalLetters()
    {
        $terms = $this->p('Foo:Bar LOL marCi @PERSON');
        
        $this->assertEquals(array(
           '_domain' => 'person',
           '_default' => 'lol marci',
           'foo' => 'bar'
        ), $terms);
    }
    
    /**
     * @expectedException Chill\MainBundle\Search\ParsingException
     */
    public function testMultipleDomainError()
    {
        $term = $this->p("@person @report");
    }
    
    public function testDoubleParenthesis()
    {
        $terms = $this->p("@papamobile name:(my beautiful name) residual "
              . "surname:(i love techno)");
        
        $this->assertEquals(array(
           '_domain' => 'papamobile',
           'name' => 'my beautiful name',
           '_default' => 'residual',
           'surname' => 'i love techno'
        ), $terms);
    }
    
    public function testAccentued()
    {
        //$this->markTestSkipped('accentued characters must be implemented');
        
        $terms = $this->p('manço bélier aztèque à saloù ê');
        
        $this->assertEquals(array(
           '_domain' => NULL,
           '_default' => 'manco belier azteque a salou e'
        ), $terms);
    }
    
    public function testAccentuedCapitals()
    {
        //$this->markTestSkipped('accentued characters must be implemented');
        
        $terms = $this->p('MANÉÀ oÛ lÎ À');
        
        $this->assertEquals(array(
           '_domain' => null,
           '_default' => 'manea ou li a'
        ), $terms);
    }
    
    public function testTrimInParenthesis()
    {
        $terms = $this->p('foo:(bar     )');
        
        $this->assertEquals(array(
           '_domain' => null,
           'foo' => 'bar',
           '_default' => ''
        ), $terms);
    }
    
    public function testTrimInDefault()
    {
        $terms = $this->p('  foo bar     ');
        
        $this->assertEquals(array(
           '_domain' => null,
           '_default' => 'foo bar'
        ), $terms);
    }
    
    public function testArgumentNameWithTrait()
    {
        $terms = $this->p('date-from:2016-05-04');
        
        $this->assertEquals(array(
            '_domain' => null,
            'date-from' => '2016-05-04',
            '_default' => ''
        ), $terms);
    }
    
    /**
     * Test the behaviour when no domain is provided in the search pattern : 
     * the default search should be enabled
     */
    public function testSearchResultDefault()
    {
        $response = $this->search->getSearchResults('default search');

        //$this->markTestSkipped();
        
        $this->assertEquals(array(
           "I am default"
        ), $response);      
    }
    
    /**
     * @expectedException \Chill\MainBundle\Search\UnknowSearchDomainException
     */
    public function testSearchResultDomainUnknow()
    {
        $response = $this->search->getSearchResults('@unknow domain');

        //$this->markTestSkipped();
        
    }
    
    public function testSearchResultDomainSearch()
    {
        //add a search service which will be supported
        $this->addSearchService(
              $this->createNonDefaultDomainSearchService("I am domain foo", 100, TRUE), 'foo'
              );
        
        $response = $this->search->getSearchResults('@foo default search');
        
        $this->assertEquals(array(
           "I am domain foo"
        ), $response);
        
    }
    
    public function testSearchWithinSpecificSearchName()
    {
        //add a search service which will be supported
        $this->addSearchService(
              $this->createNonDefaultDomainSearchService("I am domain foo", 100, TRUE), 'foo'
              );
        
        $response = $this->search->getResultByName('@foo search', 'foo');
        
        $this->assertEquals('I am domain foo', $response);
        
    }
    
    /**
     * @expectedException \Chill\MainBundle\Search\ParsingException
     */
    public function testSearchWithinSpecificSearchNameInConflictWithSupport()
    {
        $response = $this->search->getResultByName('@foo default search', 'bar');
        
    }
    
    /**
     * shortcut for executing parse method
     * 
     * @param unknown $pattern
     * @return string[]
     */
    private function p($pattern)
    {
        return $this->search->parse($pattern);
    }
    
    /**
     * Add a search service to the chill.main.search_provider
     * 
     * Useful for mocking the SearchInterface
     * 
     * @param SearchInterface $search
     * @param string $name
     */
    private function addSearchService(SearchInterface $search,  $name)
    {
        $this->search
              ->addSearchService($search, $name);
    }
    
    private function createDefaultSearchService($result, $order)
    {
        $mock = $this
              ->getMockForAbstractClass('Chill\MainBundle\Search\AbstractSearch');
        
        //set the mock as default
        $mock->expects($this->any())
              ->method('isActiveByDefault')
              ->will($this->returnValue(TRUE));
        
        $mock->expects($this->any())
              ->method('getOrder')
              ->will($this->returnValue($order));
        
        //set the return value
        $mock->expects($this->any())
              ->method('renderResult')
              ->will($this->returnValue($result));
        
        return $mock;
    }
    
    private function createNonDefaultDomainSearchService($result, $order, $domain)
    {
        $mock = $this
              ->getMockForAbstractClass('Chill\MainBundle\Search\AbstractSearch');
        
        //set the mock as default
        $mock->expects($this->any())
              ->method('isActiveByDefault')
              ->will($this->returnValue(FALSE));
        
        $mock->expects($this->any())
              ->method('getOrder')
              ->will($this->returnValue($order));
        
        $mock->expects($this->any())
              ->method('supports')
              ->will($this->returnValue($domain));
        
        //set the return value
        $mock->expects($this->any())
              ->method('renderResult')
              ->will($this->returnValue($result));
        
        return $mock;
    }
}