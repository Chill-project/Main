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

namespace Chill\MainBundle\Form\Type\Export;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Chill\MainBundle\Export\ExportManager;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class AggregatorType extends AbstractType
{
    /**
     *
     * @var \ExportManager
     */
    private $exportManager;
    
    public function __construct(ExportManager $exportManager)
    {
        $this->exportManager = $exportManager;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $aggregator = $this->exportManager->getAggregator($options['aggregator_alias']);
        
        $builder
                ->add('order', IntegerType::class, array(
                    'constraints' => array(
                        new Assert\GreaterThanOrEqual(array(
                            'value' => -1
                        )),
                        new Assert\LessThanOrEqual(array(
                            'value' => $options['aggregators_length']
                        ))
                    )
                ));
        
        $filterFormBuilder = $builder->create('form', 'form', array(
            'compound' => true, 'required' => false));
        $aggregator->buildForm($filterFormBuilder);
        
        $builder->add($filterFormBuilder);
        
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('aggregator_alias')
                ->setRequired('aggregators_length')
                ->setAllowedTypes(array(
                    'aggregators_length' => 'int'
                ))
                ->setDefault('compound', true)
                ;
    }
    
}
