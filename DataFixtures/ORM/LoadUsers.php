<?php

namespace Chill\MainBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Load agents into database
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class LoadUsers extends AbstractFixture implements ContainerAwareInterface
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

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        
    }

}
