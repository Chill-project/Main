<?php

namespace CL\Chill\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('CLChillMainBundle::layout.html.twig');
    }
}
