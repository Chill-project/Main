<?php

/*
 * Chill is a software for social workers
 * Copyright (C) 2014 Julien Fastré <julien.fastre@champs-libres.coop>
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
use Chill\MainBundle\Templating\TranslatableStringHelper;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Chill\MainBundle\Entity\Country;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Extends choice to allow adding select2 library on widget
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class Select2CountryType extends AbstractType
{
    /**
     * 
     * @var RequestStack
     */
    private $requestStack;
    
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }
    
    public function getName()
    {
        return 'select2_chill_country';
    }
    
    public function getParent()
    {
        return 'select2_entity';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();
        
        $resolver->setDefaults(array(
           'class' => 'Chill\MainBundle\Entity\Country',
           'property' => 'name['.$locale.']'
        ));
    }
}
