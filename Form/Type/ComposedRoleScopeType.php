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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Chill\MainBundle\Templating\TranslatableStringHelper;
use Chill\MainBundle\Entity\Scope;
use Chill\MainBundle\Security\RoleProvider;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Form to Edit/create a role scope. If the role scope does not
 * exists in the database, he is generated.
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 * @author Champs Libres <info@champs-libres.coop>
 */
class ComposedRoleScopeType extends AbstractType
{
    /**
     *
     * @var string[]
     */
    private $roles = array();
    
    /**
     *
     * @var string[]
     */
    private $rolesWithoutScope = array();
    
    /**
     *
     * @var TranslatableStringHelper
     */
    private $translatableStringHelper;
    
    public function __construct(TranslatableStringHelper $translatableStringHelper,
          RoleProvider $roleProvider)
    {
        $this->roles = $roleProvider->getRoles();
        $this->rolesWithoutScope = $roleProvider->getRolesWithoutScopes();
        $this->translatableStringHelper = $translatableStringHelper;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // store values used in internal function
        $translatableStringHelper = $this->translatableStringHelper;
        $rolesWithoutScopes = $this->rolesWithoutScope;
        
        //build roles
        $values = array();
        foreach ($this->roles as $role) {
            $values[$role] = $role;
        }
        
        $builder
            ->add('role', 'choice', array(
               'choices' => $values,
               'placeholder' => 'Choose amongst roles',
               'choice_attr' => function($role) use ($rolesWithoutScopes) {
                    if (in_array($role, $rolesWithoutScopes)) {
                        return array('data-has-scope' => '0');
                    } else {
                        return array('data-has-scope' => '1');
                    }
               }
            ))
            ->add('scope', 'entity', array(
                'class' => 'ChillMainBundle:Scope',
                'choice_label' => function(Scope $scope) use ($translatableStringHelper) {
                    return $translatableStringHelper->localize($scope->getName());
                },
                'required' => false,
                'data' => null
            ));
            
    }
    
    public function getName()
    {
        return 'composed_role_scope';
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', 'Chill\MainBundle\Entity\RoleScope');
    }

}
