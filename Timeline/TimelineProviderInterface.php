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
 * Services implementing those interface must be tagged like this :
 * 
 * ```
 * services:
 *    my_timeline:
 *       class: My\Class
 *       tags:
 *          #a first 'person' context :
 *          - { name: timeline, context: person }
 *          # a second 'center' context :
 *          - { name: timeline, context: center }
 * ```
 * 
 * The bundle which will call the timeline will document available context and
 * the arguments provided by the context.
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
interface TimelineProviderInterface
{
    
    /**
     * provide data to build a SQL SELECT query to fetch entities
     * 
     * The TimeLineBuilder will create a full SELECT query and append
     * the query into an UNION of SELECT queries. This permit to fetch
     * all entities from different table in a single query.
     * 
     * The associative array MUST have the following key :
     * - `id` : the name of the id column
     * - `type`: a string to indicate the type
     * - `date`: the name of the datetime column, used to order entities by date
     * - `FROM` (in capital) : the FROM clause. May contains JOIN instructions
     * 
     * Those key are optional:
     * - `WHERE` (in capital) : the WHERE clause. 
     * 
     * Where relevant, the data must be quoted to avoid SQL injection.
     * 
     * `$context` and `$args` are defined by the bundle which will call the timeline
     * rendering. 
     * 
     * @param string $context
     * @param mixed[] $args the argument to the context.
     * @return string[]
     */
    public function fetchQuery($context, array $args);
    
    /**
     * Indicate if the result type may be handled by the service
     * 
     * @param string $type the key present in the SELECT query
     * @return boolean
     */
    public function supportsType($type);

    /**
     * fetch entities from db and indicate how to render them
     * 
     * All id returned by all SELECT queries 
     * (@see TimeLineProviderInterface::fetchQuery) and with the type
     * supported by the provider (@see TimelineProviderInterface::supportsType)
     * will be passed as argument.
     * 
     * The function must return all object with the given id.
     * 
     * @param array $ids
     * @return mixed[] an array of entities
     */
    public function getEntities(array $ids);
    
    /**
     * return an associative array with argument to render the entity
     * in an html template, which will be included in the timeline page
     * 
     * The result must have the following key :
     * 
     * - `template` : the template FQDN
     * - `template_data`: the data required by the template
     * 
     * 
     * Example:
     * 
     * ```
     * array( 
     *    'template'      => 'ChillMyBundle:timeline:template.html.twig',
     *    'template_data' => array(
     *             'accompanyingPeriod' => $entity, 
     *              'person' => $args['person'] 
     *         )
     *    );
     * ```
     * 
     * `$context` and `$args` are defined by the bundle which will call the timeline
     * rendering. 
     * 
     * @param type $entity
     * @param type $context
     * @param array $args
     * @return mixed[]
     */
    public function getEntityTemplate($entity, $context, array $args);
 
}
