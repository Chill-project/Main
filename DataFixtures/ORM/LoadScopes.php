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

/**
 * Create scopes
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class LoadScopes extends AbstractFixture implements OrderedFixtureInterface
{
    public function getOrder()
    {
        return 200;
    }
    
    public $scopes = array(
        array(
            'names' => array(
                'fr' => 'tous',
                'en' => 'all',
                'nl' => 'algemeen'
            ),
        ),
        array(
            'names' => array(
                'fr' => 'social',
                'en' => 'social',
                'nl' => 'sociaal'
            )
        ),
        array(
            'names' => array(
                'fr' => 'administratif',
                'en' => 'administrative',
                'nl' => 'administratief'
            )
        )
    );
    
    public static $references = array();

    public function load(ObjectManager $manager)
    { 
        foreach ($this->scopes as $new) {
            $scope = new \Chill\MainBundle\Entity\Scope();
            $scope->setName($new['names']);
            
            $manager->persist($scope);
            $reference = 'scope_'.$new['names']['en'];
            $this->addReference($reference, $scope);
            static::$references[] = $reference;
        }
        
        $manager->flush();
    }
}
