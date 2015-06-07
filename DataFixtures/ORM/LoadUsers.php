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
    
    public static $refs = array(
        'center a_social' => array(
            'groupCenterRefs' => ['centerA_permission_group_social']
        ),
        'center a_administrative' => array(
            'groupCenterRefs' => ['centerA_permission_group_administrative']
        ),
        'center a_direction' => array(
            'groupCenterRefs' => ['centerA_permission_group_direction']
        ),
        'center b_social' => array(
            'groupCenterRefs' => ['centerB_permission_group_social']
        ),
        'center b_administrative' => array(
            'groupCenterRefs' => ['centerB_permission_group_administrative']
        ),
        'center b_direction' => array(
            'groupCenterRefs' => ['centerB_permission_group_direction']
        ),
        'multi_center' => array(
            'groupCenterRefs' => ['centerA_permission_group_social', 
                'centerB_permission_group_social']
        )
        
    );

    public function load(ObjectManager $manager)
    {
        foreach (self::$refs as $username => $params) {

            $user = new User();

            $user->setUsername($username)
                    ->setPassword(
                            $this->container->get('security.encoder_factory')
                                ->getEncoder($user)
                                ->encodePassword('password', $user->getSalt())
                            );

            foreach ($params['groupCenterRefs'] as $groupCenterRef) {
                $user->addGroupCenter($this->getReference($groupCenterRef));
            }
            
            echo 'Creating user ' . $username ."... \n";
            $manager->persist($user);
            $this->addReference($username, $user);
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
