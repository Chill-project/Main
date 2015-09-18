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

namespace Chill\MainBundle\Validation\Validator;

use Chill\MainBundle\Security\RoleProvider;
use Chill\MainBundle\Entity\RoleScope;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Chill\MainBundle\Validation\Constraint\RoleScopeScopePresenceConstraint;
use Psr\Log\LoggerInterface;

/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class RoleScopeScopePresence extends ConstraintValidator
{
    /**
     *
     * @var RoleProvider
     */
    private $roleProvider;
    
    /**
     *
     * @var LoggerInterface
     */
    private $logger;
    
    public function __construct(RoleProvider $roleProvider, LoggerInterface $logger)
    {
        $this->roleProvider = $roleProvider;
        $this->logger = $logger;
    }

    public function validate($value, Constraint $constraint)
    {
        if (! $value instanceof RoleScope) {
            throw new \RuntimeException('The validated object is not an instance of roleScope');
        }
        
        if (! $constraint instanceof RoleScopeScopePresenceConstraint) {
            throw new \RuntimeException('This validator should be used with RoleScopScopePresenceConstraint');
        }
        
        $this->logger->debug('begin validation of a role scope instance');
        
        //check that : the role IS NOT in getRolesWithoutScopes + 
        if (
                !in_array($value->getRole(), $this->roleProvider->getRolesWithoutScopes())
                &&
                $value->getScope() === NULL
                ) {
            $this->context->buildViolation($constraint->messagePresenceRequired)
                    ->setParameter('%role%', $value->getRole())
                    ->addViolation();
            $this->logger->debug('the role scole is not valid. Violation build.');
        } else {
            $this->logger->debug('role scope is valid. Validation finished.');
        }
    }

}
