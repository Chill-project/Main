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
use Symfony\Component\Form\FormBuilderInterface;

/**
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
interface ExportInterface extends ExportElementInterface
{
    
    public function getType();
    
    public function getDescription();
    
    public function getTitle();
    
    /**
     * 
     * @param QueryBuilder $qb
     * @param array $requiredModifiers
     * @param array $acl an array where each row as a `center` key containing the Chill\MainBundle\Entity\Center, and `circles` containing the reachable circles
     * TODO : we should add ability to receive data from a form
     */
    public function initiateQuery(QueryBuilder $qb, array $requiredModifiers, $acl);
    
    public function buildForm(FormBuilderInterface $builder);
    
    /**
     * @return bool
     */
    public function hasForm();
    
    public function supportsModifiers();

}
