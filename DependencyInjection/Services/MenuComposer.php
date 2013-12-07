<?php

namespace CL\Chill\MainBundle\DependencyInjection\Services;

use Symfony\Component\Routing\RouterInterface;

/**
 * This class permit to build menu from the routing information
 * stored in each bundle.
 * 
 * how to must come here FIXME 
 *
 * @author julien
 */
class MenuComposer {
    
    /**
     *
     * @var \Symfony\Component\Routing\RouteCollection; 
     */
    private $routeCollection;
    
    
    public function __construct(RouterInterface $router) {
        $this->routeCollection = $router->getRouteCollection();
    }
    
    public function getRoutesFor($menuId, array $parameters = array()) {
        $routes = array();
        
        foreach ($this->routeCollection->all() as $key => $route) {
            if ($route->getOption('menu') === $menuId) {
                $a['route'] = $key;
                $a['label'] = $route->getOption('label');
                
                if ($route->hasOption('helper')) {
                    $a['helper'] = $route->getOption('helper');
                } else {
                    $a['helper'] = '';
                }
                
                $routes[$route->getOption('order')] = $a;
            }
        }
        
        ksort($routes);
        
        return $routes;
        
    }
    
}
