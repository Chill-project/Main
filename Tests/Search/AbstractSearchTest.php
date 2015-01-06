<?php

/*
 * Chill is a suite of a modules, Chill is a software for social workers
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

namespace Chill\MainBundle\Tests\Search;

/**
 * Description of AbstractSearch
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class AbstractSearchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Chill\MainBundle\Search\AbstractSearch
     */
    private $stub;
    
    public function setUp()
    {
        $this->stub = $this->getMockForAbstractClass('Chill\MainBundle\Search\AbstractSearch');
    }
    
    public function testParseDateRegular()
    {
        
        //var_dump($this->stub);
        $date = $this->stub->parseDate('2014-05-01');

        $this->assertEquals('2014', $date->format('Y'));
        $this->assertEquals('05', $date->format('m'));
        $this->assertEquals('01', $date->format('d'));
    }
    
    public function testRecompose()
    {
        $this->markTestSkipped();
    }
}
