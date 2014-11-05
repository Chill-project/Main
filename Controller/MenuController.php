<?php

namespace Chill\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MenuController extends Controller
{
    public function writeMenuAction($menu, $layout, $activeRouteKey = null, array $args = array() )
    {
        return $this->render($layout, array(
            'menu_composer' => $this->get('chill.main.menu_composer'),
            'menu' => $menu,
            'args' => $args,
            'activeRouteKey' => $activeRouteKey
        ));
    }
}
