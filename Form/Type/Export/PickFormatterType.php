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

namespace Chill\MainBundle\Form\Type\Export;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Chill\MainBundle\Export\ExportManager;

/**
 * Choose a formatter amongst the available formatters
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class PickFormatterType extends AbstractType
{
    protected $exportManager;
    
    public function __construct(ExportManager $exportManager)
    {
        $this->exportManager = $exportManager;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $export = $this->exportManager->getExport($options['export_alias']);
        $allowedFormatters = $this->exportManager
                ->getFormattersByTypes($export->getAllowedFormattersTypes());
        
        //build choices
        $choices = array();
        foreach($allowedFormatters as $alias => $formatter) {
            $choices[$formatter->getName()] = $alias;
        }
        
        $builder->add('alias', 'choice', array(
            'choices' => $choices,
            'choices_as_values' => true,
            'multiple' => false
        ));
        
        //$builder->get('type')->addModelTransformer($transformer);
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(array('export_alias'));
    }
    
}
