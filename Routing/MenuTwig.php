<?php

/**
 * 
 * Copyright (C) 2014 Champs Libres Cooperative SCRLFS, 
 * <http://www.champs-libres.coop>
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

namespace Chill\MainBundle\Routing;

use Chill\MainBundle\Routing\MenuComposer;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add the filter 'chill_menu'
 *
 * @author Julien Fastré <julien arobase fastre point info>
 */
class MenuTwig extends \Twig_Extension implements ContainerAwareInterface
{
    
    /**
     *
     * @var MenuComposer
     */
    private $menuComposer;
    
    /**
     *
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;
    
    /**
     * the default parameters for chillMenu
     * 
     * @var mixed[] 
     */
    private $defaultParams = array(
        'layout' => 'ChillMainBundle:Menu:defaultMenu.html.twig',
        'args' => array(),
        'activeRouteKey' => null
    );
    
    public function __construct(MenuComposer $menuComposer)
    {
        $this->menuComposer = $menuComposer;
    }
    
    public function getFunctions()
    {
        return [new \Twig_SimpleFunction('chill_menu', 
                array($this, 'chillMenu'), array('is_safe' => array('html')))
            ];
    }
    
    /**
     * Render a Menu corresponding to $menuId
     * 
     * Expected params : 
     * - args: the arguments to build the path (i.e: if pattern is /something/{bar}, args must contain {'bar': 'foo'}
     * - layout: the layout. Absolute path needed (i.e.: ChillXyzBundle:section:foo.html.twig)
     * - activeRouteKey : the key active, will render the menu differently.
     * 
     * see https://redmine.champs-libres.coop/issues/179 for more informations
     * 
     * @param string $menuId
     * @param mixed[] $params
     */
    public function chillMenu($menuId, array $params = array())
    {
        $resolvedParams = array_merge($this->defaultParams, $params);

        $layout = $resolvedParams['layout'];
        unset($resolvedParams['layout']);
        
        $resolvedParams['routes'] = $this->menuComposer->getRoutesFor($menuId);
        
        return $this->container->get('templating')
                ->render($layout, $resolvedParams);
    }
    
    public function getName()
    {
        return 'chill_menu';
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

}
