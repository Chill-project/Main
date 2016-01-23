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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Chill\MainBundle\Export\ExportManager;
use Chill\MainBundle\Security\Authorization\AuthorizationHelper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Chill\MainBundle\Entity\Center;

/**
 * Pick centers amongst available centers for the user
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 * @author Champs Libres <info@champs-libres.coop>
 */
class PickCenterType extends AbstractType
{
    /**
     *
     * @var \Symfony\Component\Security\Core\User\UserInterface
     */
    protected $user;
    
    /**
     *
     * @var ExportManager
     */
    protected $exportManager;
    
    /**
     *
     * @var AuthorizationHelper
     */
    protected $authorizationHelper;
    
    public function __construct(TokenStorageInterface $tokenStorage, 
          ExportManager $exportManager, AuthorizationHelper $authorizationHelper)
    {
        $this->exportManager = $exportManager;
        $this->user = $tokenStorage->getToken()->getUser();
        $this->authorizationHelper = $authorizationHelper;
    }
    
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('export_alias')
              ;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $export = $this->exportManager->getExport($options['export_alias']);
        $centers = $this->authorizationHelper->getReachableCenters($this->user, 
              $export->requiredRole());
        
        $builder->add('c', EntityType::class, array(
           'class' => 'ChillMainBundle:Center',
           'query_builder' => function(EntityRepository $er) use ($centers) {
                $qb = $er->createQueryBuilder('c');
                $ids = array_map(function(Center $el) { return $el->getId(); }, 
                      $centers);
                return $qb->where($qb->expr()->in('c.id', $ids));
           }, 
           'multiple' => true,
           'expanded' => false,
           'choice_label' => function(Center $c) { return $c->getName(); },
           'data' => $centers
        ));
        
    }
}
