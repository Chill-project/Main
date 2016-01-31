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

/**
 * Interface for Aggregators. 
 * 
 * Aggregators gather result of a query. Most of the time, it will add
 * a GROUP BY clause.
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
interface AggregatorInterface extends ModifierInterface
{ 
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
     * transform the results to viewable and understable string.
     * 
     * @param string $key The column key, as added in the query
     * @param mixed[] $values The values from the result. Each value is unique
     * @param mixed $data The data from the form
     */
    public function getLabels($key, array $values, $data);

}
