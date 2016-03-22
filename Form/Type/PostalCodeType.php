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

namespace Chill\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Chill\MainBundle\Templating\TranslatableStringHelper;
use Chill\MainBundle\Entity\PostalCode;

/**
 * A form to pick between PostalCode
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 * @author Champs Libres <info@champs-libres.coop>
 */
class PostalCodeType extends AbstractType
{
    /**
     *
     * @var TranslatableStringHelper
     */
    protected $translatableStringHelper;
    
    public function __construct(TranslatableStringHelper $helper)
    {
        $this->translatableStringHelper = $helper;
    }
    
    
    public function getParent()
    {
        return \Symfony\Bridge\Doctrine\Form\Type\EntityType::class;
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        // create a local copy for usage in Closure
        $helper = $this->translatableStringHelper;
        $resolver->setDefault('class', PostalCode::class)
            ->setDefault('choice_label', function(PostalCode $code) use ($helper) {
                return $code->getCode().' '.$code->getName().' ['.
                      $helper->localize($code->getCountry()->getName()).']';
           }
        );
    }
}
