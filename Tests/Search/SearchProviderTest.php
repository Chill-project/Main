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
    
    public function testDomain()
    {
        $term = $this->p("@person is not my name");
        
        $this->assertEquals('person', $term['_domain']);
    }
    
    /**
     * @expectedException Chill\MainBundle\Search\ParsingException
     */
    public function testMultipleDomainError()
    {
        $term = $this->p("@person @report");
        var_dump($term);
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