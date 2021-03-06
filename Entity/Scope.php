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

namespace Chill\MainBundle\Entity;

use Chill\MainBundle\Entity\RoleScope;

/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class Scope
{
    /**
     *
     * @var int
     */
    private $id;
    
    /**
     * translatable names
     * 
     * @var array
     */
    private $name = array();
    
    /**
     *
     * @var \Doctrine\Common\Collections\Collection
     */
    private $roleScopes;
    
    public function __construct()
    {
        $this->roleScopes = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * 
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * 
     * @return array
     */
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    /**
     * 
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRoleScopes()
    {
        return $this->roleScopes;
    }
    
    public function addRoleScope(RoleScope $roleScope)
    {
        $this->roleScopes->add($roleScope);
    }
}
