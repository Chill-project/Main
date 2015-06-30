<?php

/*
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

namespace Chill\MainBundle\Test;

/**
 * a trait to prepare prophecy
 * 
 * **Usage : ** You must set up trait with `setUpTrait` before use
 * and use tearDownTrait after usage.
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 * @codeCoverageIgnore
 */
trait ProphecyTrait
{
        
    /**
     *
     * @var \Prophecy\Prophet()
     */
    private $prophet;
    
    /**
     * 
     * @return \Prophecy\Prophet
     */
    public function getProphet()
    {
        if ($this->prophet === NULL) {
            $this->prophet = new \Prophecy\Prophet();
        }
        
        return $this->prophet;
    }
    
}
