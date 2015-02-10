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

namespace Chill\MainBundle\Timeline;

/**
 * Interface for service providing info to timeline
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
interface TimelineProviderInterface
{
    /**
     * Indicate if the result id may be handled by the service
     * 
     * @param string $key the key present in the SELECT query
     * @return boolean
     */
    public function supportsKey($key);

    /**
     * fetch entities from db and indicate how to render them
     * 
     * @param array $ids
     * @return array[] an array of an associative array. 'template' will indicate a template name on how to render the entity, 'entity' will be the arguments of the template
     */
    public function getEntities(array $ids);

    /**
     * provide a SQL SELECT query to fetch entities id
     * 
     * @return string an SQL SELECT query which will fetch ID. The query must have and id (INT), a key (STRING), and a datetime to order results
     */
    public function fetchUnion($context, array $args); 
}
