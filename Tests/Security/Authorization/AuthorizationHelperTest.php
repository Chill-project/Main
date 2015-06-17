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

namespace Chill\MainBundle\Tests\Security\Authorization;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Chill\MainBundle\Test\PrepareUserTrait;
use Chill\MainBundle\Test\PrepareCenterTrait;
use Chill\MainBundle\Test\PrepareScopeTrait;

/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class AuthorizationHelperTest extends KernelTestCase
{
    
    use PrepareUserTrait, PrepareCenterTrait, PrepareScopeTrait {
                PrepareCenterTrait::setUpTrait insteadof PrepareScopeTrait;
                PrepareCenterTrait::tearDownTrait insteadof PrepareScopeTrait;
                PrepareCenterTrait::getProphet insteadof PrepareScopeTrait;
            }
    
    public function setUp() 
    {
        static::bootKernel();
        $this->setUpTrait();
    }
    
    /**
     * 
     * @return \Chill\MainBundle\Security\Authorization\AuthorizationHelper
     */
    private function getAuthorizationHelper()
    {
        return static::$kernel->getContainer()
                ->get('chill.main.security.authorization.helper')
                ;
    }
    
    /**
     * Test function userCanReach of helper.
     * 
     * A user can reach center => the function should return true.
     */
    public function testUserCanReach_UserShouldReach() 
    {
        $center = $this->prepareCenter(1, 'center');
        $scope = $this->prepareScope(1, 'default');
        $user = $this->prepareUser(array(
            array(
                'center' => $center, 'permissionsGroup' => array(
                    ['scope' => $scope, 'role' => 'ANY_ROLE']
                )
            )
        ));
        $helper = $this->getAuthorizationHelper();
        
        $this->assertTrue($helper->userCanReachCenter($user, $center));
    }
    
    /**
     * Test function userCanReach of helper
     * 
     * A user can not reachcenter =>W the function should return false
     */
    public function testUserCanReach_UserShouldNotReach() 
    {
        $centerA = $this->prepareCenter(1, 'center');
        $centerB = $this->prepareCenter(2, 'centerB');
        $scope = $this->prepareScope(1, 'default');
        $user = $this->prepareUser(array(
            array(
                'center' => $centerA, 'permissionsGroup' => array(
                    ['scope' => $scope, 'role' => 'ANY_ROLE']
                )
            )
        ));
        $helper = $this->getAuthorizationHelper();
        
        $this->assertFalse($helper->userCanReachCenter($user, $centerB));
        
    }
    
    public function testUserHasAccess_EntityWithoutScope()
    {
        $center = $this->prepareCenter(1, 'center');
        $scope = $this->prepareScope(1, 'default');
        $user = $this->prepareUser(array(
            array(
                'center' => $center, 'permissionsGroup' => array(
                    ['scope' => $scope, 'role' => 'CHILL_ROLE']
                )
            )
        ));
        $helper = $this->getAuthorizationHelper();
        $entity = $this->prophet->prophesize();
        $entity->willImplement('\Chill\MainBundle\Entity\HasCenterInterface');
        $entity->getCenter()->willReturn($center);
        
        $this->assertTrue($helper->userHasAccess($user, $entity->reveal(), 'CHILL_ROLE'));
    }
    
    
    public function testUserHasNoRole_EntityWithoutScope()
    {
        $center = $this->prepareCenter(1, 'center');
        $scope = $this->prepareScope(1, 'default');
        $user = $this->prepareUser(array(
            array(
                'center' => $center, 'permissionsGroup' => array(
                    ['scope' => $scope, 'role' => 'ANY_ROLE']
                )
            )
        ));
        $helper = $this->getAuthorizationHelper();
        $entity = $this->prophet->prophesize();
        $entity->willImplement('\Chill\MainBundle\Entity\HasCenterInterface');
        $entity->getCenter()->willReturn($center);
        
        $this->assertFalse($helper->userHasAccess($user, $entity->reveal(), 'CHILL_ROLE'));
    }
    
    /**
     * test that a user has no access on a entity, but is granted on the same role
     * on another center
     */
    public function testUserHasNoRole_UserHasRoleOnAnotherCenter_EntityWithoutScope()
    {
        $centerA = $this->prepareCenter(1, 'center');
        $centerB = $this->prepareCenter(2, 'centerB');
        $scope = $this->prepareScope(1, 'default');
        $user = $this->prepareUser(array(
            array(
                'center' => $centerA, 'permissionsGroup' => array(
                    ['scope' => $scope, 'role' => 'ANY_ROLE']
                ),
            array(
                'centerB' => $centerB, 'permissionsGroup' => array(
                    ['scope' => $scope, 'role' => 'ANY_ROLE'],
                    ['scope' => $scope, 'role' => 'CHILL_ROLE']
                )
            )
            )
        ));
        $helper = $this->getAuthorizationHelper();
        $entity = $this->prophet->prophesize();
        $entity->willImplement('\Chill\MainBundle\Entity\HasCenterInterface');
        $entity->getCenter()->willReturn($centerA);
        
        $this->assertFalse($helper->userHasAccess($user, $entity->reveal(), 'CHILL_ROLE'));
    }
    
    public function testUserHasAccess_EntityWithScope()
    {
        $center = $this->prepareCenter(1, 'center');
        $scope = $this->prepareScope(1, 'default');
        $user = $this->prepareUser(array(
            array(
                'center' => $center, 'permissionsGroup' => array(
                    ['scope' => $scope, 'role' => 'CHILL_ROLE']
                )
            )
        ));
        $helper = $this->getAuthorizationHelper();
        $entity = $this->prophet->prophesize();
        $entity->willImplement('\Chill\MainBundle\Entity\HasCenterInterface');
        $entity->willImplement('\Chill\MainBundle\Entity\HasScopeInterface');
        $entity->getCenter()->willReturn($center);
        $entity->getScope()->willReturn($scope);
        
        $this->assertTrue($helper->userHasAccess($user, $entity->reveal(), 'CHILL_ROLE'));
    }
    
    public function testUserHasNoRole_EntityWithScope()
    {
        $center = $this->prepareCenter(1, 'center');
        $scope = $this->prepareScope(1, 'default');
        $user = $this->prepareUser(array(
            array(
                'center' => $center, 'permissionsGroup' => array(
                    ['scope' => $scope, 'role' => 'CHILL_ROLE']
                )
            )
        ));
        $helper = $this->getAuthorizationHelper();
        $entity = $this->prophet->prophesize();
        $entity->willImplement('\Chill\MainBundle\Entity\HasCenterInterface');
        $entity->willImplement('\Chill\MainBundle\Entity\HasScopeInterface');
        $entity->getCenter()->willReturn($center);
        $entity->getScope()->willReturn($scope);
        
        $this->assertFalse($helper->userHasAccess($user, $entity->reveal(), 'ANOTHER_ROLE'));
    }
    
    public function testUserHasNoCenter_EntityWithScope()
    {
        $centerA = $this->prepareCenter(1, 'center'); //the user will have this center
        $centerB = $this->prepareCenter(2, 'centerB'); //the entity will have another center
        $scope = $this->prepareScope(1, 'default');
        $user = $this->prepareUser(array(
            array(
                'center' => $centerA, 'permissionsGroup' => array(
                    ['scope' => $scope, 'role' => 'CHILL_ROLE']
                )
            )
        ));
        $helper = $this->getAuthorizationHelper();
        $entity = $this->prophet->prophesize();
        $entity->willImplement('\Chill\MainBundle\Entity\HasCenterInterface');
        $entity->willImplement('\Chill\MainBundle\Entity\HasScopeInterface');
        $entity->getCenter()->willReturn($centerB);
        $entity->getScope()->willReturn($scope);
        
        $this->assertFalse($helper->userHasAccess($user, $entity->reveal(), 'CHILL_ROLE'));
    }
    
    public function testUserHasNoScope_EntityWithScope()
    {
        $center = $this->prepareCenter(1, 'center');
        $scopeA = $this->prepareScope(1, 'default'); //the entity will have this scope
        $scopeB = $this->prepareScope(2, 'other'); //the user will be granted this scope
        $user = $this->prepareUser(array(
            array(
                'center' => $center, 'permissionsGroup' => array(
                    ['scope' => $scopeB, 'role' => 'CHILL_ROLE']
                )
            )
        ));
        $helper = $this->getAuthorizationHelper();
        $entity = $this->prophet->prophesize();
        $entity->willImplement('\Chill\MainBundle\Entity\HasCenterInterface');
        $entity->willImplement('\Chill\MainBundle\Entity\HasScopeInterface');
        $entity->getCenter()->willReturn($center);
        $entity->getScope()->willReturn($scopeA);
        
        $this->assertFalse($helper->userHasAccess($user, $entity->reveal(), 'CHILL_ROLE'));
    }
    
    
    
}
