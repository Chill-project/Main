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

use Symfony\Component\Form\Test\TypeTestCase;
use Chill\MainBundle\Form\Type\CenterType;
use Chill\MainBundle\Entity\User;
use Chill\MainBundle\Entity\GroupCenter;


/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class CenterTypeTest extends TypeTestCase
{
    /**
     * Test that a user which can reach only one center 
     * render as an hidden field
     */
    public function testUserCanReachSingleCenter()
    {
        //prepare user
        $center = $this->prepareCenter(1, 'center');
        $groupCenter = (new GroupCenter())
                ->setCenter($center)
                ;
        $user = (new User())
                ->addGroupCenter($groupCenter);
        
        $type = $this->prepareType($user);
      
        $this->assertEquals('hidden', $type->getParent());
    }
    
    /**
     * Test that a user which can reach only one center 
     * render as an hidden field
     */
    public function testUserCanReachMultipleSameCenter()
    {
        //prepare user
        $center = $this->prepareCenter(1, 'center');
        $groupCenterA = (new GroupCenter())
                ->setCenter($center)
                ;
        $groupCenterB = (new GroupCenter())
                ->setCenter($center)
                ;
        $user = (new User())
                ->addGroupCenter($groupCenterA)
                ->addGroupCenter($groupCenterB);
        
        $type = $this->prepareType($user);
      
        $this->assertEquals('hidden', $type->getParent());
    }
    
    /**
     * Test that a user which can reach multiple center 
     * make CenterType render as "entity" type.
     */
    public function testUserCanReachMultipleCenters()
    {
        //prepare user
        $centerA = $this->prepareCenter(1, 'centerA');
        $centerB = $this->prepareCenter(2, 'centerB');
        $groupCenterA = (new GroupCenter())
                ->setCenter($centerA)
                ;
        $groupCenterB = (new GroupCenter())
              ->setCenter($centerB)
              ;
        $user = (new User())
                ->addGroupCenter($groupCenterA)
                ->addGroupCenter($groupCenterB)
              ;
        
        $type = $this->prepareType($user);
        
        $this->assertEquals('entity', $type->getParent());
    }
    
    /**
     * prepare a mocked center, with and id and name given
     * 
     * @param int $id
     * @param string $name
     * @return \Chill\MainBundle\Entity\Center 
     */
    private function prepareCenter($id, $name)
    {
        $prophet = new \Prophecy\Prophet;
        
        $prophecyCenter = $prophet->prophesize();
        $prophecyCenter->willExtend('\Chill\MainBundle\Entity\Center');
        $prophecyCenter->getId()->willReturn($id);
        $prophecyCenter->getName()->willReturn($name);
        
        return $prophecyCenter->reveal();
    }
    
    
    /**
     * prepare the type with mocked center transformer and token storage
     * 
     * @param User $user the user for wich the form will be prepared
     * @return CenterType
     */
    private function prepareType(User $user)
    {
        $prophet = new \Prophecy\Prophet;       
        
        //create a center transformer
        $centerTransformerProphecy = $prophet->prophesize();
        $centerTransformerProphecy
              ->willExtend('Chill\MainBundle\Form\Type\DataTransformer\CenterTransformer');
        $transformer = $centerTransformerProphecy->reveal();
        
        $tokenProphecy = $prophet->prophesize();
        $tokenProphecy
                ->willImplement('\Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $tokenProphecy->getUser()->willReturn($user);
        $token = $tokenProphecy->reveal();
        
        $tokenStorageProphecy = $prophet->prophesize();
        $tokenStorageProphecy
                ->willExtend('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage');
        $tokenStorageProphecy->getToken()->willReturn($token);
        $tokenStorage = $tokenStorageProphecy->reveal();
        
        return new CenterType($tokenStorage, $transformer);
    }
    
}
