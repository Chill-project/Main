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

use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;

/**
 * Voter for Chill software. 
 * 
 * This abstract Voter provide generic methods to handle object specific to Chill
 *
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
abstract class AbstractChillVoter extends AbstractVoter implements ChillVoterInterface
{
    
    
}
