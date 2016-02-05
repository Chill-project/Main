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
    
    const FILTER_KEY = 'filters';
    const AGGREGATOR_KEY = 'aggregators';
    const PICK_FORMATTER_KEY = 'pick_formatter';
    const EXPORT_KEY = 'export';
    
    public function __construct(ExportManager $exportManager)
    {
        $this->exportManager = $exportManager;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $export = $this->exportManager->getExport($options['export_alias']);
        
        $exportBuilder = $builder->create(self::EXPORT_KEY, null, array('compound' => true));
        //if ($export->hasForm()) {
            $export->buildForm($exportBuilder);
        //} 
        $builder->add($exportBuilder);
        
        //add filters
        $filters = $this->exportManager->getFiltersApplyingOn($export, $options['picked_centers']);
        $filterBuilder = $builder->create(self::FILTER_KEY, 'form', array('compound' => true));
        
        foreach($filters as $alias => $filter) {
            $filterBuilder->add($alias, new FilterType($this->exportManager), array(
                'filter_alias' => $alias,
                'label' => $filter->getTitle()
            ));
        }
        
        $builder->add($filterBuilder);
        
        //add aggregators
        $aggregators = $this->exportManager
                ->getAggregatorsApplyingOn($export, $options['picked_centers']);
        $aggregatorBuilder = $builder->create(self::AGGREGATOR_KEY, 'form', 
                array('compound' => true));
        
        foreach($aggregators as $alias => $aggregator) {
            $aggregatorBuilder->add($alias, new AggregatorType($this->exportManager), array(
                'aggregator_alias' => $alias,
                'label' => $aggregator->getTitle()
            ));
        }
        
        $builder->add($aggregatorBuilder);
        
        $builder->add(self::PICK_FORMATTER_KEY, PickFormatterType::class, array(
            'export_alias' => $options['export_alias']
        ));
        
    }
    
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(array('export_alias', 'picked_centers'))
                ->setAllowedTypes('export_alias', array('string'))
                ->setDefault('compound', true)
                ;
        
    }
}
