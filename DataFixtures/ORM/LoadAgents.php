<?php

namespace CL\Chill\MainBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use CL\Chill\MainBundle\Entity\Agent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Load agents into database
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class LoadAgents extends AbstractFixture implements ContainerAwareInterface {
    
    /**
     *
     * @var ContainerInterface
     */
    private $container;
    
    const AGENT_STRING = 'agent';
    
    public function getOrder() {
        return 1000;
    }
    
    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager) {
        
        echo "creating agents... \n";
        
        $userManager = $this->container->get('fos_user.user_manager');
        
        for ($i = 0; $i < 10; $i++) {
            $username = 'agent'.$i;
            echo "creating agent $username (password $username) \n";
            
            $user = $userManager->createUser();
            
            $user->setUsername($username)
                    ->setPassword($username)
                    ->setName($username)
                    ->setEmail($username.'@chill.be');
            
            $this->container->get('fos_user.user_manager')->updateUser($user, false);
            
            $this->addReference($username, $user);
        }
        
        $manager->flush();
    }
    
    
    

}
