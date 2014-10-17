<?php

namespace Chill\MainBundle\Routing;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This class permit to build menu from the routing information
 * stored in each bundle.
 * 
 * how to must come here FIXME 
 *
 * @author julien
 */
class MenuComposer implements ContainerAwareInterface
{
    
    /**
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * 
     * @internal using the service router in container cause circular references
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        if (NULL === $container) {
            throw new LogicException('container should not be null');
        }
        //see remark in MenuComposer::setRouteCollection
        $this->container = $container;
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

    /**
     * Return an array of routes added to $menuId,
     * The array is aimed to build route with MenuTwig
     * 
     * @param string $menuId
     * @param array $parameters see https://redmine.champs-libres.coop/issues/179
     * @return array
     */
    public function getRoutesFor($menuId, array $parameters = array())
    {
        $routes = array();
        $routeCollection = $this->container->get('router')->getRouteCollection();

        foreach ($routeCollection->all() as $routeKey => $route) {
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
