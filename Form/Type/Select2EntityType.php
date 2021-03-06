<?php

/*
 * Chill is a software for social workers
 * Copyright (C) 2014 Champs-Libres Coopérative <info@champs-libres.coop>
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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Extends choice to allow adding select2 library on widget
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class Select2EntityType extends AbstractType
{
    public function getName()
    {
        return 'select2_entity';
    }
    
    public function getParent()
    {
        return 'entity';
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->replaceDefaults(
              array('attr' => array('class' => 'select2 '))
              );
    }
}
