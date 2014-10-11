<?php

namespace CL\Chill\MainBundle\Routing;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RouteCollection;

/**
 * This class permit to build menu from the routing information
 * stored in each bundle.
 * 
 * how to must come here FIXME 
 *
 * @author julien
 */
class MenuComposer
{

    /**
     *
     * @var \Symfony\Component\Routing\RouteCollection; 
     */
    private $routeCollection;

    public function __construct(RouterInterface $router)
    {
        //see remark in MenuComposer::setRouteCollection
        $this->setRouteCollection($router->getRouteCollection());
    }

    /**
     * Set the route Collection
     * This function is needed for testing purpose: routeCollection is not
     * available as a service (RouterInterface is provided as a service and
     * added to this class as paramater in __construct)
     * 
     * @param RouteCollection $routeCollection
     */
    public function setRouteCollection(RouteCollection $routeCollection)
    {
        $this->routeCollection = $routeCollection;
    }

    public function getRoutesFor($menuId, array $parameters = array())
    {
        $routes = array();

        foreach ($this->routeCollection->all() as $routeKey => $route) {
            if ($route->hasOption('menus')) {
                if (array_key_exists($menuId, $route->getOption('menus'))) {
                    $route = $route->getOption('menus')[$menuId];

                    $route['key'] = $routeKey;

                    $order = $this->resolveOrder($routes, $route['order']);
                    //we do not want to duplicate order information
                    unset($route['order']);
                    $routes[$order] = $route;
                }
            }
        }

        ksort($routes);

        return $routes;
    }
    
    /**
     * recursive function to resolve the order of a array of routes.
     * If the order chosen in routing.yml is already in used, find the 
     * first next order available.
     * 
     * @param array $routes the routes previously added
     * @param int $order
     * @return int
     */
    private function resolveOrder($routes, $order){
        if (isset($routes[$order])) {
            return $this->resolveOrder($routes, $order + 1);
        } else {
            return $order;
        }
    }

}