<?php

/*
 * <one line to give the program's name and a brief idea of what it does.>
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

namespace Chill\MainBundle\Tests\Services;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Test the Twig function 'chill_menu'
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class ChillMenuTwigFunctionTest extends KernelTestCase
{
    
    private static $templating;
    
    public static function setUpBeforeClass()
    {
        self::bootKernel(array('environment' => 'test'));
        static::$templating = static::$kernel
                ->getContainer()->get('templating');
        //load templates in Tests/Resources/views
        static::$kernel->getContainer()->get('twig.loader')
                ->addPath(__DIR__.'/../Fixtures/Resources/views/', $namespace = 'tests');
    }
    
    public function testNormalMenu()
    {
        $content = static::$templating->render('@tests/menus/normalMenu.html.twig');
        $crawler = new Crawler($content);
        
        $ul = $crawler->filter('ul')->getNode(0);
        $this->assertEquals( 'ul', $ul->tagName);
        
        $lis = $crawler->filter('ul')->children();
        $this->assertEquals(3, count($lis));
        
        $lis->each(function(Crawler $node, $i) {
                $this->assertEquals('li', $node->getNode(0)->tagName);
                
                $a = $node->children()->getNode(0);
                $this->assertEquals('a', $a->tagName);
                switch($i) {
                    case 0: 
                        $this->assertEquals('/dummy?param=fake', $a->getAttribute('href'));
                        $this->assertEquals('active', $a->getAttribute('class'));
                        $this->assertEquals('test0', $a->nodeValue);
                        break;
                    case 1:
                        $this->assertEquals('/dummy1?param=fake', $a->getAttribute('href'));
                        $this->assertEmpty($a->getAttribute('class'));
                        $this->assertEquals('test1', $a->nodeValue);
                        break;
                    case 3:
                        $this->assertEquals('/dummy2/fake', $a->getAttribute('href'));
                        $this->assertEmpty($a->getAttribute('class'));
                        $this->assertEquals('test2', $a->nodeValue);
                }
        });
    }
    
    public function testMenuOverrideTemplate()
    {
        $content = static::$templating->render('@tests/menus/overrideTemplate.html.twig');
        $this->assertEquals('fake template', $content);
    }
}
