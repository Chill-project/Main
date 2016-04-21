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

use Symfony\Component\Form\FormBuilderInterface;

/**
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
interface FormatterInterface
{
    const TYPE_TABULAR = 'tabular';
    const TYPE_CSV_LIST = 'csvlist';
    
    public function getType();
    
    public function getName();
    
    /**
     * build a form, which will be used to collect data required for the execution
     * of this formatter.
     * 
     * @uses appendAggregatorForm
     * @param FormBuilderInterface $builder
     * @param String $exportAlias Alias of the export which is being executed. An export gets the data and implements the \Chill\MainBundle\Export\ExportInterface
     * @param Array(String) $aggregatorAliases Array of the aliases of the aggregators. An aggregator do the "group by" on the data. $aggregatorAliases
     */
    public function buildForm(
          FormBuilderInterface $builder, 
          $exportAlias, 
          array $aggregatorAliases
          );
    
    /**
     * Generate a response from the data collected on differents ExportElementInterface
     * 
     * @param mixed[] $result The result, as given by the ExportInterface
     * @param mixed[] $formatterData collected from the current form
     * @param string $exportAlias the id of the current export
     * @param array $filtersData an array containing the filters data. The key are the filters id, and the value are the data
     * @param array $aggregatorsData an array containing the aggregators data. The key are the filters id, and the value are the data
     * @return \Symfony\Component\HttpFoundation\Response The response to be shown
     */
    public function getResponse(
          $result, 
          $formatterData, 
          $exportAlias, 
          array $exportData, 
          array $filtersData, 
          array $aggregatorsData
          );
    
}
