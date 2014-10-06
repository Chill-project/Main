<?php

namespace CL\Chill\MainBundle\DependencyInjection\Services;

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
                foreach ($route->getOption('menus') as $menuKey => $params) {
                    if ($menuId === $menuKey) {
                        $route = array();
                        $route['route'] = $routeKey;
                        $route['label'] = $params['label']; 
                        $route['helper'] = 
                                (isset($params['helper'])) ? $params['helper'] : null;
                        
                        //add route to the routes array, avoiding duplicate 'order'
                        // to erase previously added
                        if (!isset($routes[$params['order']])) {
                            $routes[$params['order']] = $route;
                        } else {
                            $routes[$params['order'] + 1 ] = $route;
                        }
                        
                    }
                }
            }
        }

        ksort($routes);

        return $routes;
    }

}
