<?php

/*
 * Copyright (C) 2016 Champs-Libres <info@champs-libres.coop>
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
 * Modifiers modify the export's query.
 * 
 * Known subclasses : AggregatorInterface and FilterInterface
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
interface ModifierInterface extends ExportElementInterface
{
    /**
     * The role required for executing this Modifier
     * 
     * If null, will used the ExportInterface::requiredRole role from
     * the current executing export.
     * 
     * @return NULL|\Symfony\Component\Security\Core\Role\Role A role required to execute this ModifiersInterface
     */
    public function addRole();
    
    /**
     * On which type of Export this ModifiersInterface may apply.
     * 
     * @return string the type on which the Modifiers apply
     */
    public function applyOn();
    
    /**
     * Alter the query initiated by the export, to add the required statements 
     * (`GROUP BY`, `SELECT`, `WHERE`)
     * 
     * @param QueryBuilder $qb the QueryBuilder initiated by the Export (and eventually modified by other Modifiers)
     * @param mixed[] $data the data from the Form (builded by buildForm)
     */
    public function alterQuery(QueryBuilder $qb, $data);
}
