<?php

namespace CL\Chill\MainBundle\Tests\Services;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\Loader\YamlFileLoader;
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
     * @var \CL\Chill\MainBundle\DependencyInjection\Services\MenuComposer;
     */
    private $menuComposer;
    
    public function setUp()
    {
        self::bootKernel(array('environment' => 'test'));
        $this->loader = static::$kernel->getContainer()
                ->get('routing.loader');
        $this->menuComposer = static::$kernel->getContainer()
                ->get('chill.main.menu_composer');
    }
    
    public function testMenuComposer()
    {
        $collection = new RouteCollection();
        //$collection->add($this->loader->load(__DIR__.'dummy_menu_composer.yml', 'yaml'));
        
        $routes = $this->menuComposer->getRoutesFor('dummy0');
        
        $this->assertInternalType('array', $routes);
        $this->assertCount(2, $routes);
        //check that the keys are sorted
        $orders = array_keys($routes);
        foreach ($orders as $key => $order){
            if (array_key_exists($key + 1, $orders)) {
                $this->assertGreaterThan($order, $orders[$key + 1],
                        'Failing to assert that routes are ordered');
            }
        }
        $this->assertSame(array(
            50 => array(
                'route'   => 'chill_main_dummy_0',
                'label' => 'test0',
                'helper'=> null
                ),
            51 => array(
                'route'   => 'chill_main_dummy_1',
                'label' => 'test1',
                'helper'=> 'great helper'
            )
            
        ), $routes);
    }
}
