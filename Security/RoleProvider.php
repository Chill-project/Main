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

namespace Chill\MainBundle\Security;

/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class RoleProvider
{
    /**
     *
     * @var ProvideRoleInterface[]
     */
    private $providers = array();
    
    /**
     * Add a role provider
     * 
     * @internal This function is called by the dependency injector: it inject provider
     * @param \Chill\MainBundle\Security\ProvideRoleInterface $provider
     */
    public function addProvider(ProvideRoleInterface $provider) 
    {
        $this->providers[] = $provider;
    }
    
    /**
     * 
     * @return string[] the roles as string
     */
    public function getRoles()
    {
        $roles = array();
        foreach ($this->providers as $provider) {
            if ($provider->getRoles() !== NULL) {
                $roles = array_merge($roles, $provider->getRoles());
            }
        }
        
        return $roles;
    }
    
    /**
     * 
     * @return string[] the roles as string
     */
    public function getRolesWithoutScopes()
    {
        $roles = array();
        foreach ($this->providers as $provider) {
            if ($provider->getRolesWithoutScope() !== NULL) {
                $roles = array_merge($roles, $provider->getRolesWithoutScope());
            }
        }
        
        return $roles;
    }
    
}
