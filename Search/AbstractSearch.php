<?php


/*
 *
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

namespace Chill\MainBundle\Search;

use Chill\MainBundle\Search\SearchInterface;

/**
 * This class implements abstract search with most common responses.
 * 
 * you should use this abstract class instead of SearchInterface : if the signature of
 * search interface change, the generic method will be implemented here.
 * 
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 *
 */
abstract class AbstractSearch implements SearchInterface
{
    
}