<?php

/*
 * Chill is a suite of a modules, Chill is a software for social workers
 * Copyright (C) 2014, Champs Libres Cooperative SCRLFS, <http://www.champs-libres.coop>
 * 
 * This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Chill\MainBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Chill\MainBundle\Entity\PermissionsGroup;

/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class LoadPermissionsGroup extends AbstractFixture implements OrderedFixtureInterface
{
    public function getOrder()
    {
        return 400;
    }
    
    public static $permissionGroup = array(
        array(
            'name' => 'social',
            'role_scopes' => array(
                'role_scope_CHILL_FOO_EDIT_social', 
                'role_scope_CHILL_FOO_SEE_administrative',
                "role_scope_CHILL_FOO_EDIT_all"
            )
        ),
        array(
            'name' => 'administrative',
            'role_scopes' => array(
                "role_scope_CHILL_FOO_SEE_social",
                "role_scope_CHILL_FOO_EDIT_administrative",
                "role_scope_CHILL_FOO_EDIT_all"
            )
        ),
        array(
            'name' => 'direction',
            'role_scopes' => array(
                "role_scope_CHILL_FOO_EDIT_all",
                "role_scope_CHILL_FOO_SEE_DETAILS_social",
                "role_scope_CHILL_FOO_SEE_DETAILS_administrative"
            )
        )
    );
    
    public static $refs = array();

    public function load(ObjectManager $manager)
    {
        foreach (static::$permissionGroup as $new) {
            $permissionGroup = new PermissionsGroup();
            $permissionGroup->setName($new['name']);
            foreach ($new['role_scopes'] as $roleScopeRef) {
                $permissionGroup->addRoleScope($this->getReference($roleScopeRef));
            }
            
            $manager->persist($permissionGroup);
            $reference = 'permission_group_'.$new['name'];
            echo "Creating $reference \n";
            $this->setReference($reference, $permissionGroup);
            static::$refs[] = $reference;
        }
        
        $manager->flush();
    }
}
