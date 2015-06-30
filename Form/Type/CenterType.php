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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Chill\MainBundle\Entity\Center;
use Chill\MainBundle\Form\Type\DataTransformer\CenterTransformer;

/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class CenterType extends AbstractType
{
    /**
     * The user linked with this type. 
     *
     * @var \Chill\MainBundle\Entity\User
     */
    protected $user;
    
    /**
     * associative array where keys are center.id and
     * value are center objects
     *
     * @var Center[]
     */
    protected $reachableCenters = array();
    
    /**
     *
     * @var CenterTransformer
     */
    protected $transformer;
    
    public function __construct(TokenStorage $tokenStorage, 
            CenterTransformer $transformer)
    {
        $this->user = $tokenStorage->getToken()->getUser();
        $this->transformer = $transformer;
        $this->prepareReachableCenterByUser();
    }

    public function getName()
    {
        return 'center';
    }
    
    /**
     * return a 'hidden' field if only one center is available.
     * 
     * Return a 'choice' field if more than one center is available.
     * 
     * @return string
     * @throws \RuntimeException if the user is not associated with any center
     */
    public function getParent()
    {
        $nbReachableCenters = count($this->reachableCenters);
        
        if ($nbReachableCenters === 0) {
            throw new \RuntimeException("The user is not associated with "
                    . "any center. Associate user with a center");
        } elseif ($nbReachableCenters === 1) {
            return 'hidden';
        } else {
            return 'entity';
        }
    }
    
    /**
     * configure default options, i.e. add choices if user can reach multiple
     * centers.
     * 
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {        
        if (count($this->reachableCenters) > 1) {
            $resolver->setDefault('class', 'Chill\MainBundle\Entity\Center');
        } 
    }
    
    /**
     * add a data transformer if user can reach only one center
     * 
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->getParent() === 'hidden') {
            $builder->addModelTransformer($this->transformer);
        }
    }
    
    /**
     * populate reachableCenters as an associative array where
     * keys are center.id and value are center entities.
     * 
     */
    private function prepareReachableCenterByUser()
    {
        $groupCenters = $this->user->getGroupCenters();
        
        foreach ($groupCenters as $groupCenter) {
            
            $center = $groupCenter->getCenter();
            
            if (!array_key_exists($center->getId(), 
                    $this->reachableCenters)) {
                $this->reachableCenters[$center->getId()] = $center;
            }
        }
    }

}
