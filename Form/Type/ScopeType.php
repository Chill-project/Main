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

/**
 * Type to show reachable scope for a given center and role.
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 * @author Champs Libres <info@champs-libres.coop>
 */
class ScopeType extends AbstractType
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


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $reachableScopes = $this->authorizationHelper
              ->getReachableScopes($this->user, $options['role'], 
                    $options['center']);
        
        $choices = array();
        foreach($reachableScopes as $scope) {
            $choices[$scope->getId()] = $this->translatableStringHelper
                  ->localize($scope->getName());
        }
        
        $options['choices'] = $choices;
        
        $builder->create('reachable_scope', 'choice', $options);
    }
    
    public function getParent()
    {
        return 'choice';
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        // configure scope type
        $resolver->addAllowedTypes(array('center', 'role'))
              ->addAllowedValues('center', 
                    array('Chill\MainBundle\Entity\Center'))
              ->addAllowedValues('role', 
                    array('Symfony\Component\Security\Core\Role\Role'))
              ->setRequired(array('center', 'role'))
              ;
        
        //configure parent type
        $resolver->setDefault('data_class', 'Chill\MainBundle\Entity\Scope');
    }
    
    public function getName()
    {
        return 'scope';
    }
}
