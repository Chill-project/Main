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
use Chill\MainBundle\Entity\GroupCenter;
use Chill\MainBundle\DataFixtures\ORM\LoadCenters;
use Chill\MainBundle\DataFixtures\ORM\LoadPermissionsGroup;

/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class LoadGroupCenters extends AbstractFixture implements OrderedFixtureInterface
{
    public function getOrder()
    {
        return 500;
    }
    
    public static $refs = array();

    public function load(ObjectManager $manager)
    {
        foreach (LoadCenters::$refs as $centerRef) {
            foreach (LoadPermissionsGroup::$refs as $permissionGroupRef) {
                $GroupCenter = new GroupCenter();
                $GroupCenter->setCenter($this->getReference($centerRef));
                $GroupCenter->addPermissionGroup($this->getReference($permissionGroupRef));
                
                $manager->persist($GroupCenter);
                
                $reference = $centerRef.'_'.$permissionGroupRef;
                $this->addReference($reference, $GroupCenter);
                static::$refs[] = $reference;
                echo "Creating $reference...  \n";
            }
        }
        
        $manager->flush();
    }
}
