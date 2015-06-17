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

use Chill\MainBundle\Entity\User;
use Chill\MainBundle\Entity\GroupCenter;
use Chill\MainBundle\Entity\RoleScope;
use Chill\MainBundle\Entity\PermissionsGroup;

/**
 * A trait to prepare user with permission. May be used 
 * within tests.
 * 
 * **Usage : ** You must set up trait with `setUpTrait` before use
 * and use tearDownTrait after usage.
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 * @codeCoverageIgnore
 */
trait PrepareUserTrait
{
    
    /**
     * prepare a user with correct permissions
     * 
     * Example of permissions:
     * ```
     * array(
     *    array( 'center' => $centerA, 'permissionsGroup' => array(
     *        [ 'role' => 'CHILL_REPORT_SEE', 'scope' => $scopeA]
     *    ),
     *    array( 'center' => $centerB, 'permissionsGroup' => array(
     *        [ 'role' => 'CHILL_ACTIVITY_UPDATE', 'scope' => $scopeB]
     *    )
     * )
     * ```
     * Scope must be an int. Scope created have this int as id, and the 
     * int converted to string as name.
     * 
     * 
     * @param array $permissions an array of permissions, with key 'center' for the center and key 'attrs' for an array of  ['role' => (string), 'scope' => (int)]
     * @return User
     * @throws \LogicException if the trait is not set up
     */
    protected function prepareUser(array $permissions)
    {
        $user = new User();
        
        foreach ($permissions as $permission) {
            $groupCenter = (new GroupCenter())
                    ->setCenter($permission['center']);
            $permissionGroup = new PermissionsGroup();
            foreach ($permission['permissionsGroup'] as $pg) {
                
                $roleScope = (new RoleScope())
                        ->setRole($pg['role'])
                        ->setScope($pg['scope']);
                        ;
                $permissionGroup->addRoleScope($roleScope);
                $groupCenter->addPermissionGroup($permissionGroup);
            }
            $user->addGroupCenter($groupCenter);
        }
        
        return $user;
    }
}
