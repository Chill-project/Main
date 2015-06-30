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

namespace Chill\MainBundle\Security\Authorization;

use Chill\MainBundle\Entity\User;
use Chill\MainBundle\Entity\Center;
use Chill\MainBundle\Entity\HasCenterInterface;
use Chill\MainBundle\Entity\HasScopeInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Role\Role;
use Chill\MainBundle\Entity\Scope;

/**
 * Helper for authorizations. 
 * 
 * Provides methods for user and entities information.
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class AuthorizationHelper
{
    /**
     *
     * @var RoleHierarchyInterface
     */
    protected $roleHierarchy;
    
    protected $existingRoles = array('CHILL_MASTER_ROLE', 'CHILL_PERSON_SEE', 
       'CHILL_PERSON_UPDATE',);
    
    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }
    
    /**
     * Determines if a user is active on this center
     * 
     * @param User $user
     * @param Center $center
     * @return bool
     */
    public function userCanReachCenter(User $user, Center $center)
    {
        foreach ($user->getGroupCenters() as $groupCenter) {
            if ($center->getId() === $groupCenter->getCenter()->getId()) {
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 
     * Determines if the user has access to the given entity.
     * 
     * if the entity implements Chill\MainBundle\Entity\HasScopeInterface,
     * the scope is taken into account.
     * 
     * @param User $user
     * @param HasCenterInterface $entity the entity may also implement HasScopeInterface
     * @param string|Role $attribute
     * @return boolean true if the user has access
     */
    public function userHasAccess(User $user, HasCenterInterface $entity, $attribute)
    {
        
        $center = $entity->getCenter();
        
        if (!$this->userCanReachCenter($user, $center)) {
            return false;
        }
        
        $role = ($attribute instanceof Role) ? $attribute : new Role($attribute);
        
        foreach ($user->getGroupCenters() as $groupCenter){
            //filter on center
            if ($groupCenter->getCenter()->getId() === $entity->getCenter()->getId()) {
                //iterate on permissionGroup
                foreach($groupCenter->getPermissionGroups() as $permissionGroup) {
                    //iterate on roleScopes
                    foreach($permissionGroup->getRoleScopes() as $roleScope) {
                        //check that the role allow to reach the required role
                        if ($this->isRoleReached($role, 
                              new Role($roleScope->getRole()))){
                            //if yes, we have a right on something...
                            // perform check on scope if necessary
                            if ($entity instanceof HasScopeInterface) {
                                $scope = $entity->getScope();
                                if ($scope->getId() === $roleScope
                                      ->getScope()->getId()) {
                                    return true;
                                }
                            } else {
                                return true;
                            }
                        }
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Get reachable Centers for the given user, role,
     * and optionnaly Scope
     * 
     * @param User $user
     * @param Role $role
     * @param null|Scope $scope
     * @return Center[]
     */
    public function getReachableCenters(User $user, Role $role, Scope $scope = null)
    {
        $centers = array();
        
        foreach ($user->getGroupCenters() as $groupCenter){
            //iterate on permissionGroup
            foreach($groupCenter->getPermissionGroups() as $permissionGroup) {
                //iterate on roleScopes
                foreach($permissionGroup->getRoleScopes() as $roleScope) {
                    //check that the role is in the reachable roles
                    if ($this->isRoleReached($role, 
                          new Role($roleScope->getRole()))) {
                        if ($scope === null) {
                            $centers[] = $groupCenter->getCenter();
                            break 2;
                        } else {
                            if ($scope->getId() == $roleScope->getScope()->getId()){
                                $centers[] = $groupCenter->getCenter();
                                break 2;
                            }      
                        }
                    }
                }
            }
        }
        
        return $centers;
    }
    
    /**
     * Return all reachable scope for a given user, center and role
     * 
     * @param User $user
     * @param Role $role
     * @param Center $center
     * @return Scope[]
     */
    public function getReachableScopes(User $user, Role $role, Center $center)
    {
        $scopes = array();
        
        foreach ($user->getGroupCenters() as $groupCenter){
            if ($center->getId() === $groupCenter->getCenter()->getId()) {
                //iterate on permissionGroup
                foreach($groupCenter->getPermissionGroups() as $permissionGroup) {
                    //iterate on roleScopes
                    foreach($permissionGroup->getRoleScopes() as $roleScope) {
                        //check that the role is in the reachable roles
                        if ($this->isRoleReached($role, 
                              new Role($roleScope->getRole()))) {

                            $scopes[] = $roleScope->getScope();
                        }
                    }
                }
            }
        }
        
        return $scopes;
    }
    
    /**
     * Test if a parent role may give access to a given child role
     * 
     * @param Role $childRole The role we want to test if he is reachable
     * @param Role $parentRole The role which should give access to $childRole
     * @return boolean true if the child role is granted by parent role
     */
    protected function isRoleReached(Role $childRole, Role $parentRole)
    {
        $reachableRoles = $this->roleHierarchy
                ->getReachableRoles([$parentRole]);
        
        return in_array($childRole, $reachableRoles);
    }
}
