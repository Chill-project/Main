<?php

namespace Chill\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {        
        if ($this->isGranted('ROLE_ADMIN')) {
            
            return $this->redirectToRoute('chill_main_admin_central');
        }
        
        return $this->render('ChillMainBundle::layout.html.twig');
    }

    public function indexWithoutLocaleAction()
    {
        return $this->redirect($this->generateUrl('chill_main_homepage'));
    }
}