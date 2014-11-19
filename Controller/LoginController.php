<?php

namespace Chill\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;

class LoginController extends Controller
{
    /**
     * 
     * @todo Improve this with http://symfony.com/blog/new-in-symfony-2-6-security-component-improvements#added-a-security-error-helper
     * @param Request $request
     * @return Response
     */
    public function loginAction(Request $request)
    {
        
        $session = $request->getSession();

        if ($request->attributes->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContextInterface::AUTHENTICATION_ERROR
            );
        } elseif (null !== $session && $session->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR);
            $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        $lastUsername = (null === $session) ? 
              '' : $session->get(SecurityContextInterface::LAST_USERNAME);


        return $this->render('ChillMainBundle:Login:login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => (empty($error)) ? $error : $error->getMessage()
        ));  
        
    }
    
    public function LoginCheckAction(Request $request)
    {
        
    }

}
