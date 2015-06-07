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

/**
 * Helper for authorizations. 
 * 
 * Provides methods for user and entities information.
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class AuthorizationHelper
{
    // I wonder if this function should not be moved into the class user itself --JF
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
}
