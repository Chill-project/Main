<?php

/*
 * Copyright (C) 2015 Champs-Libres <info@champs-libres.coop>
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

namespace Chill\MainBundle\Export;

use Doctrine\ORM\QueryBuilder;

/**
 * Interface for Export.
 * 
 * An export is a class which will initiate a query for an export. 
 *
 * @example Chill\PersonBundle\Export\CountPerson an example of implementation
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
interface ExportInterface extends ExportElementInterface
{
    /**
     * Return the Export's type. This will inform _on what_ export will apply. 
     * Most of the type, it will be a string which references an entity.
     * 
     * Example of types : Chill\PersonBundle\Export\Declarations::PERSON_TYPE
     * 
     * @return string
     */
    public function getType();
    
    /**
     * A description, which will be used in the UI to explain what the export does.
     * This description will be translated.
     * 
     * @return string
     */
    public function getDescription();
    
    /**
     * The initial query, which will be modified by ModifiersInterface 
     * (i.e. AggregatorInterface, FilterInterface).
     * 
     * This query should take into account the `$acl` and restrict result only to
     * what the user is allowed to see. (Do not show personal data the user
     * is not allowed to see).
     * 
     * @param QueryBuilder $qb
     * @param array $requiredModifiers
     * @param array $acl an array where each row as a `center` key containing the Chill\MainBundle\Entity\Center, and `circles` containing the reachable circles
     * TODO : we should add ability to receive data from a form
     */
    public function initiateQuery(QueryBuilder $qb, array $requiredModifiers, $acl);

    
    /**
     * Return wether this export has a form.
     * 
     * @return bool
     */
    public function hasForm();
    
    /**
     * Inform which ModifiersInterface (i.e. AggregatorInterface, FilterInterface)
     * are allowed. The modifiers should be an array of types the _modifier_ apply on 
     * (@see ModifiersInterface::applyOn()).
     * 
     * @return string[]
     */
    public function supportsModifiers();
    
    /**
     * Return the required Role to execute the Export.
     * 
     * @return \Symfony\Component\Security\Core\Role\Role
     * 
     */
    public function requiredRole();
    
    /**
     * Return which formatter type is allowed for this report.
     * 
     * @return string[]
     */
    public function getAllowedFormattersTypes();
    
    /**
     * give the list of keys the current export added to the queryBuilder in 
     * self::initiateQuery
     * 
     * Example: if your query builder will contains `SELECT count(id) AS count_id ...`,
     * this function will return `array('count_id')`.
     * 
     * @param mixed[] $data the data from the export's form (added by self::buildForm)
     */
    public function getQueryKeys($data);
    
    /**
     * Return the results of the query builder.
     * 
     * @param QueryBuilder $qb
     * @param mixed[] $data the data from the export's fomr (added by self::buildForm)
     * @return mixed[] an array of results
     */
    public function getResult(QueryBuilder $qb, $data);
    
        
    /**
     * transform the results to viewable and understable string.
     * 
     * @param string $key The column key, as added in the query
     * @param mixed[] $values The values from the result. Each value is unique
     * @param mixed $data The data from the form
     */
    public function getLabels($key, array $values, $data);

}