<?php

/*
 * Chill is a software for social workers
 * Copyright (C) 2015 Champs-Libres Coopérative <info@champs-libres.coop>
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

namespace Chill\MainBundle\Tests\Routing\Loader;

use Chill\MainBundle\Routing\Loader\ChillRoutesLoader;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;


/**
 * Test the route loader
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class RouteLoaderTest extends KernelTestCase
{
    private $router;
    
    public function setUp()
    {
        static::bootKernel();
        $this->router = static::$kernel->getContainer()->get('router');
    }
    
    /**
     * Test that the route loader loads at least homepage
     */
    public function testRouteFromMainBundleAreLoaded()
    {
        $homepage = $this->router->getRouteCollection()->get('chill_main_homepage');
        
        $this->assertNotNull($homepage);
    }
    
}
