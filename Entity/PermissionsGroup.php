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
use Symfony\Component\Validator\Context\ExecutionContextInterface;


/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class PermissionsGroup
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
    private $name;
    
    /**
     *
     * @var \Doctrine\Common\Collections\Collection
     */
    private $roleScopes;
    
    public function __construct()
    {
        $this->roleScopes = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function getId()
    {
        return $this->id;
    }

    /**
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function getRoleScopes()
    {
        return $this->roleScopes;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * 
     * @param RoleScope $roleScope
     */
    public function addRoleScope(RoleScope $roleScope)
    {
        $this->roleScopes->add($roleScope);
    }
    
    /**
     * 
     * @param RoleScope $roleScope
     * @throws \RuntimeException if the roleScope could not be removed.
     */
    public function removeRoleScope(RoleScope $roleScope)
    {
        $result = $this->roleScopes->removeElement($roleScope);
        if ($result === FALSE) {
            throw new \RuntimeException(sprintf("The roleScope '%s' could not be removed, "
                    . "aborting.", spl_object_hash($roleScope)));
        }
    }
    
    /**
     * Test that a role scope is associated only once with the permission group
     * 
     * @param ExecutionContextInterface $context
     */
    public function isRoleScopePresentOnce(ExecutionContextInterface $context) 
    {
        $roleScopesId = array_map(function(RoleScope $roleScope) {
                    return $roleScope->getId();
                }, 
                $this->getRoleScopes()->toArray());
        $countedIds = array_count_values($roleScopesId);
        
        foreach ($countedIds as $id => $nb) {
            if ($nb > 1) {
                $context->buildViolation("A permission is already present "
                        . "for the same role and scope")
                        ->addViolation();
            }
        }
    }


}
