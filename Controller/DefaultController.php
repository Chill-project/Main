<?php

namespace CL\Chill\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $a = array(
            array('success', "Message de succès (success)! "),
            array('success',"Un autre message de succès ! "),
            array('danger' , "(danger)Avert lorem ipsum dolor sit amet spiritur"),
            array('danger' ,"Avertum ipsut est amet amergitur tatouari"),
            array('info' , "(info) Un message d'information s'affiche."),
            array('info' , "Info informitur escept asolitur amet. Sit Roberetur astot.")
        );
        
        if ($this->getRequest()->query->get('addMessages', 1)) {
            foreach ($a as $array) {
                $this->get('session')->getFlashBag()->add($array[0], $array[1]);
            }
        }
        
        
        return $this->render('CLChillMainBundle::layout.html.twig');
    }
}
