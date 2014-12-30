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


class SearchProviderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->search = new SearchProvider();
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
        $this->markTestSkipped('accentued characters must be implemented');
        
        $terms = $this->p('manço bélier aztèque à saloù ê');
        
        $this->assertEquals(array(
           '_domain' => NULL,
           '_default' => 'manco belier azteque a salou e'
        ), $terms);
    }
    
    public function testAccentuedCapitals()
    {
        $this->markTestSkipped('accentued characters must be implemented');
        
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
}