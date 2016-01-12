<?php

/*
 * Chill is a software for social workers
 *
 * Copyright (C) 2014-2015, Champs Libres Cooperative SCRLFS, 
 * <http://www.champs-libres.coop>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Chill\MainBundle\Form\Type\Export;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Chill\MainBundle\Export\ExportManager;
use Chill\MainBundle\Form\Type\Export\FilterType;
use Chill\MainBundle\Form\Type\Export\AggregatorType;

/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class ExportType extends AbstractType
{
    /**
     *
     * @var ExportManager
     */
    protected $exportManager;
    
    public function __construct(ExportManager $exportManager)
    {
        $this->exportManager = $exportManager;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $export = $this->exportManager->getExport($options['export_alias']);
        
        /* this part has not been experimented
        if ($export->hasForm()) {
            $exportBuilder = $builder->create('export', null, array('compound' => true));
            $export->buildForm($exportBuilder);
            $builder->add($exportBuilder);
        } */
        
        //add filters
        $filters = $this->exportManager->getFiltersApplyingOn($export->supportsModifiers());
        $filterBuilder = $builder->create('filters', 'form', array('compound' => true));
        
        foreach($filters as $alias => $filter) {
            $filterBuilder->add($alias, new FilterType($this->exportManager), array(
                'filter_alias' => $alias,
                'label' => $filter->getTitle()
            ));
        }
        
        $builder->add($filterBuilder);
        
        //add aggregators
        $aggregators = iterator_to_array($this->exportManager
                ->getAggregatorsApplyingOn($export->supportsModifiers()));
        $aggregatorBuilder = $builder->create('aggregators', 'form', 
                array('compound' => true));
        $nb = count($aggregators);
        
        foreach($aggregators as $alias => $aggregator) {
            $aggregatorBuilder->add($alias, new AggregatorType($this->exportManager), array(
                'aggregator_alias' => $alias,
                'aggregators_length' => $nb,
                'label' => $aggregator->getTitle()
            ));
        }
        
        $builder->add($aggregatorBuilder);
        
        $builder->add('pick_formatter', PickFormatterType::class, array(
            'export_alias' => $options['export_alias']
        ));
        
    }
    
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(array('export_alias'))
                ->setAllowedTypes('export_alias', array('string'))
                ->setDefault('compound', true)
                ;
        
    }
}
