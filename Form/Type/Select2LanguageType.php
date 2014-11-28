<?php

/*
 * Chill is a software for social workers
 * Copyright (C) 2014, Champs Libres Cooperative SCRLFS, <http://www.champs-libres.coop>
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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Chill\MainBundle\Entity\Language;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\FormBuilderInterface;
use Chill\MainBundle\Form\Type\DataTransformer\MultipleObjectsToIdTransformer;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Extends choice to allow adding select2 library on widget for languages (multiple)
 */
class Select2LanguageType extends AbstractType
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var ObjectManager
     */
    private $em;
    
    public function __construct(RequestStack $requestStack,ObjectManager $em)
    {
        $this->requestStack = $requestStack;
        $this->em = $em;
    }
    
    public function getName()
    {
        return 'select2_chill_language';
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new MultipleObjectsToIdTransformer($this->em,'Chill\MainBundle\Entity\Language');
        $builder->addModelTransformer($transformer);
    }

    public function getParent()
    {
        return 'select2_choice';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();
        $languages = $this->em->getRepository('Chill\MainBundle\Entity\Language')->findAll();
        $choices = array();

        foreach ($languages as $l) {
            $choices[$l->getId()] = $l->getName()[$locale];
        }

        asort($choices, SORT_STRING | SORT_FLAG_CASE);

        $resolver->setDefaults(array(
           'class' => 'Chill\MainBundle\Entity\Language',
           'choices' => $choices
        ));
    }
}