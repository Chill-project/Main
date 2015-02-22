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

namespace Chill\MainBundle\Routing\Loader;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

/**
 * Import routes from bundles
 * 
 * Routes must be defined in configuration, add an entry 
 * under `chill_main.routing.resources`
 * 
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class ChillRoutesLoader extends Loader
{
    private $routes;
    
   
    
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }
    
    /**
     * {@inheritDoc}
     * 
     * @param type $resource
     * @param type $type
     * @return RouteCollection
     */
    public function load($resource, $type = null)
    {
        $collection = new RouteCollection();
        
        foreach ($this->routes as $resource) {
            $collection->addCollection(
                  $this->import($resource, NULL)
                  );
        }
        
        return $collection;
    }

    /**
     * {@inheritDoc}
     * 
     * @param type $resource
     * @param type $type
     * @return boolean
     */
    public function supports($resource, $type = null)
    {
        return 'chill_routes' === $type;
    }

}
