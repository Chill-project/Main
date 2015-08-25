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

use Chill\MainBundle\Entity\Center;
use Chill\MainBundle\Entity\PermissionsGroup;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class GroupCenter
{
    /**
     *
     * @var int
     */
    private $id;
    
    /**
     *
     * @var Center
     */
    private $center;
    
    /**
     *
     * @var Collection 
     */
    private $users;
    
    /**
     *
     * @var PermissionsGroup
     */
    private $permissionsGroup;
    
    public function __construct()
    {
        $this->permissionGroups = new ArrayCollection();
        $this->users = new ArrayCollection();
    }
    
    public function getId()
    {
        return $this->id;
    }

    /**
     * 
     * @return Center
     */
    public function getCenter()
    {
        return $this->center;
    }

    /**
     * 
     * @return PermissionsGroup[]
     * @deprecated
     */
    public function getPermissionGroups()
    {
        trigger_error("This function is deprecated, association "
                . "between PermissionGroup and GroupCenter has changed,"
                . "see #578", E_USER_DEPRECATED);
        
        return array($this->permissionGroup);
    }

    /**
     * 
     * @param Center $center
     * @return \Chill\MainBundle\Entity\GroupCenter
     */
    public function setCenter(Center $center)
    {
        $this->center = $center;
        return $this;
    }

    /**
     * 
     * @param PermissionsGroup $permission
     * @return \Chill\MainBundle\Entity\GroupCenter
     * @deprecated
     */
    public function addPermissionGroup(PermissionsGroup $permission)
    {
        trigger_error("This function is deprecated, association "
                . "between PermissionGroup and GroupCenter has changed,"
                . "see #578", E_USER_DEPRECATED);
        
        $this->setPermissionsGroup($permission);
        
        return $this;
    }
    
    public function getUsers()
    {
        return $this->users;
    }
    
    /**
     * 
     * @return PermissionGroup
     */
    public function getPermissionsGroup()
    {
        return $this->permissionsGroup;
    }

    /**
     * 
     * @param \Chill\MainBundle\Entity\PermissionsGroup $permissionGroup
     * @return \Chill\MainBundle\Entity\GroupCenter
     */
    public function setPermissionsGroup(PermissionsGroup $permissionsGroup)
    {
        $this->permissionsGroup = $permissionsGroup;
        return $this;
    }





}
