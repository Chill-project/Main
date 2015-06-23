<?php

/*
 * Chill is a software for social workers
 * Copyright (C) 2015 Champs Libres <info@champs-libres.coop>
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
use Chill\MainBundle\Security\Authorization\AuthorizationHelper;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Chill\MainBundle\Templating\TranslatableStringHelper;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

/**
 * Type to show reachable scope for a given center and role.
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 * @author Champs Libres <info@champs-libres.coop>
 */
abstract class AbstractHasScopeType extends AbstractType
{
    /**
     *
     * @var AuthorizationHelper
     */
    protected $authorizationHelper;
    
    /**
     *
     * @var TranslatableStringHelper
     */
    protected $translatableStringHelper;
    
    /**
     *
     * @var \Chill\MainBundle\Entity\User
     */
    protected $user;
    
    public function __construct(AuthorizationHelper $helper,
        TokenStorageInterface $tokenStorage, 
        TranslatableStringHelper $translatableStringHelper)
    {
        $this->authorizationHelper = $helper;
        $this->user = $tokenStorage->getToken()->getUser();
        $this->translatableStringHelper = $translatableStringHelper;
    }


    protected function appendScopeChoices(FormBuilderInterface $builder, 
            array $options, $name = 'scope')
    {
        $reachableScopes = $this->authorizationHelper
              ->getReachableScopes($this->user, $options['role'], 
                    $options['center']);
        
        $choices = array();
        foreach($reachableScopes as $scope) {
            $choices[$scope->getId()] = $this->translatableStringHelper
                  ->localize($scope->getName());
        }
        
        $builder->addEventListener(FormEvents::PRE_SET_DATA, 
                function (FormEvent $event) use ($choices, $name) {
                    $form = $event->getForm();
                    $form->add($name, 'choice', array(
                        'choices' => $choices 
                    ));
                
                });
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        // configure scope type
        $resolver
              ->setRequired(array('center', 'role'))
              ->setAllowedTypes(array(
                  'center' => 'Chill\MainBundle\Entity\Center',
                  'role'   => 'Symfony\Component\Security\Core\Role\Role'
              ))
              ;
        
        //configure parent type
        $resolver->setDefault('data_class', 'Chill\MainBundle\Entity\Scope');
    }
    
}
