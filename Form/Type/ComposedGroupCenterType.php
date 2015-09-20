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

namespace Chill\MainBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Chill\MainBundle\Entity\PermissionsGroup;
use Chill\MainBundle\Entity\Center;

/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class ComposedGroupCenterType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('permissionsgroup', 'entity', array(
            'class' => 'Chill\MainBundle\Entity\PermissionsGroup',
            'choice_label' => function(PermissionsGroup $group) {
                return $group->getName();
            }
        ))->add('center', 'entity', array(
            'class' => 'Chill\MainBundle\Entity\Center',
            'choice_label' => function(Center $center) {
                return $center->getName();
            }
        ))
            ;
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', 'Chill\MainBundle\Entity\GroupCenter');
    }
    
    public function getName()
    {
        return 'composed_groupcenter';
    }

}
