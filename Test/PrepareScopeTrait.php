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
 * A trait to prepare center
 * 
 * **Usage :** You must set up trait with `setUpTrait` before use
 * and use tearDownTrait after usage.
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 * @codeCoverageIgnore
 */
trait PrepareScopeTrait
{
    use ProphecyTrait;
    
    /**
     * prepare a mocked center, with and id and name given
     * 
     * @param int $id
     * @param string $name
     * @return \Chill\MainBundle\Entity\Center 
     */
    protected function prepareScope($id, $name)
    {
        
        $scope = $this->getProphet()->prophesize();
        $scope->willExtend('\Chill\MainBundle\Entity\Scope');
        $scope->getId()->willReturn($id);
        $scope->getName()->willReturn($name);
        
        return $scope->reveal();
    }
}
