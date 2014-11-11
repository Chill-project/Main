<?php

namespace Chill\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {        
        return $this->render('ChillMainBundle::layout.html.twig');
    }
}
