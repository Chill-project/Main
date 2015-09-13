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
 * Declare role
 * 
 * The role are added to the configuration at compile time.
 * 
 * The implemented object must be declared as a service and tagged as 
 * 
 * <pre>
 * my_role_declaration:
 *    # ...
 *    tags:
 *       - { name: chill.role }
 * </pre>
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
interface ProvideRoleInterface
{
    /**
     * return an array of role provided by the object
     * 
     * @return string[] array of roles (as string)
     */
    public function getRoles();
    
    /**
     * return roles which doesn't need 
     * 
     * @return string[] array of roles without scopes
     */
    public function getRolesWithoutScope();
}
