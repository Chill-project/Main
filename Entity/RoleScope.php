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
 * Note: changes are tracked on this class.
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
    
    /**
     * track changes. 
     *
     * @var bool
     */
    private $hasChanges = false;
    
    /**
     * disabled change tracking if the roleScope is new.
     *
     * @var bool
     */
    private $new = false;
    
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
        if ($role !== $this->role) {
            $this->role = $role;
            $this->registerChange();
        }
        
        return $this;
    }

    /**
     * 
     * @param \Chill\MainBundle\Entity\Scope $scope
     * @return \Chill\MainBundle\Entity\RoleScope
     */
    public function setScope(Scope $scope)
    {
        if ($scope !== $this->scope) {
            $this->scope = $scope;
            $this->registerChange();
        }
        
        return $this;
    }
    
    /**
     * set the class as changed.
     * 
     * If the class is new (never persisted by doctrine), changes are always false.
     */
    private function registerChange()
    {
        if (! $this->new) {
            $this->hasChanges = true;
        }
    }
    
    /**
     * return true if the class has changed during his lifetime.
     * 
     * Always false if the class is new (= not created from doctrine)
     * 
     * @return bool
     */
    public function hasChanges()
    {
        return $this->hasChanges;
    }


}
