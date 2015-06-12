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
use Chill\MainBundle\Entity\RoleScope;
use Chill\MainBundle\Entity\Scope;
use Chill\MainBundle\Entity\GroupCenter;
use Chill\MainBundle\Entity\PermissionsGroup;


/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class CenterTypeTest extends TypeTestCase
{
    
    public function testUserCanReachSingleCenter()
    {
        //prepare user
        $roleScope = (new RoleScope())
                ->setRole('CHILL_DUMMY_ROLE')
                ->setScope((new Scope())->setName('default'))
                ;
        $groupCenter = (new GroupCenter())
                ->setCenter($this->prepareCenter(1, 'center'))
                ;
        $user = (new User())
                ->addGroupCenter($groupCenter);
        
        $type = $this->prepareType($user);
        
        $this->assertEquals('hidden', $type->getParent());
    }
    
    /**
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
     * 
     * @param User $user
     * @return CenterType
     */
    private function prepareType(User $user)
    {
        $prophet = new \Prophecy\Prophet;
        
        
        
        $centerTransformerProphecy = $prophet->prophesize();
        $centerTransformerProphecy->willExtend('Chill\MainBundle\Form\Type\DataTransformer\CenterTransformer')
                //->read('transform')->willReturn(1)
                //->read('reverseTransform')->willReturn($center)
                ;
        $centerTransformer = $centerTransformerProphecy->reveal();
        
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
        
        return new CenterType($tokenStorage, $centerTransformer);
    }
    
}
