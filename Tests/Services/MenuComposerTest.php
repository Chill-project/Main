<?php

namespace Chill\MainBundle\Tests\Services;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\RouteCollection;

/**
 * This class provide functional test for MenuComposer
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class MenuComposerTest extends KernelTestCase
{
    
    /**
     *
     * @var \Symfony\Bundle\FrameworkBundle\Routing\DelegatingLoader;
     */
    private $loader;
    
    /**
     *
     * @var \Chill\MainBundle\DependencyInjection\Services\MenuComposer;
     */
    private $menuComposer;
    
    public function setUp()
    {
        self::bootKernel(array('environment' => 'test'));
        $this->menuComposer = static::$kernel->getContainer()
                ->get('chill.main.menu_composer');
    }
    
    /**
     * @covers \Chill\MainBundle\Routing\MenuComposer
     */
    public function testMenuComposer()
    {
        $collection = new RouteCollection();
        
        $routes = $this->menuComposer->getRoutesFor('dummy0');
        
        $this->assertInternalType('array', $routes);
        $this->assertCount(3, $routes);
        //check that the keys are sorted
        $orders = array_keys($routes);
        foreach ($orders as $key => $order){
            if (array_key_exists($key + 1, $orders)) {
                $this->assertGreaterThan($order, $orders[$key + 1],
                        'Failing to assert that routes are ordered');
            }
        }
        
        //check that the array are identical, order is not important :
        
        $expected = array(
            50 => array(
                'key'   => 'chill_main_dummy_0',
                'label' => 'test0',
                'otherkey' => 'othervalue'
                ),
            51 => array(
                'key'   => 'chill_main_dummy_1',
                'label' => 'test1',
                'helper'=> 'great helper'
            ),
            52 => array(
                'key'   => 'chill_main_dummy_2',
                'label' => 'test2'
            ));
        
        
        foreach ($expected as $order => $route ){
            
        }
        
        //compare arrays
        foreach($expected as $order => $route) {
            //check the key are the one expected
            $this->assertTrue(isset($routes[$order]));
            
            if (isset($routes[$order])){ #avoid an exception if routes with order does not exists
                //sort arrays. Order matters for phpunit::assertSame
                ksort($route);
                ksort($routes[$order]);
                $this->assertSame($route, $routes[$order]);
            }
        }
    }
}
