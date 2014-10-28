<?php

namespace Chill\MainBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Chill\MainBundle\DataFixtures\ORM\LoadCenters;
use Chill\MainBundle\DataFixtures\ORM\LoadPermissionsGroup;
use Chill\MainBundle\Entity\User;

/**
 * Load fixtures users into database
 * 
 * create a user for each permission_group and center.
 * username and password are identicals.
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class LoadUsers extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     *
     * @var ContainerInterface
     */
    private $container;

    public function getOrder()
    {
        return 1000;
    }
    
    public static $refs = array();

    public function load(ObjectManager $manager)
    {
        foreach(LoadCenters::$refs as $centerRef) {
            foreach(LoadPermissionsGroup::$refs as $permissionGroupRef) {
                $user = new User();
                
                $permissionGroup = $this->getReference($permissionGroupRef);
                $center = $this->getReference($centerRef);
                $username = $center->getName().'_'.$permissionGroup->getName();
                
                $user->setUsername($username)
                        ->setPassword($this->container->get('security.encoder_factory')
                                ->getEncoder($user)
                                ->encodePassword($username, $user->getSalt()));
                $user->addGroupCenter($this->getReference($centerRef.'_'.$permissionGroupRef));
                
                $manager->persist($user);
                $this->addReference($username, $user);
                static::$refs[] = $user->getUsername();
                echo "Creating user with username ".$user->getUsername()."... \n";
            }
        }
        
        $manager->flush();
    }

    public function setContainer(ContainerInterface $container = null)
    {
        if (NULL === $container) {
            throw new \LogicException('$container should not be null');
        }
        
        $this->container = $container;
    }

}
