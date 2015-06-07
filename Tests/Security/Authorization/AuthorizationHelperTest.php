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

/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class AuthorizationHelperTest extends KernelTestCase
{
    public function setUp() 
    {
        static::bootKernel();
    }
    
    private function getUser($username)
    {
        return static::$kernel->getContainer()
                ->get('doctrine.orm.entity_manager')
                ->getRepository('ChillMainBundle:User')
                ->findOneByUsername($username)
                ;
    }
    
    private function getCenter($centerName)
    {
        return static::$kernel->getContainer()
                ->get('doctrine.orm.entity_manager')
                ->getRepository('ChillMainBundle:Center')
                ->findOneByName($centerName)
                ;
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
        $centerAUser = $this->getUser('center a_social');
        $multiCenter = $this->getuser('multi_center');
        $centerA = $this->getCenter('Center A');
        $centerB = $this->getCenter('Center B');
        $helper = $this->getAuthorizationHelper();
        
        $this->assertTrue($helper->userCanReachCenter($centerAUser, $centerA));
        $this->assertTrue($helper->userCanReachCenter($multiCenter, $centerA));
        $this->assertTrue($helper->userCanReachCenter($multiCenter, $centerB));
    }
    
    /**
     * Test function userCanReach of helper
     * 
     * A user can not reachcenter =>W the function should return false
     */
    public function testUserCanReach_UserShouldNotReach() 
    {
        $centerAUser = $this->getUser('center a_social');
        $center = $this->getCenter('Center B');
        $helper = $this->getAuthorizationHelper();
        
        $this->assertFalse($helper->userCanReachCenter($centerAUser, $center));
        
    }
    
}
