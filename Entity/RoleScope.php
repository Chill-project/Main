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

/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class RoleScope
{
    /**
     *
     * @var int
     */
    private $id;
    
    /**
     *
     * @var string
     */
    private $role;
    
    /**
     *
     * @var Scope 
     */
    private $scope;
    
    public function __construct() {
        $this->new = true;
    }
    
    public function getId()
    {
        return $this->id;
    }

    /**
     * 
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * 
     * @return Scope
     */
    public function getScope()
    {
        return $this->scope;
    }
    
    /**
     * 
     * @param type $role
     * @return \Chill\MainBundle\Entity\RoleScope
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * 
     * @param \Chill\MainBundle\Entity\Scope $scope
     * @return \Chill\MainBundle\Entity\RoleScope
     */
    public function setScope(Scope $scope = null)
    {
        $this->scope = $scope;
        
        return $this;
    }
}
