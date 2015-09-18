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

namespace Chill\MainBundle\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Check that a role scope has a scope if required
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class RoleScopeScopePresenceConstraint extends Constraint
{
    
    public $messagePresenceRequired = "The role \"%role%\" require to be associated with "
            . "a scope.";
    
    public function validatedBy()
    {
        return 'role_scope_scope_presence';
    }
    
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
    
}
