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

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Chill\MainBundle\Security\Authorization\AuthorizationHelper;
use Chill\MainBundle\Templating\TranslatableStringHelper;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Chill\MainBundle\Entity\User;
use Chill\MainBundle\Entity\Center;
use Symfony\Component\Security\Core\Role\Role;
use Chill\MainBundle\Form\Type\DataTransformer\ScopeTransformer;

/**
 * Trait to add an input with reachable scope for a given center and role.
 * 
 * Example usage :
 * 
 * ```
 * class AbcType extends Symfony\Component\Form\AbstractType
 * {
 *     use AppendScopeChoiceTypeTrait;
 *     protected $authorizationHelper;
 *     protected $translatableStringHelper;
 *     protected $user;
 * 
 *     public function __construct(AuthorizationHelper $helper,
 *       TokenStorageInterface $tokenStorage, 
 *       TranslatableStringHelper $translatableStringHelper)
 *     {
 *         $this->authorizationHelper = $helper;
 *         $this->user = $tokenStorage->getToken()->getUser();
 *         $this->translatableStringHelper = $translatableStringHelper;
 *     }
 * 
 *     public function buildForm(FormBuilder $builder, array $options)
 *     {
 *           // ... add your form there
 *     
 *         // append the scope using FormEvents: PRE_SET_DATA
 *         $this->appendScopeChoices($builder, $options['role'], 
 *              $options['center'], $this->user, $this->authorizationHelper, 
 *              $this->translatableStringHelper);
 *      }
 * 
 *      public function configureOptions(OptionsResolver $resolver)
 *      {
 *              // ... add your options
 *              
 *         // add an option 'role' and 'center' to your form (optional)
 *         $this->appendScopeChoicesOptions($resolver);
 *      }
 * 
 *  }
 * ```
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 * @author Champs Libres <info@champs-libres.coop>
 */
trait AppendScopeChoiceTypeTrait 
{
    /**
     * Append a scope choice field, with the scopes reachable by given
     * user for the given role and center.
     * 
     * The field is added on event FormEvents::PRE_SET_DATA
     * 
     * @param FormBuilderInterface $builder
     * @param Role $role
     * @param Center $center
     * @param User $user
     * @param AuthorizationHelper $authorizationHelper
     * @param TranslatableStringHelper $translatableStringHelper
     * @param string $name
     */
    protected function appendScopeChoices(FormBuilderInterface $builder, 
            Role $role, Center $center, User $user,
            AuthorizationHelper $authorizationHelper, 
            TranslatableStringHelper $translatableStringHelper,
            ObjectManager $om, $name = 'scope')
    {
        $reachableScopes = $authorizationHelper
              ->getReachableScopes($user, $role, $center);
        
        $choices = array();
        foreach($reachableScopes as $scope) {
            $choices[$scope->getId()] = $translatableStringHelper
                  ->localize($scope->getName());
        }
        
        $dataTransformer = new ScopeTransformer($om);
        
        $builder->addEventListener(FormEvents::PRE_SET_DATA, 
                function (FormEvent $event) use ($choices, $name, $dataTransformer, $builder) {
                    $form = $event->getForm();
                    $form->add(
                            $builder
                            ->create($name, 'choice', array(
                                'choices' => $choices,
                                'data_class' => 'Chill\MainBundle\Entity\Scope',
                                'auto_initialize' => false
                                )
                            )
                            ->addModelTransformer($dataTransformer)
                            ->getForm()
                    );
                });
    }
    
    /**
     * Append a `role` and `center` option to the form.
     * 
     * The allowed types are : 
     * - Chill\MainBundle\Entity\Center for center
     * - Symfony\Component\Security\Core\Role\Role for role
     * 
     * @param OptionsResolver $resolver
     */
    public function appendScopeChoicesOptions(OptionsResolver $resolver)
    {
        $resolver
              ->setRequired(array('center', 'role'))
              ->setAllowedTypes(array(
                  'center' => 'Chill\MainBundle\Entity\Center',
                  'role'   => 'Symfony\Component\Security\Core\Role\Role'
              ))
              ;
    }
    
}
