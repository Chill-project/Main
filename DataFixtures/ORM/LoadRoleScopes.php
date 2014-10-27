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
use Chill\MainBundle\Entity\RoleScope;
use Chill\MainBundle\DataFixtures\ORM\LoadScopes;

/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class LoadRoleScopes extends AbstractFixture implements OrderedFixtureInterface
{
    public function getOrder()
    {
        return 300;
    }
    
    public static $permissions = array(
        'CHILL_FOO_SEE' => array(
            'names' => array(
                'fr' => 'voir foo',
                'en' => 'see foo',
                'nl' => 'zie foo'
            )
        ),
        'CHILL_FOO_EDIT' => array(
            'names' => array(
                'fr' => 'modifier foo',
                'en' => 'edit foo',
                'nl' => 'editie foo'
            )
        )
    );
    
    public static $references = array();

    public function load(ObjectManager $manager)
    {
        foreach (static::$permissions as $key => $permission) {
            foreach(LoadScopes::$references as $scopeReference) {
                $roleScope = new RoleScope();
                $roleScope->setRole($key)
                        ->setScope($this->getReference($scopeReference))
                        ;
                $reference = 'role_scope_'.$key.'_'.$this->getReference($scopeReference)->getName()['en'];
                var_dump($reference);
                $this->addReference($reference, $roleScope);
                $manager->persist($roleScope);
                static::$references[] = $reference;
            }
        }
        
        $manager->flush();
    }

}
