<?php

/*
 * Chill is a software for social workers
 *
 * Copyright (C) 2014-2016, Champs Libres Cooperative SCRLFS, 
 * <http://www.champs-libres.coop>, <info@champs-libres.coop>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
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
use Chill\MainBundle\Entity\PostalCode;

/**
 * Description of LoadPostalCodes
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 * @author Champs Libres <info@champs-libres.coop>
 */
class LoadPostalCodes extends AbstractFixture implements OrderedFixtureInterface
{
    public function getOrder()
    {
        return 50;
    }
    
    public static $refs = array();

    public function load(ObjectManager $manager)
    {
        $lines = str_getcsv(self::$codes, "\n");
        $belgium = $manager->getRepository('ChillMainBundle:Country')
              ->findOneBy(array('countryCode' => 'BE'));
        
        foreach($lines as $line) {
            $code = str_getcsv($line); 
            $c = new PostalCode();
            $c->setCountry($belgium)
                  ->setCode($code[0])
                  ->setName(implode(' - ', array(
                     ucwords(strtolower($code[1])), strtoupper($code[2]),
                  )));
            $manager->persist($c);
            $ref = 'postal_code_'.$code[0];
            
            if (! $this->hasReference($ref)) {
                $this->addReference($ref, $c);
                self::$refs[] = $ref;
            }
        }
        
        $manager->flush();
    }
    
    private static $codes = <<<EOF
1000,BRUXELLES,BRUXELLES,Bruxelles
1020,Laeken,BRUXELLES,Bruxelles
1030,SCHAERBEEK,SCHAERBEEK,Bruxelles
1040,ETTERBEEK,ETTERBEEK,Bruxelles
1050,IXELLES,IXELLES,Bruxelles
1060,SAINT-GILLES,SAINT-GILLES,Bruxelles
1070,ANDERLECHT,ANDERLECHT,Bruxelles
1080,MOLENBEEK-SAINT-JEAN,MOLENBEEK-SAINT-JEAN,Bruxelles
1081,KOEKELBERG,KOEKELBERG,Bruxelles
1082,BERCHEM-SAINTE-AGATHE,BERCHEM-SAINTE-AGATHE,Bruxelles
1083,GANSHOREN,GANSHOREN,Bruxelles
1090,JETTE,JETTE,Bruxelles
1120,Neder-Over-Heembeek,BRUXELLES,Bruxelles
1130,Haren,BRUXELLES,Bruxelles
1140,EVERE,EVERE,Bruxelles
1150,WOLUWE-SAINT-PIERRE,WOLUWE-SAINT-PIERRE,Bruxelles
1160,AUDERGHEM,AUDERGHEM,Bruxelles
1170,WATERMAEL-BOITSFORT,WATERMAEL-BOITSFORT,Bruxelles
1180,UCCLE,UCCLE,Bruxelles
1190,FOREST,FOREST,Bruxelles
1200,WOLUWE-SAINT-LAMBERT,WOLUWE-SAINT-LAMBERT,Bruxelles
1210,SAINT-JOSSE-TEN-NOODE,SAINT-JOSSE-TEN-NOODE,Bruxelles
1300,Limal,WAVRE,Brabant-Wallon
1300,WAVRE,WAVRE,Brabant-Wallon
1301,Bierges,WAVRE,Brabant-Wallon
1310,LA HULPE,LA HULPE,Brabant-Wallon
1315,Glimes,INCOURT,Brabant-Wallon
1315,INCOURT,INCOURT,Brabant-Wallon
1315,Opprebais,INCOURT,Brabant-Wallon
1315,Piètrebais,INCOURT,Brabant-Wallon
1315,Roux-Miroir,INCOURT,Brabant-Wallon
1320,BEAUVECHAIN,BEAUVECHAIN,Brabant-Wallon
EOF;

}
