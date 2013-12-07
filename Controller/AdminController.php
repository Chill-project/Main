<?php

namespace CL\Chill\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * 
 *
 * @author julien.fastre@champs-libres.coop
 */
class AdminController extends Controller {
    
    public function indexAction($menu = 'admin', 
            $header_title = 'views.Main.admin.index.header_title',
            $page_title = 'views.Main.admin.index.page_title') {
        
        
        return $this->render('CLChillMainBundle:Admin:index.html.twig', array(
            'menu_composer' => $this->get('cl_chill_main_menu_composer'),
            'menu' => $menu,
            'header_title' => $header_title,
            'page_title' => $page_title,
            'args' => array()
        ));
    }
    
}
