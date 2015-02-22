<?php

/*
 * Chill is a software for social workers
 * Copyright (C) 2014 Champs-Libres Coopérative <info@champs-libres.coop>
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

namespace Chill\MainBundle\Search;

/**
 * Throw by search provider when the search name is not found
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class UnknowSearchNameException extends \Exception
{
    
    private $name;
    
    public function __construct($name)
    {
        parent::__construct( "No module search supports with the name $name");
        $this->name = $name;
    }
    
    public function getName()
    {
        return $this->name;
    }
}
